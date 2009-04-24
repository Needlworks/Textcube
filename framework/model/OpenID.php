<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'OPENID_LIBRARY_ROOT', ROOT . "/library/contrib/phpopenid/" );
define( 'XPATH_LIBRARY_ROOT', ROOT . "/library/contrib/phpxpath/" );
define( 'Auth_OpenID_NO_MATH_SUPPORT', 1 );
define( 'OPENID_PASSWORD', "-OPENID-" );

$path_extra = dirname(__FILE__);
$path = ini_get('include_path');

if( !isset( $_ENV['OS'] ) || strstr( $_ENV['OS'], 'Windows' ) === false ) {
	$path .= ':' . OPENID_LIBRARY_ROOT . ':' . $path_extra;
} else {
	$path .= ';' . OPENID_LIBRARY_ROOT . ';' . $path_extra;
}
ini_set('include_path', $path);
if( !file_exists("/dev/urandom") ) { 
	define('Auth_OpenID_RAND_SOURCE', null);
}

include_once OPENID_LIBRARY_ROOT."Auth/Yadis/XML.php";
include_once XPATH_LIBRARY_ROOT."XPath.class.php";

class Auth_Textcube_xmlparser extends XPath
{
	function __construct()
	{
		$this->ns = array();
        $xmlOptions = array(XML_OPTION_CASE_FOLDING => false, XML_OPTION_SKIP_WHITE => TRUE);
        parent::XPath( FALSE, $xmlOptions );
        $this->bDebugXmlParse = false;
    }

    function init($xml_string, $namespace_map)
    {
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

    function setXML($xml_string)
    {
    	return $this->importFromString( $xml_string );
    }

    function evalXPath($xpath, $node = null)
    {
    	if( $xpath[0] != '/' ) { $xpath = "//$xpath"; }
    	$nodes = $this->evaluate($xpath);
    	$return_nodes = array();
    	foreach( $nodes as $n ) {
    		$node = $this->nodeIndex[$n];
    		$node['text'] = join( '', $node['textParts'] );
    		$return_nodes[] = $node;
    	}
    	return $return_nodes;
    }

    function content($node)
    {
		return $node['text'];
    }

    function attributes($node)
    {
        if (isset($node['attributes'])) {
				return $node['attributes'];
        }
		return null;
    }
}

class Model_OpenID {
	function setCookie( $key, $value )
	{
		$session_cookie_path = "/";
		if( !empty($service['session_cookie_path']) ) {
			$session_cookie_path = $service['session_cookie_path'];
		}
		if( !headers_sent() ) {
			setcookie( $key, $value, time()+3600*24*30, $session_cookie_path );
		}
	}

	function clearCookie( $key )
	{
		$session_cookie_path = "/";
		if( !empty($service['session_cookie_path']) ) {
			$session_cookie_path = $service['session_cookie_path'];
		}
		if( !headers_sent() ) {
			setcookie( $key, '', time()-3600, $session_cookie_path );
		}
	}

	function getDisplayName( $openid )
	{
		$s = split( '#', $openid );
		$openid = $s[0];
		if( strlen($openid) > 40 ) {
			$openid = substr($openid,0,36) . "...";
		}
		return $openid;
	}

}

class Model_OpenIDSession {
	function __construct($tid) {
		$this->pickle_key = $tid;
	}

    function set($name, $value)
    {
		$tr = Model_Transaction::taste( $this->pickle_key );
        $tr[$name] = $value;
		Model_Transaction::repickle( $this->pickle_key, $tr );
    }

    function get($name, $default=null)
    {
		$tr = Model_Transaction::taste( $this->pickle_key );
        if (array_key_exists($name, $tr)) {
            return $tr[$name];
        } else {
            return $default;
        }
    }

    function del($name)
    {
		$tr = Model_Transaction::taste( $this->pickle_key );
        unset($tr[$name]);
		Model_Transaction::repickle( $this->pickle_key, $tr );
    }

    function contents()
    {
		$tr = Model_Transaction::taste( $this->pickle_key );
        return $tr;
    }
}

class Model_OpenIDConsumer extends Model_OpenID {
	function __construct($tid = null) {
		set_include_path(get_include_path() . PATH_SEPARATOR . OPENID_LIBRARY_ROOT);
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/SReg.php";
		require_once "Auth/OpenID/AX.php";
		restore_include_path();

		$store_path = ROOT . "/cache/openidstore";

		if (!file_exists($store_path) &&
			!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		} else if( false == fopen( $store_path."/check", "w" ) ) {
			print "Could not create a file on the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		unlink( $store_path."/check" );

		$store = new Auth_OpenID_FileStore($store_path);

		/**
		 * Create a consumer object using the store object created earlier.
		 */
		if( $tid ) {
			$this->session = new Model_OpenIDSession( $tid );
		} else {
			$this->session = null;
		}

		$this->consumer = new Auth_OpenID_Consumer($store, $this->session );
	}

	function fetch( $openid )
	{
		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();
		return $auth_request->endpoint->claimed_id;
	}

	function fetchXRDSUri( $openid )
	{
		global $TextCubeLastXRDSUri, $TextCubeDoNotUseAcceptHeader;
		$TextCubeLastXRDSUri = '';
		$TextCubeDoNotUseAcceptHeader = true;

		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();

		if (!$auth_request) {
			return array( '', '', '' );
		}

		if( $auth_request->endpoint->local_id ) {
			$IdPIdentity = $auth_request->endpoint->local_id; 
		} else {
			$IdPIdentity = $auth_request->endpoint->claimed_id; 
		}
		return array( 
			$IdPIdentity,
			$auth_request->endpoint->server_url, 
			$TextCubeLastXRDSUri );
	}

	function tryAuth( $tid, $openid, $remember_openid = null )
	{
		global $hostURL, $blogURL;
		$trust_root = $hostURL . "/";
		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();

		// Handle failure status return values.
		if (!$auth_request) {
			return $this->_redirectWithError( _text("인증하지 못하였습니다. 아이디를 확인하세요"), $tid );
		}

		if( ! $this->IsExisted( $auth_request->endpoint->claimed_id ) )
		{
			if( $auth_request->message->isOpenID2() ) {
				$ax_nickname = Auth_OpenID_AX_AttrInfo::make( 'http://axschema.org/namePerson/friendly', 1, true, 'nickname' );
				$ax_request = new Auth_OpenID_AX_FetchRequest();
				$ax_request->add( $ax_nickname );
				$auth_request->addExtension( $ax_request );
			} else {
				$sreg_request = Auth_OpenID_SRegRequest::build( null, array( 'nickname' ) );
				$auth_request->addExtension( $sreg_request );
			}
		}

		if( $remember_openid ) {
				$this->setCookie( 'openid',
						empty($auth_request->endpoint->display_identifier) ?
						$auth_request->endpoint->claimed_id : $auth_request->endpoint->display_identifier );
		} else {
				$this->clearCookie( 'openid' );
		}

		$tr = Model_Transaction::taste( $tid );
		$finishURL = $tr['finishURL'];
		$redirect_url = $auth_request->redirectURL($trust_root, $finishURL);

		return $this->redirect( $redirect_url );
	}

	function finishAuth( $tid )
	{
		global $hostURL, $blogURL;
		// Complete the authentication process using the server's response.
		$tr = Model_Transaction::taste($tid);
		ob_start();
		$response = $this->consumer->complete($tr['finishURL']);
		ob_end_clean();

		$msg = '';
		if( $response->status == Auth_OpenID_CANCEL ) {
			// This means the authentication was cancelled.
			$msg = _text("인증이 취소되었습니다.");
		} else if ($response->status == Auth_OpenID_FAILURE) {
			$msg = _text("오픈아이디 인증이 실패하였습니다: ") . $response->message;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			$this->openid = $response->identity_url;
			$this->delegatedid = $response->endpoint->local_id;
			$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
			$this->sreg = $sreg_resp->contents();
			if( !isset($this->sreg['nickname']) ) {
				$this->sreg['nickname'] = "";
			}
			$msg = '';
			if( empty($tr['authenticate_only']) ) {
				$this->setAcl( $this->openid );
				$this->update( $this->openid, $this->delegatedid, $this->sreg['nickname'] );
				if( !empty($tr['need_writers']) ) {
					if( !Acl::check( 'group.writers') ) {
						$msg = _text("관리자 권한이 없는 오픈아이디 입니다") . " : " . $this->openid;
					}
				}
				fireEvent( "AfterOpenIDLogin", $this->openid );
			} else {
				Acl::authorize('openid_temp', $this->openid);
			}
		}

		return $msg ? $this->_redirectWithError( $msg, $tid ) : $this->_redirectWithSucess( $tid );
	}

	function _redirectWithError($msg, $tid)
	{
		$tr = Model_Transaction::unpickle( $tid );
		$requestURI = $tr['requestURI'];
		if( !empty($tr['authenticate_only']) ) {
			$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=0";
		} else {
			$this->setCookie( 'openid_auto', 'n' );
		}
		$this->printErrorReturn( $msg, $requestURI );
	}

	function _redirectWithSucess($tid)
	{
		$tr = Model_Transaction::unpickle( $tid );
		$requestURI = $tr['requestURI'];
		if( !empty($tr['authenticate_only']) ) {
			$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=1";
		} else {
			$this->setCookie( 'openid_auto', 'y' );
		}
		$this->redirect( $requestURI );
	}

	function printErrorReturn( $msg, $location )
	{
		$query = split( '\?', $location );
		$query = array_pop($query);
		parse_str($query,$args);
		if( !empty($args['tid']) ) {
			$tid = $args['tid'];
			$tr = Model_Transaction::taste($tid);
			$tr['openid_errormsg'] = $msg;
			Model_Transaction::repickle($tid,$tr);
			header( "Location: $location" );
		} else {
			header("HTTP/1.0 200 OK");
			header("Content-type: text/html");
			print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert('$msg');";
			if( $location ) {
				print "document.location.href='$location';";
			}
			print "//]]>" . CRLF . "</script></body></html>";
		}
		exit(0);
	}

	function redirect( $location )
	{
		header("HTTP/1.0 302 Moved Temporarily");
		header("Location: $location");
		print( "<html><body></body></html>" );
		exit(0);
	}

	function isExisted($openid)
	{
		global $database;
		$blogid = getBlogId();
		$openid = Data_IAdapter::escapeString($openid);

		$query = "SELECT openid FROM {$database['prefix']}OpenIDUsers WHERE blogid={$blogid} and openid='{$openid}'";
		$result = Data_IAdapter::queryCell($query);

		if (is_null($result)) {
			return false;
		}
		return true;
	}

	function setUserInfo( $nickname, $homepage )
	{
		if( !isset( $_SESSION['openid'] ) ) {
			$_SESSION['openid'] = array();
		}
		$_SESSION['openid']['nickname'] = $nickname;
		$_SESSION['openid']['homepage'] = $homepage;
	}

	function logout()
	{
		Acl::authorize('openid', null );
		Model_OpenID::setCookie( 'openid_auto', 'n' );
		Model_OpenIDConsumer::clearUserInfo();
	}

	function clearUserInfo()
	{
		unset( $_SESSION['openid'] );
	}

	function updateUserInfo( $nickname, $homepage )
	{
		global $database;
		$openid = Acl::getIdentity( 'openid' );
		if( empty($openid) ) {
			return false;
		}
		$openid = Data_IAdapter::escapeString($openid);
		$query = "SELECT data FROM {$database['prefix']}OpenIDUsers WHERE openid='{$openid}'";
		$result = Data_IAdapter::queryCell($query);
		$data = unserialize( $result );

		if( !empty($nickname) ) $data['nickname'] = $nickname;
		if( !empty($homepage) ) $data['homepage'] = $homepage;
		Model_OpenIDConsumer::setUserInfo( $data['nickname'], $data['homepage'] );

		$data = serialize( $data );
		Data_IAdapter::execute("UPDATE {$database['prefix']}OpenIDUsers SET data='{$data}' where openid = '{$openid}'");
	}

	function update($openid,$delegatedid,$nickname,$homepage=null)
	{
		global $database;
		$blogid = getBlogId();
		$openid = Data_IAdapter::escapeString($openid);
		$delegatedid = Data_IAdapter::escapeString($delegatedid);

		$query = "SELECT data FROM {$database['prefix']}OpenIDUsers WHERE openid='{$openid}'";
		$result = Data_IAdapter::queryCell($query);

		if (is_null($result)) {
			$data = serialize( array( 'nickname' => $nickname, 'homepage' => $homepage ) );
			Model_OpenIDConsumer::setUserInfo( $nickname, $homepage );

			/* Owner column is used for reference, all openid records are shared */
			Data_IAdapter::execute("INSERT INTO {$database['prefix']}OpenIDUsers (blogid,openid,delegatedid,firstLogin,lastLogin,loginCount,data) VALUES ($blogid,'{$openid}','{$delegatedid}',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,'{$data}')");
		} else {
			$data = unserialize( $result );

			if( !empty($nickname) ) $data['nickname'] = $nickname;
			if( !empty($homepage) ) $data['homepage'] = $homepage;
			Model_OpenIDConsumer::setUserInfo( $data['nickname'], $data['homepage'] );

			$data = serialize( $data );
			Data_IAdapter::execute("UPDATE {$database['prefix']}OpenIDUsers SET data='{$data}', lastLogin = UNIX_TIMESTAMP(), loginCount = loginCount + 1 where openid = '{$openid}'");
		}
		return;
	}

	function setAcl($openid)
	{
		global $database;

		Acl::authorize('openid', $openid);

		$blogid = getBlogId();
		$query = "SELECT userid FROM {$database['prefix']}UserSettings WHERE name like 'openid.%' and value='".Data_IAdapter::escapeString($openid)."' order by userid";
		$result = Data_IAdapter::queryRow($query);

		$userid = null;
		if( $result ) {
			$userid = $result['userid'];
			Acl::authorize('textcube', $userid);
		}

		if( !empty($userid) && in_array( "group.writers", Acl::getCurrentPrivilege() ) ) {
			Session::authorize($blogid, $userid);
		} else {
			Session::authorize($blogid, SESSION_OPENID_USERID );
		}
	}

	function setDelegate( $openid )
	{
		if( !Acl::check( array("group.creators") ) ) {
			return false;
		}
		$openid_server = '';
		$xrds_uri = '';
		if( $openid ) {
			list( $openid, $openid_server, $xrds_uri ) = $this->fetchXRDSUri( $openid );
		}
		if( Utils_Misc::setBlogSettingGlobal( "OpenIDDelegate", $openid ) && 
			Utils_Misc::setBlogSettingGlobal( "OpenIDServer", $openid_server ) && 
			Utils_Misc::setBlogSettingGlobal( "OpenIDXRDSUri", $xrds_uri ) ) {
			return true;
		}
		return false;
	}

	function setComment( $mode )
	{
		if( !Acl::check( array("group.administrators") ) ) {
			return false;
		}
		return Utils_Misc::setBlogSettingGlobal( "AddCommentMode", empty($mode) ? '' : 'openid' );
	}

	function setOpenIDLogoDisplay( $mode )
	{
		if( !Acl::check( array("group.administrators") ) ) {
			return false;
		}
		return Utils_Misc::setBlogSettingGlobal( "OpenIDLogoDisplay", $mode  );
	}

	function getCommentInfo($blogid,$id){
		global $database;

		$sql="SELECT * FROM {$database['prefix']}Comments WHERE a.blogid = $blogid AND a.id = $id";
		return Data_IAdapter::queryRow($sql, MYSQL_ASSOC);
	}

	function commentFetchHint( $comment_ids, $blogid )
	{
		echo "KILL ME, Where are you?"; exit;
	}
}
?>
