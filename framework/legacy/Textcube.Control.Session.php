<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('SESSION_OPENID_USERID', -1);

final class Session {
    private static $sessionMicrotime;
    private static $sessionName = null;
    private static $sessionDBRepair = false;
    private static $sConfig = null;
    private static $context = null;
    private static $pool = null;

    function __construct() {
        $sessionMicrotime = Timer::getMicroTime();
    }

    private static function initialize() {
        self::$context = Model_Context::getInstance();
        self::$pool = DBModel::getInstance();
    }

    public static function open($savePath, $sessionName) {
        return true;
    }

    public static function close() {
        return true;
    }

    public static function getName() {
        if (is_null(self::$context)) {
            self::initialize();
        }
        if (self::$sessionName == null) {
            if (self::$context->getProperty('service.session_cookie') !== null) {
                self::$sessionName = self::$context->getProperty('service.session_cookie');
            } else {
                self::$sessionName = 'TSSESSION' . self::$context->getProperty('service.domain') . self::$context->getProperty('service.path');
                self::$sessionName = preg_replace('/[^a-zA-Z0-9]/', '', self::$sessionName);
            }
        }
        return self::$sessionName;
    }

    public static function read($id) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $current = Timestamp::getUNIXtime();
        $id = POD::escapeString($id);

        $sql = "SELECT privilege, updated, expires FROM " . self::$context->getProperty('database.prefix') . "Sessions WHERE id = '$id' AND expires > $current";
        if ($result = self::query('row', $sql)) {
            if ($result['updated'] == 1) {
                $expires = $current + self::$context->getProperty('service.timeout');
                $sql = "UPDATE " . self::$context->getProperty('database.prefix') . "Sessions SET updated = $current, expires = $expires WHERE id = '$id'";
                self::query('query', $sql);
            }
            return $result['privilege'];
        }
        return '';
    }

    public static function write($id, $data) {
        if (is_null(self::$context)) {
            self::initialize();
        }

        if (strlen($id) < 32) {
            return false;
        }
        $userid = Acl::getIdentity('textcube');
        if (empty($userid)) {
            $userid = Acl::getIdentity('openid') ? SESSION_OPENID_USERID : '';
        }
        if (empty($userid)) {
            $userid = 'null';
        }
        $id = POD::escapeString($id);
        $data = POD::escapeString($data);
        $server = POD::escapeString($_SERVER['HTTP_HOST']);
        $request = POD::escapeString(substr($_SERVER['REQUEST_URI'], 0, 255));
        $referer = isset($_SERVER['HTTP_REFERER']) ? POD::escapeString(substr($_SERVER['HTTP_REFERER'], 0, 255)) : '';
        $timer = Timer::getMicroTime() - self::$sessionMicrotime;
        $current = Timestamp::getUNIXtime();
        $result = self::query('count', "UPDATE " . self::$context->getProperty('database.prefix') . "Sessions
				SET userid = $userid, privilege = '$data', server = '$server', request = '$request', referer = '$referer', timer = $timer, updated = IF(updated,$current,1)
				WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
        if ($result && $result == 1) {
            @POD::commit();
            return true;
        }
        return false;
    }

    public static function destroy($id, $setCookie = false) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $id = POD::escapeString($id);
        self::query('query', "DELETE FROM " . self::$context->getProperty('database.prefix') . "Sessions " .
//			WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
            "WHERE id = '$id'");
        self::gc();
    }

    public static function gc($maxLifeTime = false) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        self::query('query', "DELETE FROM " . self::$context->getProperty('database.prefix') . "Sessions
			WHERE expires < " . Timestamp::getUNIXtime());
        $result = self::query('all', "SELECT DISTINCT v.id, v.address
			FROM " . self::$context->getProperty('database.prefix') . "SessionVisits v
			LEFT JOIN " . self::$context->getProperty('database.prefix') . "Sessions s ON v.id = s.id AND v.address = s.address
			WHERE s.id IS NULL AND s.address IS NULL");
        if ($result) {
            $gc = array();
            foreach ($result as $g) {
                self::query('query', "DELETE FROM " . self::$context->getProperty('database.prefix') . "SessionVisits WHERE id = '{$g['id']}' AND address = '{$g['address']}'");
            }
        }
        return true;
    }

    private static function getAnonymousSession() {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $current = Timestamp::getUNIXtime();
        $result = self::query('cell', "SELECT id FROM " . self::$context->getProperty('database.prefix') . "Sessions WHERE address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL AND expires > $current");
        if ($result) {
            return $result;
        }
        return false;
    }

    private static function newAnonymousSession() {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $current = Timestamp::getUNIXtime();
        $meet_again_baby = $current + 60;

        for ($i = 0; $i < 3; $i++) {
            if (($id = self::getAnonymousSession()) !== false) {
                return $id;
            }
            $id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
            $result = self::query('count', "INSERT INTO " . self::$context->getProperty('database.prefix') . "Sessions (id, address, server, request, referer, created, updated, expires) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', '', '', '', $current, 0, $meet_again_baby)");
            if ($result > 0) {
                self::gc();
                return $id;
            }
        }
        return false;
    }

    public static function setSessionAnonymous($currentId) {
        $id = self::getAnonymousSession();
        if ($id !== false) {
            if ($id != $currentId) {
                session_id($id);
            }
            return true;
        }
        $id = self::newAnonymousSession();
        if ($id !== false) {
            session_id($id);
            return true;
        }
        return false;
    }

    public static function isAuthorized($id) {
        /* OpenID and Admin sessions are treated as authorized ones*/
        if (is_null(self::$context)) {
            self::initialize();
        }
        $id = POD::escapeString($id);

        $result = self::query('cell', "SELECT id
			FROM " . self::$context->getProperty('database.prefix') . "Sessions
			WHERE id = '$id' "
//				AND address = '{$_SERVER['REMOTE_ADDR']}'
            . "AND (userid IS NOT NULL OR preexistence IS NOT NULL)");
        if ($result) {
            return true;
        }
        return false;
    }

    public static function isGuestOpenIDSession($id) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $id = POD::escapeString($id);
        $result = self::query('cell', "SELECT id
			FROM " . self::$context->getProperty('database.prefix') . "Sessions
			WHERE id = '$id'" .
//				AND address = '{$_SERVER['REMOTE_ADDR']}' AND userid < 0");
            " AND userid < 0");
        if ($result) {
            return true;
        }
        return false;
    }

    public static function set() {
        self::$sessionMicrotime = Timer::getMicroTime();
        if (!empty($_GET['TSSESSION'])) {
            $id = $_GET['TSSESSION'];
            $_COOKIE[session_name()] = $id;
        } else {
            if (!empty($_COOKIE[session_name()])) {
                $id = $_COOKIE[session_name()];
            } else {
                $id = '';
            }
        }
        if ((strlen($id) < 32) || !self::isAuthorized($id)) {
            self::setSessionAnonymous($id);
        }
    }

    public static function authorize($blogid, $userid, $expires = null) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        $blogid = intval($blogid);
        $userid = intval($userid);
        if (!Validator::isInteger($expires, 0)) {
            return false;
        }
        $session_cookie_path = "/";
        $t = self::$context->getProperty('service.session_cookie_path');
        if (!empty($t)) {
            $session_cookie_path = self::$context->getProperty('service.session_cookie_path');
        }
        if (!is_numeric($userid)) {
            return false;
        }
        $current = Timestamp::getUNIXtime();
        if (is_null($expires)) {
            $expires = $current + self::$context->getProperty('service.timeout');
        }
        if ($userid != SESSION_OPENID_USERID) { /* OpenID session : -1 */
            $_SESSION['userid'] = $userid;
            $id = session_id();
            if (self::isGuestOpenIDSession($id)) {
                $result = self::query('execute', "UPDATE " . self::$context->getProperty('database.prefix') . "Sessions " .
//					SET userid = $userid WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
                    "SET userid = $userid WHERE id = '$id'");
                if ($result) {
                    return true;
                }
            }
        }
        if (self::isAuthorized(session_id())) {
            return true;
        }
        for ($i = 0; $i < 3; $i++) {
            $id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
            $result = self::query('execute', "INSERT INTO " . self::$context->getProperty('database.prefix') . "Sessions
				(id, address, userid, created, updated, expires)
				VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', $userid, $current, $current, $expires)");
            if ($result) {
                @session_id($id);
                //$service['domain'] = $service['domain'].':8888';
                setcookie(self::getName(), $id, 0, $session_cookie_path, self::$context->getProperty('service.session_cookie_domain'));
                return true;
            }
        }
        return false;
    }

    /* Customized queryset (for recovering Session tables) */
    private static function query($mode = 'query', $sql) {
        if (is_null(self::$context)) {
            self::initialize();
        }

        $result = self::DBQuery($mode, $sql);
        if ($result === false) {
            if (self::$sessionDBRepair === false) {
                @POD::query("REPAIR TABLE " . self::$context->getProperty('database.prefix') . "Sessions");
                @POD::query("REPAIR TABLE " . self::$context->getProperty('database.prefix') . "SessionVisits");
                $result = self::DBQuery($mode, $sql);
                self::$sessionDBRepair = true;
            }
        }
        return $result;
    }

    private static function DBQuery($mode = 'query', $sql) {
        switch ($mode) {
            case 'cell':
                return POD::queryCell($sql);
            case 'row':
                return POD::queryRow($sql);
            case 'execute':
                return POD::execute($sql);
            case 'count':
                return POD::queryCount($sql);
            case 'all':
                return POD::queryAll($sql);
            case 'query':
            default:
                return POD::query($sql);
        }
        return null;
    }
}

?>
