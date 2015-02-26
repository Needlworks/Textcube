<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$path_extra = dirname(__FILE__);
$path = ini_get('include_path');

if (!isset($_ENV['OS']) || strstr($_ENV['OS'], 'Windows') === false) {
    $path .= ':' . OPENID_LIBRARY_ROOT . ':' . $path_extra;
} else {
    $path .= ';' . OPENID_LIBRARY_ROOT . ';' . $path_extra;
}
ini_set('include_path', $path);
if (!file_exists("/dev/urandom")) {
    define('Auth_OpenID_RAND_SOURCE', null);
}

include_once OPENID_LIBRARY_ROOT . "Auth/Yadis/XML.php";
include_once XPATH_LIBRARY_ROOT . "XPath.class.php";

class Auth_Textcube_xmlparser extends XPath {
    function Auth_Textcube_xmlparser() {
        $this->ns = array();
        $xmlOptions = array(XML_OPTION_CASE_FOLDING => false, XML_OPTION_SKIP_WHITE => TRUE);
        parent::XPath(FALSE, $xmlOptions);
        $this->bDebugXmlParse = false;
    }

    function init($xml_string, $namespace_map) {
        foreach ($namespace_map as $prefix => $uri) {
            if (!$this->registerNamespace($prefix, $uri)) {
                return false;
            }
        }
        if (!$this->setXML($xml_string)) {
            return false;
        }

        return true;
    }

    function setXML($xml_string) {
        return $this->importFromString($xml_string);
    }

    function evalXPath($xpath, $node = null) {
        if ($xpath[0] != '/') {
            $xpath = "//$xpath";
        }
        $nodes = $this->evaluate($xpath);
        $return_nodes = array();
        foreach ($nodes as $n) {
            $node = $this->nodeIndex[$n];
            $node['text'] = join('', $node['textParts']);
            $return_nodes[] = $node;
        }
        return $return_nodes;
    }

    function content($node) {
        return $node['text'];
    }

    function attributes($node) {
        if (isset($node['attributes'])) {
            return $node['attributes'];
        }
        return null;
    }
}

class OpenID {
    function setCookie($key, $value) {
        $context = Model_Context::getInstance();
        $session_cookie_path = "/";
        $savedPath = $context->getProperty('service.session_cookie_path');
        if (!empty($savedPath)) {
            $session_cookie_path = $context->getProperty('service.session_cookie_path');
        }
        if (!headers_sent()) {
            setcookie($key, $value, time() + 3600 * 24 * 30, $session_cookie_path);
        }
    }

    function clearCookie($key) {
        $context = Model_Context::getInstance();
        $session_cookie_path = "/";
        $savedPath = $context->getProperty('service.session_cookie_path');
        if (!empty($savedPath)) {
            $session_cookie_path = $context->getProperty('service.session_cookie_path');
        }
        if (!headers_sent()) {
            setcookie($key, '', time() - 3600, $session_cookie_path);
        }
    }

    function getDisplayName($openid) {
        $s = explode('#', $openid);
        $openid = $s[0];
        if (strlen($openid) > 40) {
            $openid = substr($openid, 0, 36) . "...";
        }
        return $openid;
    }

}

class OpenIDSession {
    function OpenIDSession($tid) {
        $this->pickle_key = $tid;
    }

    function set($name, $value) {
        $tr = Transaction::taste($this->pickle_key);
        $tr[$name] = $value;
        Transaction::repickle($this->pickle_key, $tr);
    }

    function get($name, $default = null) {
        $tr = Transaction::taste($this->pickle_key);
        if (array_key_exists($name, $tr)) {
            return $tr[$name];
        } else {
            return $default;
        }
    }

    function del($name) {
        $tr = Transaction::taste($this->pickle_key);
        unset($tr[$name]);
        Transaction::repickle($this->pickle_key, $tr);
    }

    function contents() {
        $tr = Transaction::taste($this->pickle_key);
        return $tr;
    }
}

class OpenIDConsumer extends OpenID {
    function __construct($tid = null) {
        require_once OPENID_LIBRARY_ROOT . "Auth/OpenID/Consumer.php";
        require_once OPENID_LIBRARY_ROOT . "Auth/OpenID/FileStore.php";
        require_once OPENID_LIBRARY_ROOT . "Auth/OpenID/SReg.php";
        require_once OPENID_LIBRARY_ROOT . "Auth/OpenID/AX.php";

        $store_path = __TEXTCUBE_CACHE_DIR__ . "/openidstore";

        if (!file_exists($store_path) &&
            !mkdir($store_path)
        ) {
            print "Could not create the FileStore directory '$store_path'. " .
                " Please check the effective permissions.";
            exit(0);
        } else {
            if (false == fopen($store_path . "/check", "w")) {
                print "Could not create a file on the FileStore directory '$store_path'. " .
                    " Please check the effective permissions.";
                exit(0);
            }
        }
        unlink($store_path . "/check");

        $store = new Auth_OpenID_FileStore($store_path);

        /**
         * Create a consumer object using the store object created earlier.
         */
        if ($tid) {
            $this->session = new OpenIDSession($tid);
        } else {
            $this->session = null;
        }

        $this->consumer = new Auth_OpenID_Consumer($store, $this->session);
    }

    function fetch($openid) {
        ob_start();
        $auth_request = $this->consumer->begin($openid);
        ob_end_clean();
        return $auth_request->endpoint->claimed_id;
    }

    function fetchXRDSUri($openid) {
        global $TextCubeLastXRDSUri, $TextCubeDoNotUseAcceptHeader;
        $TextCubeLastXRDSUri = '';
        $TextCubeDoNotUseAcceptHeader = true;

        ob_start();
        $auth_request = $this->consumer->begin($openid);
        ob_end_clean();

        if (!$auth_request) {
            return array('', '', '');
        }

        if ($auth_request->endpoint->local_id) {
            $IdPIdentity = $auth_request->endpoint->local_id;
        } else {
            $IdPIdentity = $auth_request->endpoint->claimed_id;
        }
        return array(
            $IdPIdentity,
            $auth_request->endpoint->server_url,
            $TextCubeLastXRDSUri);
    }

    function tryAuth($tid, $openid, $remember_openid = null) {
        $context = Model_Context::getInstance();
        $trust_root = $context->getProperty('uri.host') . "/";
        ob_start();
        $auth_request = $this->consumer->begin($openid);
        ob_end_clean();

        // Handle failure status return values.
        if (!$auth_request) {
            return $this->_redirectWithError(_text("인증하지 못하였습니다. 아이디를 확인하세요"), $tid);
        }

        if (!$this->IsExisted($auth_request->endpoint->claimed_id)) {
            if ($auth_request->message->isOpenID2()) {
                $ax_nickname = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, true, 'nickname');
                $ax_request = new Auth_OpenID_AX_FetchRequest();
                $ax_request->add($ax_nickname);
                $auth_request->addExtension($ax_request);
            } else {
                $sreg_request = Auth_OpenID_SRegRequest::build(null, array('nickname'));
                $auth_request->addExtension($sreg_request);
            }
        }

        if ($remember_openid) {
            $this->setCookie('openid',
                empty($auth_request->endpoint->display_identifier) ?
                    $auth_request->endpoint->claimed_id : $auth_request->endpoint->display_identifier);
        } else {
            $this->clearCookie('openid');
        }

        $tr = Transaction::taste($tid);
        $finishURL = $tr['finishURL'];
        $redirect_url = $auth_request->redirectURL($trust_root, $finishURL);

        return $this->redirect($redirect_url);
    }

    function finishAuth($tid) {
        // Complete the authentication process using the server's response.
        $tr = Transaction::taste($tid);
        ob_start();
        $response = $this->consumer->complete($tr['finishURL']);
        ob_end_clean();

        $msg = '';
        if ($response->status == Auth_OpenID_CANCEL) {
            // This means the authentication was cancelled.
            $msg = _text("인증이 취소되었습니다.");
        } else {
            if ($response->status == Auth_OpenID_FAILURE) {
                $msg = _text("오픈아이디 인증이 실패하였습니다: ") . $response->message;
            } else {
                if ($response->status == Auth_OpenID_SUCCESS) {
                    $this->openid = $response->identity_url;
                    $this->delegatedid = $response->endpoint->local_id;
                    $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
                    $this->sreg = $sreg_resp->contents();
                    if (!isset($this->sreg['nickname'])) {
                        $this->sreg['nickname'] = "";
                    }
                    $msg = '';
                    if (empty($tr['authenticate_only'])) {
                        $this->setAcl($this->openid);
                        $this->update($this->openid, $this->delegatedid, $this->sreg['nickname']);
                        if (!empty($tr['need_writers'])) {
                            if (!Acl::check('group.writers')) {
                                $msg = _text("관리자 권한이 없는 오픈아이디 입니다") . " : " . $this->openid;
                            }
                        }
                        fireEvent("AfterOpenIDLogin", $this->openid);
                    } else {
                        Acl::authorize('openid_temp', $this->openid);
                    }
                }
            }
        }

        return $msg ? $this->_redirectWithError($msg, $tid) : $this->_redirectWithSucess($tid);
    }

    function _redirectWithError($msg, $tid) {
        $tr = Transaction::unpickle($tid);
        $requestURI = $tr['requestURI'];
        if (!empty($tr['authenticate_only'])) {
            $requestURI .= (strchr($requestURI, '?') === false ? "?" : "&") . "authenticated=0";
        } else {
            $this->setCookie('openid_auto', 'n');
        }
        $this->printErrorReturn($msg, $requestURI);
    }

    function _redirectWithSucess($tid) {
        $tr = Transaction::unpickle($tid);
        $requestURI = $tr['requestURI'];
        if (!empty($tr['authenticate_only'])) {
            $requestURI .= (strchr($requestURI, '?') === false ? "?" : "&") . "authenticated=1";
        } else {
            $this->setCookie('openid_auto', 'y');
        }
        $this->redirect($requestURI);
    }

    function printErrorReturn($msg, $location) {
        $query = explode('?', $location);
        $query = array_pop($query);
        parse_str($query, $args);
        if (!empty($args['tid'])) {
            $tid = $args['tid'];
            $tr = Transaction::taste($tid);
            $tr['openid_errormsg'] = $msg;
            Transaction::repickle($tid, $tr);
            header("Location: $location");
        } else {
            header("HTTP/1.0 200 OK");
            header("Content-type: text/html");
            print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert('$msg');";
            if ($location) {
                print "document.location.href='$location';";
            }
            print "//]]>" . CRLF . "</script></body></html>";
        }
        exit(0);
    }

    function redirect($location) {
        header("HTTP/1.0 302 Moved Temporarily");
        header("Location: $location");
        print("<html><body></body></html>");
        exit(0);
    }

    function isExisted($openid) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();
        $pool->reset('OpenIDUsers');
        $pool->setQualifier('blogid', 'equals', intval($context->getProperty('blog.id')));
        $pool->setQualifier('openid', 'equals', $openid, true);
        $result = $pool->getCell('openid');
        if (is_null($result)) {
            return false;
        }
        return true;
    }

    function setUserInfo($nickname, $homepage) {
        if (!isset($_SESSION['openid'])) {
            $_SESSION['openid'] = array();
        }
        $_SESSION['openid']['nickname'] = $nickname;
        $_SESSION['openid']['homepage'] = $homepage;
    }

    function logout() {
        Acl::authorize('openid', null);
        OpenID::setCookie('openid_auto', 'n');
        OpenIDConsumer::clearUserInfo();
    }

    function clearUserInfo() {
        unset($_SESSION['openid']);
    }

    function updateUserInfo($nickname, $homepage) {
        $openid = Acl::getIdentity('openid');
        if (empty($openid)) {
            return false;
        }

        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        $pool->reset('OpenIDUsers');
        $pool->setQualifier('openid', 'equals', $openid, true);
        $result = $pool->getCell('openidinfo');

        $data = unserialize($result);

        if (!empty($nickname)) {
            $data['nickname'] = $nickname;
        }
        if (!empty($homepage)) {
            $data['homepage'] = $homepage;
        }
        OpenIDConsumer::setUserInfo($data['nickname'], $data['homepage']);

        $data = serialize($data);
        $pool->reset('OpenIDUsers');
        $pool->setAttribute('openidinfo', $data, true);
        $pool->setQualifier('openid', 'equals', $openid, true);
        $pool->update();
    }

    function update($openid, $delegatedid, $nickname, $homepage = null) {
        $context = Model_Context::getInstance();
        $pool = DBModel::getInstance();

        $pool->reset('OpenIDUsers');
        $pool->setQualifier('openid', 'equals', $openid, true);
        $result = $pool->getCell('openidinfo');

        if (is_null($result)) {
            $data = serialize(array('nickname' => $nickname, 'homepage' => $homepage));
            OpenIDConsumer::setUserInfo($nickname, $homepage);

            /* Owner column is used for reference, all openid records are shared */
            $pool->reset('OpenIDUsers');
            $pool->setAttribute('blogid', $context->getProperty('blog.id'));
            $pool->setAttribute('openid', $openid, true);
            $pool->setAttribute('delegatedid', $deligatedid, true);
            $pool->setAttribute('firstlogin', Timestamp::getUNIXTime());
            $pool->setAttribute('lastlogin', Timestamp::getUNIXTime());
            $pool->setAttribute('logincount', 1);
            $pool->setAttribute('openidinfo', $data, true);
            $pool->insert();
        } else {
            $data = unserialize($result);

            if (!empty($nickname)) {
                $data['nickname'] = $nickname;
            }
            if (!empty($homepage)) {
                $data['homepage'] = $homepage;
            }
            OpenIDConsumer::setUserInfo($data['nickname'], $data['homepage']);

            $data = serialize($data);


            $pool->reset('OpenIDUsers');
            $pool->setQualifier('openid', 'equals', $openid, true);
            $lastcount = $pool->getCell('logincount');

            $pool->reset('OpenIDUsers');
            $pool->setAttribute('openidinfo', $data, true);
            $pool->setAttribute('lastlogin', Timestamp::getUNIXTime());
            $pool->setAttribute('logincount', $lastcount + 1);
            $pool->setQualifier('openid', 'equals', $openid, true);
            $pool->update();
        }
        return;
    }

    function setAcl($openid) {
        Acl::authorize('openid', $openid);
        $pool = DBModel::getInstance();
        $context = Model_Context::getInstance();
        $blogid = intval($context->getProperty('blog.id'));

        $pool->reset('UserSettings');
        $pool->setQualifier('name', 'like', 'openid.', true);
        $pool->setQualifier('value', 'equals', $openid, true);
        $pool->setOrder('userid', 'ASC');
        $result = $pool->getCell('userid');

        $userid = null;
        if ($result) {
            $userid = $result;
            Acl::authorize('textcube', $userid);
        }

        if (!empty($userid) && in_array("group.writers", Acl::getCurrentPrivilege())) {
            Session::authorize($blogid, $userid);
        } else {
            Session::authorize($blogid, SESSION_OPENID_USERID);
        }
    }

    function setDelegate($openid) {
        if (!Acl::check(array("group.creators"))) {
            return false;
        }
        $openid_server = '';
        $xrds_uri = '';
        if ($openid) {
            list($openid, $openid_server, $xrds_uri) = $this->fetchXRDSUri($openid);
        }
        if (Setting::setBlogSettingGlobal("OpenIDDelegate", $openid) &&
            Setting::setBlogSettingGlobal("OpenIDServer", $openid_server) &&
            Setting::setBlogSettingGlobal("OpenIDXRDSUri", $xrds_uri)
        ) {
            return true;
        }
        return false;
    }

    function setComment($mode) {
        if (!Acl::check(array("group.administrators"))) {
            return false;
        }
        return Setting::setBlogSettingGlobal("AddCommentMode", empty($mode) ? '' : 'openid');
    }

    function setOpenIDLogoDisplay($mode) {
        if (!Acl::check(array("group.administrators"))) {
            return false;
        }
        return Setting::setBlogSettingGlobal("OpenIDLogoDisplay", $mode);
    }

    function getCommentInfo($blogid, $id) {
        $context = Model_Context::getInstance();
        $blogid = intval($context->getProperty('blog.id'));
        $pool = DBModel::getInstance();
        $pool->reset('Comments');
        $pool->setQualifier('blogid', 'equals', $blogid);
        $pool->setQualifier('id', 'equals', $id);
        return $pool->getRow('*');
    }

    function commentFetchHint($comment_ids, $blogid) {
        echo "KILL ME, Where are you?";
        exit;
    }
}

?>
