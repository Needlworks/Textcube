<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('SESSION_OPENID_USERID', -1);

final class Session {
    private static $sessionName = null;
    private static $mc = null;
    private static $context;
    private static $pool;

    private static function initialize() {
        global $memcache;
        /** After PHP 5.0.5, session write performs after object destruction. */
        self::$mc = $memcache;
        /** To Avoid this, just copy memcache handle into Session object.     */
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
        if (is_null(self::$mc)) {
            self::initialize();
        }
        if (self::$sessionName == null) {
            if (!is_null(self::$context->getProperty('service.session_cookie'))) {
                self::$sessionName = self::$context->getProperty('service.session_cookie');
            } else {
                self::$sessionName = 'TSSESSION' . self::$context->getProperty('service.domain') . self::$context->getProperty('service.path');
                self::$sessionName = preg_replace('/[^a-zA-Z0-9]/', '', self::$sessionName);
            }
        }
        return self::$sessionName;
    }

    public static function read($id) {
        if (is_null(self::$mc)) {
            self::initialize();
        }
        //return self::$mc->get(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
        return self::$mc->get(self::$context->getProperty('service.domain') . "/sessions/{$id}");
    }

    public static function write($id, $data) {
        if (is_null(self::$mc)) {
            self::initialize();
        }
        $id = POD::escapeString($id);
        //return self::$mc->set(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",$data,0,self::$context->getProperty('service.timeout'));
        //return self::$mc->set(self::$context->getProperty('service.domain')."/sessions/{$id}",$data,0,self::$context->getProperty('service.timeout'));
    }

    public static function destroy($id, $setCookie = false) {
        //self::$mc->delete(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
        $id = POD::escapeString($id);
        self::$mc->delete(self::$context->getProperty('service.domain') . "/sessions/{$id}", 0);
        self::$mc->delete(self::$context->getProperty('service.domain') . "/anonymousSession/{$_SERVER['REMOTE_ADDR']}", 0);
        return self::$mc->delete(self::$context->getProperty('service.domain') . "/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}", 0);
    }

    public static function gc($maxLifeTime = false) {
        if (is_null(self::$context)) {
            self::initialize();
        }
        self::$pool->reset('SessionVisits');
        self::$pool->delete();
        return true;
        /* Code below needs testing. Currently it consumes server load. Flushing make visitor counter a little bit inaccurrate, but it's neglectable.*/
        $keys = self::getMemcacheKeys();
        $authorizedIds = array();
        $authorizedAddr = array();
        $removeIds = array();
        $removeAddr = array();

        $idPattern = '/sessions\/(?<id>\w+)/i';
        $authorizedidPattern = '/authorizedSession\/(?<id>\w+)/i';
        $addrPattern = '/anonymousSession\/(?<addr>\w+)/i';
        foreach ($keys as $key) {
            if (preg_match($idPattern, $key, $matches) > 0 || preg_match($authorizedidPattern, $key, $matches) > 0) {
                array_push($authorizedIds, $matches['id']);
            } else {
                if (preg_match($addrPattern, $key, $matches) > 0) {
                    array_push($authorizedAddr, $matches['addr']);
                }
            }
        }
        self::$pool->reset('SessionVisits');
        $results = self::$pool->getAll('id, address');
        foreach ($results as $info) {
            if (!in_array($info['id'], $authorizedIds)) {
                array_push($removeIds, $info['id']);
            }
            if (!in_array($info['address'], $authorizedAddr)) {
                array_push($removeAddr, $info['id']);
            }
        }
        if (count($removeIds) < 25) {
            self::$pool->resetQualifiers();
            self::$pool->setQualifier('id', 'hasoneof', $removeIds);
            self::$pool->delete();
        } else {
            foreach ($removeIds as $id) {
                self::$pool->resetQualifiers();
                self::$pool->setQualifier('id', 'eq', $id);
                self::$pool->delete();
            }
        }
        foreach ($removeAddr as $addr) {
            self::$pool->resetQualifiers();
            self::$pool->setQualifier('address', 'eq', $addr);
            self::$pool->delete();
        }
        return true;
    }

    private static function getAnonymousSession() {
        $anonymousSessionId = self::$mc->get(self::$context->getProperty('service.domain') . "/anonymousSession/{$_SERVER['REMOTE_ADDR']}");
        if (!empty($anonymousSessionId)) {
            return $anonymousSessionId;
        } else {
            return false;
        }
    }

    private static function newAnonymousSession() {
        for ($i = 0; $i < 3; $i++) {
            if (($id = self::getAnonymousSession()) !== false) {
                return $id;
            }
            $id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
            $result = self::$mc->set(self::$context->getProperty('service.domain') . "/sessions/{$id}", true, 0, self::$context->getProperty('service.timeout'));
            if ($result > 0) {
                $result = self::$mc->set(self::$context->getProperty('service.domain') . "/anonymousSession/{$_SERVER['REMOTE_ADDR']}", $id, 0, 120); // anonymous session timeout is 120 sec.
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
        if (is_null(self::$mc)) {
            self::initialize();
        }
        $id = POD::escapeString($id);
        /* OpenID and Admin sessions are treated as authorized ones*/
        //$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
        $userid = self::$mc->get(self::$context->getProperty('service.domain') . "/authorizedSession/{$id}");
        if (!empty($userid)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isGuestOpenIDSession($id) {
        if (is_null(self::$mc)) {
            self::initialize();
        }
        $id = POD::escapeString($id);
        //$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
        $userid = self::$mc->get(self::$context->getProperty('service.domain') . "/authorizedSession/{$id}");
        if (!empty($userid) && $userid < 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function set() {
        if (is_null(self::$mc)) {
            self::initialize();
        }
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

    public static function authorize($blogid, $userid) {
        if (is_null(self::$mc)) {
            self::initialize();
        }
        $blogid = intval($blogid);
        $userid = intval($userid);
        $session_cookie_path = "/";
        if (!is_null(self::$context->getProperty('service.session_cookie_path'))) {
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
                //$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,0,self::$context->getProperty('service.timeout'));
                $result = self::$mc->set(self::$context->getProperty('service.domain') . "/authorizedSession/{$id}", $userid, 0, $expires);
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
            //$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,0,self::$context->getProperty('service.timeout'));
            $result = self::$mc->set(self::$context->getProperty('service.domain') . "/authorizedSession/{$id}", $userid, 0, self::$context->getProperty('service.timeout'));

            if ($result) {
                @session_id($id);
                setcookie(self::getName(), $id, 0, $session_cookie_path, self::$context->getProperty('service.session_cookie_domain'));
                return true;
            }
        }
        return false;
    }

    public function getMemcacheKeys($limit = 10000) {
        $keysFound = array();
        $slabs = self::$mc->getExtendedStats('slabs');
        foreach ($slabs as $serverSlabs) {
            foreach ($serverSlabs as $slabId => $slabMeta) {
                try {
                    $cacheDump = self::$mc->getExtendedStats('cachedump', (int)$slabId, 1000);
                } catch (Exception $e) {
                    continue;
                }

                if (!is_array($cacheDump)) {
                    continue;
                }

                foreach ($cacheDump as $dump) {

                    if (!is_array($dump)) {
                        continue;
                    }

                    foreach ($dump as $key => $value) {
                        $keysFound[] = $key;

                        if (count($keysFound) == $limit) {
                            return $keysFound;
                        }
                    }
                }
            }
        }
        return $keysFound;
    }
}

?>
