<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'OPENID_PLUGIN_VERSION', 1.0 ); 
define( 'OPENID_PASSWORD', "-OPENID-" );
if( !defined( 'OPENID_REGISTERS' ) ) {
	define('OPENID_REGISTERS', 10);
}

global $hostURL, $service;
global $openid_pluginbase;
$openid_pluginbase = $hostURL . $service['path'] . "/plugins/" . basename(dirname( __FILE__ ));

require_once  "openid_session.php";
requireComponent( "Textcube.Function.misc" );

openid_session_read();

function openid_login()
{
	global $hostURL, $blogURL, $service;
	global $openid_session;
	global $openid_session_id;

	$requestURI = $_GET['requestURI'];

	require "affiliate.php";

	$img_url = $hostURL . $service['path'] . "/plugins/" . basename(dirname( __file__ )) . "/login-bg.gif";

	if( !empty($_COOKIE['openid']) ) {
		$openid_remember_check = "checked";
		$cookie_openid = $_COOKIE['openid'];
	} else {
		$openid_remember_check = "";
		$cookie_openid = '';
	}

	if( strlen($openid_session_id) >= 32 ) {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html><head>
<title>텍스트큐브 오픈아이디 인증</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="' . $service['path'] . '/style/admin/default/basic.css" />
<link rel="stylesheet" type="text/css" href="' . $service['path'] . '/style/admin/default/login.css" />
<style>
.login-button
{
	position						 : static;
	background-color                 : #FFFFFF;
	background-position              : left top;
	display                          : inline;
	font-weight                      : bold;
	height                           : 3em;
	line-height                      : 3em;
	width                            : 6em;
	border                           : 1px solid #777777;
	cursor                           : pointer;
	margin-right					 : 5px;
}

#logo-box
{
	text-align                       : center;
}

dl
{
	margin-left                      : 70px;
}
dd
{
	margin-top						 : 1em;
	margin-left                      : 0;
}
dd .input-text
{
	border                           : 1px solid #999999;
	font-family                      : "Lucida Grande", Tahoma, Arial, Verdana, sans-serif;
	font-size                        : 1.3em;
	padding                          : 3px 0 3px 5px;
	width                            : 208px;
}
#data-outbox {
	width:650px;
} 

#rember_login {
	padding-top: 10px;
}

#openid_identifier {
	padding-left: 30px; 
	background: url(' . $img_url . ') no-repeat; 
	height: 1.5em; 
	width:400px;
	font-size: 1.5em;
	font-weight: bold;
	font-family: arial;
}
</style>
</head>
<body id="body-login" onload="document.getElementById(\'openid_identifier\').focus()">
	<div id="temp-wrap">
		<div id="all-wrap">
			<form method="get" name="openid_form" action="' . $blogURL . '/plugin/openid/try_auth">
				<div id="data-outbox">
					<div id="login-box">
						<div id="logo-box">
							<img src="' . $service['path'] . '/style/admin/default/image/logo_textcube.png" alt="텍스트큐브 로고" />
			            	<p><b>텍스트큐브 오픈아이디 로그인</b></p>
			            </div>
			            
			            <div id="field-box">
			            	<dl id="email-line">
			            		<dt><label for="loginid">' . _text('오픈아이디') . '</label></dt>

			            		<dd><input type="text" class="input-text" id="openid_identifier" name="openid_identifier" value="' . $cookie_openid . '" maxlength="256" tabindex="1" /></dd>
			            		<dd><input type="checkbox" class="checkbox" id="openid_remember" name="openid_remember" ' . $openid_remember_check. ' /><label for="openid_auto">' . _text('오픈아이디 기억') . '</label></dd>
			            		<dd><input type="submit" class="login-button" name="openid_login" value="로그인" /><input type="submit" class="login-button" name="openid_cancel" value="취소" /></dd>
			            		<dd><a href="' . $openid_help_link . '">' . _text('오픈아이디란?') . '</a> | <a href="' . $openid_signup_link . '">' . _text('오픈아이디 발급하기') . '</a></dd>
							</dl>
						</div>
					</div>
				</div>
        		<input type="hidden" name="requestURI" value="' . $requestURI . '" />
			</form>
		</div>
	</div>
</body>
</html>
';
	} else {
	echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html><head>
<title>텍스트큐브 오픈아이디 인증</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="' . $service['path'] . '/style/admin/default/basic.css" />
<link rel="stylesheet" type="text/css" href="' . $service['path'] . '/style/admin/default/login.css" />
</head>
<body id="body-login">
<script type="text/javascript">
//<![CDATA[
alert("Session creation error' . $openid_session_id . '");
//]]>
</script>
</body>
</html>
';
	}
}

function _openid_update_id($openid,$delegatedid,$nickname,$homepage=null,$userid=null)
{
	global $database, $blogid;
	global $openid_session;
	$openid = mysql_tt_escape_string($openid);
	$delegatedid = mysql_tt_escape_string($delegatedid);

	$query = "SELECT data FROM {$database['prefix']}OpenIDUsers WHERE openid='{$openid}'";
	$result = DBQuery::queryCell($query);

	if (is_null($result)) {
		$data = serialize( array( 'nickname' => $nickname, 'homepage' => $homepage, 'acl' => '' ) );
		$openid_session['nickname'] = $nickname;
		$openid_session['homepage'] = $homepage;

		/* Owner column is used for reference, all openid records are shared */
		DBQuery::execute("insert into {$database['prefix']}OpenIDUsers (blogid,openid,delegatedid,firstLogin,lastLogin,loginCount,data) values ($blogid,'{$openid}','{$delegatedid}',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,'{$data}')");
	} else {
		$data = unserialize( $result );

		if( !empty($nickname) ) $data['nickname'] = $nickname;
		if( !empty($homepage) ) $data['homepage'] = $homepage;
		if( $userid !== null ) $data['acl'] = $userid;

		$openid_session['nickname'] = $data['nickname'];
		$openid_session['homepage'] = $data['homepage'];

		if( !isset($data['acl']) ) {
			$data['acl'] = '';
		}

		$data = serialize( $data );
		DBQuery::execute("update {$database['prefix']}OpenIDUsers set data='{$data}', lastLogin = UNIX_TIMESTAMP(), loginCount = loginCount + 1 where openid = '{$openid}'");
	}
	return;
}

function _openid_existed($openid)
{
	global $database, $blogid;
	$openid = mysql_tt_escape_string($openid);

	$query = "SELECT openid FROM {$database['prefix']}OpenIDUsers WHERE blogid={$blogid} and openid='{$openid}'";
	$result = DBQuery::queryCell($query);

	if (is_null($result)) {
		return false;
	}
	return true;
}

function _openid_authorizeSession($userid) {
	global $database, $service;
	if (!is_numeric($userid))
		return false;
	$_SESSION['userid'] = $userid;
	if (isSessionAuthorized(session_id()))
		return true;
	for ($i = 0; $i < 100; $i++) {
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = DBQuery::query("INSERT INTO {$database['prefix']}Sessions(id, address, userid, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', $userid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if ($result && (mysql_affected_rows() == 1)) {
			@session_id($id);
			header("Set-Cookie: TSSESSION=$id; path=/; domain={$service['domain']}");
			return true;
		}
	}
	return false;
}

function _openid_set_acl($openid)
{
	global $database;

	Acl::authorize('openid', $openid);

	$blogid = getBlogId();
	$query = "SELECT * FROM {$database['prefix']}OpenIDUsers WHERE blogid={$blogid} and openid='{$openid}'";
	$result = DBQuery::queryRow($query);
	$data = unserialize( $result['data'] );

	if( !isset($data['acl']) ) {
		return;
	}

	$userid = $data['acl'];

	if( empty($userid) || !class_exists( "Acl" ) ) {
		return;
	}

	/* Check Acl class and use Auth class.. this is normal */
	Acl::authorize('textcube', $userid);
	Acl::setBasicAcl($userid);
	Acl::setTeamAcl($userid);

	if( in_array( "group.writers", Acl::getCurrentPrivilege() ) ) {
		authorizeSession($blogid, $userid);
	} else {
		authorizeSession($blogid, null);
	}

}

function openid_GetCurrent($target)
{
	global $openid_session;
	if( empty($openid_session['id'] )) {
		return '';
	}
	return $openid_session['id'];
}

function openid_try_auth()
{
	global $hostURL, $blogURL;
	if( isset($_GET['openid_remember']) ) {
		$openid_remember = true;
	} else {
		$openid_remember = false;
	}

	if( !empty($_GET['authenticate_only'])) {
		$authenticate_only = '1';
	} else {
		$authenticate_only = '';
	}

	$openid = $_GET['openid_identifier'];
	$requestURI = $_GET['requestURI'];
	if( empty($requestURI) ) {
		$requestURI = $blogURL;
	}

	if( isset($_GET['openid_cancel']) || isset($_GET['openid_cancel_x']) ) {
		header( "Location: " . $blogURL);
		exit(0);
	}

	if (empty($openid)) {
		openid_setcookie( 'openid_auto', 'n' );
		print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert('" . _text("오픈ID를 입력하세요") . "');";
		print "document.location.href='$blogURL/plugin/openid/login?requestURI=" . urlencode($requestURI) . "';//]]>" . CRLF . "</script></body></html>";
		exit(0);
	}

	return _openid_try_auth( $openid, $requestURI, $openid_remember, $authenticate_only );
}

function openid_Fetch( $openid, $xrdsuri = false )
{
	require_once  "common.php";
	require_once  "xmlwrapper.php";

	static $xmlparser = null;
	if( !$xmlparser ) $xmlparser = new Services_Textcube_xmlparser();
	Services_Yadis_setDefaultParser( $xmlparser );

	global $TextCubeLastXRDSUri;
	$TextCubeLastXRDSUri = '';

	// Begin the OpenID authentication process.
	ob_start();
	$auth_request = $consumer->begin($openid);
	ob_end_clean();

	if (!$auth_request) {
		return "";
	}

	if( $xrdsuri ) {
		if( $auth_request->endpoint->delegate ) {
			$IdPIdentity = $auth_request->endpoint->delegate; 
		} else {
			$IdPIdentity = $auth_request->endpoint->identity_url; 
		}
		return array( 
			$IdPIdentity,
			$auth_request->endpoint->server_url, 
			$TextCubeLastXRDSUri );
	}
	return $auth_request->endpoint->identity_url;
}

function openid_SetUserId($openid)
{
	_openid_update_id( $openid, null, null, null, getUserId() );
	return "";
}

function openid_ResetUserId($openid)
{
	_openid_update_id( $openid, null, null, null, "" );
	return "";
}

function _openid_try_auth( $openid, $requestURI, $openid_remember, $authenticate_only )
{
	global $hostURL, $blogURL;
	require_once  "common.php";
	require_once  "xmlwrapper.php";

	static $xmlparser = null;
	if( !$xmlparser ) $xmlparser = new Services_Textcube_xmlparser();

	Services_Yadis_setDefaultParser( $xmlparser );

	$process_url = $hostURL . $blogURL . "/plugin/openid/finish?authenticate_only=$authenticate_only&requestURI=" . urlencode($requestURI);
	$trust_root = $hostURL . "/";

	// Begin the OpenID authentication process.
	ob_start();
	$auth_request = $consumer->begin($openid);
	ob_end_clean();

	unset($_SESSION['verified_openid']);

	// Handle failure status return values.
	if (!$auth_request) {
		openid_setcookie( 'openid_auto', 'n' );
		if( !empty($authenticate_only) ) {
			$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=0";
		}
		print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert('" . _text("인증하지 못하였습니다. 아이디를 확인하세요") . "');document.location.href='" . $requestURI . "';//]]>" . CRLF . "</script></body></html>";
		exit(0);
	}

	if( ! _openid_existed( $auth_request->endpoint->identity_url ) )
	{
		$auth_request->addExtensionArg('sreg', 'optional', 'nickname');
	}

	if( $openid_remember ) {
			openid_setcookie( 'openid', $auth_request->endpoint->identity_url );
	} else {
			openid_clearcookie( 'openid' );
	}

	$redirect_url = $auth_request->redirectURL($trust_root, $process_url);

	header("HTTP/1.0 302 Moved Temporarily");
	header("Location: ".$redirect_url);

	// Hack for avoiding textcube zero-length content
	print( "<html><body>Textcube</body></html>" );
	exit(0);
}

function openid_finish()
{
	global $openid_session;
	// Complete the authentication process using the server's response.
	require_once  "common.php";

	ob_start();

	$response = $consumer->complete($_GET);

	if ($response->status == Auth_OpenID_CANCEL) {
		// This means the authentication was cancelled.
		$msg = '인증이 취소되었습니다.';
	} else if ($response->status == Auth_OpenID_FAILURE) {
		$msg = "오픈아이디 인증이 실패하였습니다: " . $response->message;
	} else if ($response->status == Auth_OpenID_SUCCESS) {
		// This means the authentication succeeded.
		$openid = $response->identity_url;
		$sreg = $response->extensionResponse('sreg');
		if( !isset($sreg['nickname']) ) {
			$sreg['nickname'] = "";
		}

		if( empty($_GET['authenticate_only']) ) {
			$openid_session['id'] = $openid;
			$openid_session['delegatedid'] = $response->endpoint->delegate;
			_openid_update_id( $response->identity_url, $response->endpoint->delegate, $sreg['nickname'] );
			_openid_set_acl( $response->identity_url );
			openid_session_write();
		} else {
			Acl::authorize('openid_temp', $openid);
		}
	}

	$requestURI = $_GET['requestURI'];
	if( !empty($_GET['authenticate_only']) && $msg ) {
		$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=0";
	}

	if( $msg )
	{
		ob_end_clean();
		openid_setcookie( 'openid_auto', 'n' );
		header("HTTP/1.0 200 OK");
		header("Content-type: text/html");
		print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert(\"$msg\"); document.location.href=\"$requestURI\";//]]>" . CRLF . "</script></body></html>";
	}
	else
	{
		ob_end_clean();
		openid_setcookie( 'openid_auto', 'y' );
		header("HTTP/1.0 302 Moved Temporarily");
		header("Location: $requestURI");

		// Hack for avoiding textcube zero-length content
		print( "<html><body></body></html>" );
	}
	ob_flush();
}

function openid_SessionLogout($target)
{
	global $openid_session;
	openid_session_destroy();
	Acl::authorize('openid', null );

	$openid_session['id'] = '';
	$openid_session['nickname'] = '';
	openid_session_write();
	openid_setcookie( 'openid_auto', 'n' );
	return "";
}

function openid_logout()
{
	openid_SessionLogout('');

	header("HTTP/1.0 302 Moved Temporarily");
	header("Location: ".$_GET['requestURI']);

	// Hack for avoiding textcube zero-length content
	print( "<html><body></body></html>" );
	exit;
}

function _openid_additional_script()
{
	global $blogURL;
	return '
			function deleteComment(id) {
				width = 450;
				height = 400;
				try {
					if(openWindow != \'\') openWindow.close();
				} catch (e) {}
				openWindow = window.open("' . $blogURL . '/plugin/openid/comment/delete?id=" + id, "textcube", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			
			function commentComment(parent) {	
				width = 450;
				height = 380;
				try {
					if(openWindow != \'\') openWindow.close();
				} catch (e) {}
				openWindow = window.open("' . $blogURL . '/plugin/openid/comment/comment?id=" + parent, "textcube", "width="+width+",height="+height+",location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0");
				openWindow.focus();
				alignCenter(openWindow,width,height);
			}
			';
}
function openid_hardcore_login($target)
{
	global $openid_session;
	if( !isset($_COOKIE['openid_auto']) || $_COOKIE['openid_auto'] != 'y' ) {
		return $target;
	}
	if( !empty($openid_session['id']) ) {
		return $target;
	}
	if( empty($_COOKIE['openid']) ) {
		return $target;
	}
	_openid_try_auth( $_COOKIE['openid'], $_SERVER["REQUEST_URI"], true, '' );
	/* Never return */
	return $target;
}

function openid_add_delegate($target)
{
	global $suri;
	$openid_delegate = misc::getBlogSettingGlobal( 'OpenIDDelegate', '' );
	$openid_server = misc::getBlogSettingGlobal( 'OpenIDServer', '' );
	$openid_xrduri = misc::getBlogSettingGlobal( 'OpenIDXRDSUri', '' );
	if( empty($openid_delegate) ) {
		return $target;
	}
	if( $suri['directive'] != '/' ) {
		return $target;
	}
	$target ="<!--OpenID Delegation Begin-->
<link rel='openid.server' href='$openid_server' />
<link rel='openid.delegate' href='$openid_delegate' />
<meta http-equiv='X-XRDS-Location' content='$openid_xrduri' />
<!--OpenID Delegation End-->
$target";
	header( "X-XRDS-Location: $openid_xrduri" );
	return $target;
}

function openid_add_controller($target)
{
	global $hostURL, $service, $blogURL;
	global $openid_session;
	global $openid_pluginbase;
	$script_url = $openid_pluginbase . "/openid.js";

	$openid_id = "";
	$openid_nickname = "";

	if( isset($openid_session['id']) )
	{
		$openid_id = $openid_session['id'];
		$openid_nickname = $openid_session['nickname'];
		openid_session_write();
	}
	else
	{
		$openid_loggedin = 0;
	}
	if( misc::getBlogSettingGlobal('AddCommentMode', '') == 'openid' && !Acl::check('group.writers') ) {
		$openid_add_comment_only_by_openid = 1;
	} else {
		$openid_add_comment_only_by_openid = 0;
	}
	$openid_add_comment_only_by_openid_msg = _t('관리자의 설정에 의해 오픈아이디로 로그인한 사용자만 댓글을 남길 수 있습니다');
	$target .= "<script type='text/javascript'>//<![CDATA[\n" .
		"var openid_entryurl = \"$hostURL$blogURL/plugin/openid/\";\n" .
		"var openid_pluginbase = \"$openid_pluginbase/\";\n" .
		"var openid_id = '$openid_id';\n" .
		"var openid_add_comment_only_by_openid = $openid_add_comment_only_by_openid;\n" .
		"var openid_add_comment_only_by_openid_msg = '$openid_add_comment_only_by_openid_msg';\n" .
		"var openid_nickname = '$openid_nickname';\n" .
		_openid_additional_script() . CRLF .
		"//]]></script>\n" .
		"<script type=\"text/javascript\" src=\"$script_url\"></script>\n";
	return $target;
}

function openid_LOGIN_add_form($target, $requestURI)
{
	global $hostURL, $blogURL, $service;
	global $openid_session;
	global $openid_session_id;

	$img_url = $hostURL . $service['path'] . "/plugins/" . basename(dirname( __file__ )) . "/login-bg.gif";

	require "affiliate.php";

	if( !empty($_COOKIE['openid']) ) {
		$openid_remember_check = "checked";
		$cookie_openid = $_COOKIE['openid'];
	} else {
		$openid_remember_check = "";
		$cookie_openid = '';
	}
	$target .= '
<style type="text/css">
#openid-temp-wrap {width: 230px; margin: 20px -10px 0 340px;}
#openid-line { margin: 0; padding-right: 5px;}
#openid-all-wrap { position:relative; width: 230px; }
#openid-field-box { width: 230px; }
#openid_identifier { font-size: 1.3em; padding-left: 30px; width: 183px; background: url(' . $img_url . ') no-repeat; }
.openid-login-button { display: inline; width: 74px; height: 3em; cursor: pointer; padding: 0pt 5px; font-size: 1em; font-weight: bold; font-family:\'Lucida Grande\',Arial,굴림,Gulim,Tahoma,Verdana,sans-serif; background-color: #fff; border: 1px solid ; vertical-align: middle}
#openid-login-button { float: right; margin: 15px 10px 5px 20px; left: 100px }
#openid-remember { display:block; margin-top: 10px; }
#openid-help { display:block; }
</style>
	<form method="get" name="openid_form" action="' . $blogURL . '/plugin/openid/try_auth">
	<div id="openid-temp-wrap">
		<hr size="1">
		<div id="openid-all-wrap">
			<div id="openid-field-box">
				<dl id="openid-line">
					<dt><label for="loginid">' . _text('관리자 계정과 연결된 오픈아이디') . '</label></dt>

					<dd><input type="text" class="input-text" id="openid_identifier" name="openid_identifier" value="' . $cookie_openid . '" maxlength="256" /></dd>
					<input type="submit" class="openid-login-button" id="openid-login-button" name="openid_login" value="로그인" />
					<dd id="openid-remember"><input type="checkbox" class="checkbox" name="openid_remember" ' . $openid_remember_check. ' /><label for="openid_auto">' . _text('오픈아이디 저장') . '</label></dd>
					<dd id="openid-help"><a href="' . $openid_help_link . '">' . _text('오픈아이디란?') . '</a> </dd>
					<dd><a href="' . $openid_signup_link . '">' . _text('오픈아이디 발급하기') . '</a></dd>
				</dl>
			</div>
			<input type="hidden" name="requestURI" value="' . $requestURI . '" />
		</div>
	</div>
	</form>
	<script type="text/javascript">//<![CDATA[' . CRLF . 'function focus_openid(){document.getElementById("openid_identifier").focus();}//]]>' . CRLF . '</script>
	';
	return $target;
}

function _openid_set_temp_password( $blogid, $id )
{
	global $database;
	$pw = md5( 'seed for hash' . time() . filemtime( ROOT .DS.'config.php') );
	$pw = substr($pw, 0, 32);
	DBQuery::execute("UPDATE {$database['prefix']}Comments SET password = '" . md5($pw) . "' WHERE blogid = $blogid and id = $id" );
	return $pw;
}

function _openid_get_auth_id()
{
	global $openid_session;
	if( !isset( $openid_session['id'] ) ) {
		return '';
	}
	return $openid_session['id'];
}

function _openid_has_ownership($trying_openid)
{
	global $openid_session;
	if( empty($trying_openid) ) return false;
	if( !isset($openid_session['id']) ) return false;
	if( $trying_openid == $openid_session['id'] ) return true;
	if( isset($openid_session['delegatedid']) && $trying_openid == $openid_session['delegatedid'] ) return true;
	return false;
}

function _openid_fix_table()
{
	global $database, $store_path;

	$checkup_path = ROOT .DS."cache".DS."_php_consumer";
	if( ! file_exists( $checkup_path ) ) {
		mkdir( $checkup_path, 0777 );
	}

	$checkup_path = ROOT .DS."cache".DS."_php_consumer".DS."checkup";
	$openid_check_magic = '2007-07-31';
	$fix0 = false;
	$fix1 = false;
	$fix2 = true;
	$fix3 = false;
	$fix4 = false;

	if( file_exists($checkup_path) && ($fixed = file_get_contents( $checkup_path) ) ) {
		if( $fixed == $openid_check_magic ) {
			return;
		}
	}

	$rows = DBQuery::queryAll("DESC {$database['prefix']}OpenIDUsers");
	foreach( $rows as $row ) {
		if( $row['Field'] == 'owner' )    { $fix0 = true; }
		if( $row['Field'] == 'blocked' )  { $fix1 = true; }
		if( $row['Field'] == 'data' )     { $fix2 = false; }
		if( $row['Field'] == 'nickname' ) { $fix3 = true; }
	}
	$rows = DBQuery::queryAll("DESC {$database['prefix']}OpenIDComments");
	foreach( $rows as $row ) {
		if( $row['Field'] == 'owner' )    { $fix4 = true; }
	}

	if( $fix0 ) {
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers change column owner blogid int(11) not null default 0");
	}
	if( $fix1 ) {
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers drop column blocked");
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers drop column admin");
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers drop column member");
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers drop column comment");
	}

	if( $fix2 ) {
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers add column data text");
	}

	if( $fix3 ) {
		$rows = DBQuery::queryAll("select blogid,openid,nickname from {$database['prefix']}OpenIDUsers");
		foreach( $rows as $row ) {
			$blogid = $row["blogid"];
			$openid = $row["openid"];
			$data = serialize( array( "nickname" => $row["nickname"], "homepage" => $openid ) );
			DBQuery::execute("update {$database['prefix']}OpenIDUsers set data='{$data}' where blogid={$blogid} and openid='{$openid}'");
		}
		DBQuery::execute("alter table {$database['prefix']}OpenIDUsers drop column nickname");
	}
	if( $fix4 ) {
		DBQuery::execute("alter table {$database['prefix']}OpenIDComments change column owner blogid int(11) not null default 0");
	}
	$f = fopen( $checkup_path, "w");
	if( $f ) {
		fwrite($f,$openid_check_magic);
		fclose($f);
	}
}

_openid_fix_table();

function openid_setcomment()
{
	if( !Acl::check( array("group.administrators") ) ) {
		respondResultPage( -1);
		return;
	}
	if( misc::setBlogSettingGlobal( "AddCommentMode", empty($_GET['mode']) ? "" : "openid" ) ) {
		respondResultPage(0);
	} else {
		respondResultPage(-1);
	}
}

function openid_setdelegate()
{
	if( !Acl::check( array("group.administrators") ) ) {
		respondResultPage( -1);
		return;
	}
	$openid = empty($_GET['openid']) ? '' : $_GET['openid'];
	$openid_server = '';
	$xrds_uri = '';
	if( $openid ) {
		list( $openid, $openid_server, $xrds_uri ) = openid_Fetch( $openid, true );
	}
	if( misc::setBlogSettingGlobal( "OpenIDDelegate", $openid ) && 
		misc::setBlogSettingGlobal( "OpenIDServer", $openid_server ) && 
		misc::setBlogSettingGlobal( "OpenIDXRDSUri", $xrds_uri ) ) {
		respondResultPage(0);
	} else {
		respondResultPage(-1);
	}
}

function openid_AddingCommentViewTail( $target, $comment_id )
{
	global $database, $blogid;
	global $hostURL, $service, $blogURL;
	global $openid_session;

	if( empty($openid_session['id']) ) {
		return $target;
	}
	$comment = _openid_getCommentInfo( getBlogId(), $comment_id );
	if( $comment['secret'] ) {
		$secret_checked = "true";
	} else {
		$secret_checked = "false";
	}
	if( !empty( $comment['parent'] ) ) {
		$parent = _openid_getCommentInfo( getBlogId(), $comment['parent'] );
		if( $parent['secret'] ) {
			$secret_checked = "true";
		}
	} else {
		$parent = array();
	}
	$scr = "<script type='text/javascript'>//<![CDATA[\n
if( document.getElementById('password') ) document.getElementById('password').disabled = true;
document.getElementById('name').value = '{$openid_session['nickname']}';
document.getElementById('title').innerHTML += ' ( <img style=\"position:relative;top:3px;left:0\"; src=\"$hostURL{$service['path']}/plugins/CL_OpenID/openid16x16.gif\" alt=\"OpenID Logo\" /> {$openid_session['id']} )';
document.getElementById('secret').checked = $secret_checked;
//]]>\n</script><style type='text/css'>.password-line{display:none}</style>";
	return "$target$scr";
}

function openid_AddingComment( $target, $comment )
{
	global $openid_session;
	if( !Acl::check( "group.writers" ) ) {
		if( misc::getBlogSettingGlobal('AddCommentMode', '') == 'openid' ) {
			return $openid_session['id'] ? true : false;
		}
	}
	return true;
}

function openid_AddComment( $id, $comment )
{
	/* Assert $id is numeric by the caller function in lib/model/comment.php */

	global $openid_session;
	global $database, $blogid;

	$auth_id = _openid_get_auth_id();
	if( $auth_id )
	{ 
		$result = getCommentAttributes($blogid,$id,"name,homepage");
		_openid_update_id( $openid_session['id'], $openid_session['delegatedid'], $result['name'], $result['homepage']);
		openid_session_write();

		DBQuery::execute("UPDATE {$database['prefix']}Comments SET password = '" . OPENID_PASSWORD . "' WHERE blogid = $blogid and id = $id" );
		DBQuery::execute("DELETE FROM {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = $id" );
		DBQuery::execute("INSERT INTO {$database['prefix']}OpenIDComments (blogid,id,openid) values " .
			"( {$blogid}, {$id}, '{$auth_id}' )");
	}

	if( empty($comment['parent']) )
	{
		return;
	}

	$parent_comment = _openid_getCommentInfo( $blogid, $comment['parent'] );

	/* Check if parent's comment is written by openid and secret. */
	if( ! Acl::check('group.writers') && !_openid_has_ownership( $parent_comment['openid'] ) ) {
		return;
	}

	$result = getCommentAttributes($blogid,$comment['parent'],"secret");
	if( empty($result) || empty($result['secret']) ) {
		return;
	}

	$row = DBQuery::queryRow("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = {$comment['parent']}" );
	if( empty($row) ) {
		return;
	}
	/* Then, this administor's comment can be secret */
	DBQuery::execute("UPDATE {$database['prefix']}Comments SET secret = 1 WHERE blogid = $blogid and id = $id" );
	return;
}

function openid_ShowSecretComment($target, $comment)
{
	global $database, $blogid;
	global $hostURL, $service, $blogURL;
	global $openid_session;

	if( !$comment['secret'] || empty($openid_session['id']) ) {
		return $target;
	}
	$row = DBQuery::queryRow("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = {$comment['id']}" );
	if( !empty($row) && $row['openid'] == $openid_session['id'] ) {
		return true;
	}
	if( empty($comment['parent']) ) {
		return false;
	}
	$row = DBQuery::queryRow("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = {$comment['parent']}" );
	if( empty($row) ) {
		return $target;
	}
	if( $row['openid'] == $openid_session['id'] ) {
		return true;
	}
	return false;
}

function openid_ViewCommenter($name, $comment)
{
	global $database, $blogid;
	global $hostURL, $service, $blogURL;
	global $openid_pluginbase;

	$openid_pluginbase = $hostURL . $service['path'] . "/plugins/" . basename(dirname( __FILE__ ));

	if( $comment['secret'] ) {
		return $name;
	}
	$row = DBQuery::queryAll("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = {$comment['id']}" );
	return $name . ($row ? "<img src=\"" . $openid_pluginbase . "/openid16x16.gif\" hspace=\"2\" align=\"absmiddle\" title=\"" .
		sprintf( _text("오픈아이디(%s)로 작성하였습니다"), $row[0]['openid'] ) . "\">" : "");
}

function openid_comment_comment()
{
	global $hostURL, $blogURL;
	global $openid_session;

	if( !Acl::check('group.writers') && misc::getBlogSettingGlobal('AddCommentMode', '') == 'openid' ) {
		if( empty($openid_session['id']) ) {
			$msg = _t('관리자의 설정에 의해 오픈아이디로 로그인한 사용자만 댓글을 남길 수 있습니다.\r\n로그인하시겠습니까?');
			print( "<html><body><script type='text/javascript'>//<![CDATA[\n" );
			print( "var yn = confirm('" . $msg . "');\n" );
			print( "if(yn && window.opener) window.opener.document.location.href='$hostURL$blogURL/plugin/openid/login?requestURI='+escape( window.opener.document.location.href );" );
			print( "window.close();//]]>\n</script></body></html>" );
			exit(0);
		}
	}

	$entryId = $_GET['id'];
	header("HTTP/1.0 302 Moved Temporarily");
	header("Location: $hostURL$blogURL/comment/comment/$entryId");
	print( "<html><body></body></html>" );
	exit(0);
}

function _openid_getCommentInfo($blogid,$id){
	global $database;

	$sql="select a.*, openid from {$database['prefix']}Comments a left join {$database['prefix']}OpenIDComments b on a.id = b.id where a.blogid = $blogid and a.id = $id";
	return DBQuery::queryRow($sql, MYSQL_ASSOC);
}
/* Get and rename from original code */

function openid_comment_del()
{
	global $blogid, $defaultURL, $blog, $user, $skinSetting;
	global $service, $adminSkinSetting, $hostURL, $blogURL, $pageTitle, $comment, $suri;
	global $openid_session;

	$entryId = $_GET['id'];
	$suri['id'] = $entryId;

	if( empty($openid_session['id']) || doesHaveOwnership() || doesHaveMembership() )
	{
		header("HTTP/1.0 302 Moved Temporarily");
		header("Location: $hostURL$blogURL/comment/delete/$entryId");
		print( "<html><body></body></html>" );
		exit(0);
	}

	list($replier) = getCommentAttributes($blogid, $suri['id'], 'replier');
	$comment = _openid_getCommentInfo($blogid, $suri['id']);
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo _text('댓글 삭제') ;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/popup-comment.css" />
		<script type="text/javascript">
			//<![CDATA[
				var servicePath = "<?php echo $service['path'];?>";
				var blogURL = "<?php echo $blogURL;?>";
				var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
			//]]>
		</script>
		<script type="text/javascript" src="<?php echo $service['path'];?>/script/common2.js"></script>
	</head>
	<body>
		<form name="deleteComment" method="post" action="<?php echo $blogURL;?>/comment/delete/<?php echo $entryId;?>">
			<div id="comment-box">
				<img src="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/image/img_comment_popup_logo.gif" alt="<?php echo _text('텍스트큐브 로고');?>" />	
				<div id="command-box">
<? 
/*-------------------------------------------------------------------------------------------*/
if( ! _openid_has_ownership($comment['openid']) ) { ?>
					<div class="edit-line">
						<label>로그인된 오픈아이디의 권한으로는 수정/삭제가 불가능합니다.</label>
					</div>
					<div class="password-line">
						<input type="button" class="input-button" name="Submit" value="<?php echo _text('닫기');?>" onclick="window.close()" />				
					</div>
<? 
} else { 
	if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId())) )
	{
		if( _openid_has_ownership($comment['openid']) ) {
			$tmp_password = _openid_set_temp_password( $blogid, $suri['id'] );
		}
		else
		{
			$tmp_password = "";
		}
	}
/*-------------------------------------------------------------------------------------------*/
?>
					<div class="edit-line">
						<input type="radio" id="edit" class="radio" name="mode" value="edit" checked="checked" /><label for="edit"><?php echo _text('댓글을 수정합니다.');?></label>
					</div>
					<div class="delete-line">			
						<input type="radio" id="delete" class="radio" name="mode" value="delete" /><label for="delete"><?php echo _text('댓글을 삭제합니다.');?></label>
					</div>
					<div class="password-line">
	<?php
	if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId())) )
	{
		if( !_openid_has_ownership($comment['openid']) ) {
	?>				  
						<label for="password"><?php echo _text('비밀번호');?><span class="divider"> | </span></label><input type="password" id="password" class="input-text" name="password" />
	<?php
		} else {
	?>
						<input type="hidden" id="password" class="input-text" name="password" value="<? echo $tmp_password ?>"/>
	<?
		}
	}
	?>
						<input type="button" class="input-button" name="Submit" value="<?php echo _text('다음');?>" onclick="document.deleteComment.submit()" />				
					</div>
<? } ?>
				</div>
			</div>
		</form>
	</body>
	</html>
<?php
}

function openid_manage()
{
	global $database, $blogURL, $hostURL;

	$menu_url = $hostURL . $blogURL . "/blogid/plugin/adminMenu?name=" . $_GET['name'];
	$menu1 = $menu_url . "&amp;mode=1";
	$menu2 = $menu_url . "&amp;mode=3";
	$menu3 = $menu_url . "&amp;mode=5";
	$menu4 = $menu_url . "&amp;mode=7";
	$order = "order by lastLogin desc";

	$mode = preg_replace( '/.*mode=(.+)/', '\1', $_SERVER["QUERY_STRING"] . "mode=7");
	/* last mode=7 will be default */
	switch( $mode )
	{
	case 2:
		$menu2 = $menu_url . "&amp;mode=3"; $order = "order by delegatedid asc";
		break;
	case 3:
		$menu2 = $menu_url . "&amp;mode=2"; $order = "order by delegatedid desc";
		break;
	case 4:
		$menu3 = $menu_url . "&amp;mode=5"; $order = "order by loginCount asc";
		break;
	case 5:
		$menu3 = $menu_url . "&amp;mode=4"; $order = "order by loginCount desc";
		break;
	case 6:
		$menu4 = $menu_url . "&amp;mode=7"; $order = "order by lastLogin asc";
		break;
	case 7:
		$menu4 = $menu_url . "&amp;mode=6"; $order = "order by lastLogin desc";
		break;
	case 0:
		$menu1 = $menu_url . "&amp;mode=1"; $order = "order by openid asc";
		break;
	case 1:
		$menu1 = $menu_url . "&amp;mode=0"; $order = "order by openid desc";
		break;
	}

	$mode = misc::getBlogSettingGlobal( "AddCommentMode", "" );
	if( $mode === 'openid' ) {
		$mode = "checked='checked'";
	} else {
		$mode = "";
	}

	/* Fetch registerred openid */
	$openid_list = array();
	for( $i=0; $i<OPENID_REGISTERS; $i++ )
	{
		$openid = getUserSetting( "openid." . $i );
		if( !empty($openid) ) {
			array_push( $openid_list, $openid );
		}
	}
?>
	<script type="text/javascript">
		//<![CDATA[
	function toggle_openid_only() {
		try {
			var oo = document.getElementById( 'openidonlycomment' );
			if( ! oo ) {
				return false;
			}
			oo = oo.checked ? "1" : "0";
			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/plugin/openid/setcomment?mode=" + oo);
			request.onSuccess = function() {
				PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?php echo _t('저장하지 못했습니다.');?>");
			}
			request.send("");
		} catch(e) {
		}
	}
	function setDelegate() {
		try {
			delegatedid = document.getElementById( 'openid_for_delegation' ).value;
			if( !delegatedid ) {
				alert( "<?php echo _text('블로그 주소를 오픈아이디로 사용하지 않습니다.') ?>");
			}

			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/plugin/openid/setdelegate?openid=" + escape(delegatedid));
			request.onSuccess = function() {
				PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?php echo _t('저장하지 못했습니다.');?>");
			}
			request.send("");
		} catch(e) {
		}
	}
	//]]>
	</script>
	
	<div id="part-openid-comment" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _text('댓글/방명록 설정')?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<tbody>
			<tr class="site">
			<td><span class="text">
			<input id="openidonlycomment" type="checkbox" name="openidonlycomment" <?php echo $mode?>
				onclick="toggle_openid_only();"
			/>
			<label for="openidonlycomment">체크할 경우, 오픈아이디 로그인을 해야만 댓글 및 방명록을 쓸 수 있습니다.</label>
			</span></td>
			</tr>
			</tbody>
		</table>
	</div>
	
	<div id="part-openid-blogaddress" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _text('블로그 주소를 오픈아이디로 사용')?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<tbody>
				<tr class="site">
					<td>
<?php
		$currentDelegate = misc::getBlogSettingGlobal( 'OpenIDDelegate', 'HIHI' );
?>
						<select id="openid_for_delegation">
<?php
		print "<option value='' >" . _text('블로그 주소를 오픈아이디로 사용하지 않음');
		foreach( $openid_list as $openid ) {
			$selected = '';
			if( $openid == $currentDelegate ) {
				$selected = "selected";
			}
			print "<option value='$openid' $selected>" . $openid;
		}
?>
						</select>
						<input type="button" onclick="setDelegate(); return false" value="<?php echo _text('확인') ?>" class="save-button input-button"/>
					</td>
				</tr>
				<tr>
					<td>
						<span class="text"><?php echo sprintf( _text('블로그 주소(%s)를 관리자로 등록된 오픈아이디 중 하나에 위임하여 오픈아이디로 사용할 수 있습니다.'), "$hostURL$blogURL"); ?>
						(<a href="<?php echo $blogURL?>/owner/setting/account"><?php echo _text('관리자 계정에 추가하기')?></a>)
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="part">
		<h2 class="caption"><span class="main-text"><?php echo _text('오픈아이디 사용현황')?></span></h2>
	
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th class="site"><span class="text"><a href="<?php echo $menu1?>"><?php echo _text('오픈아이디 주소(이름)')?></a></span></th>
					<th class="site"><span class="text"><a href="<?php echo $menu2?>"><?php echo _text('위임주소')?></a></span></th>
					<th class="site"><span class="text"><a href="<?php echo $menu3?>"><?php echo _text('로그인 회수')?></a></span></th>
					<th class="site"><span class="text"><a href="<?php echo $menu4?>"><?php echo _text('마지막 로그인')?></a></span></th>
				</tr>
			</thead>
			<tbody>
<?php
$sql="select * from {$database['prefix']}OpenIDUsers $order";
$rec = DBQuery::queryAll( $sql );
for ($i=0; $i<count($rec); $i++) {
$record = $rec[$i];
$data = unserialize($record['data']);
$nickname = "({$data['nickname']})";

$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
$className .= ($i == sizeof($rec) - 1) ? ' last-line' : '';
?>
				<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
					<td><?php echo "{$record['openid']} {$nickname}";?></td>
					<td><?php echo $record['delegatedid'];?></td>
					<td><?php echo $record['loginCount'];?></td>
					<td><?php echo Timestamp::format5($record['lastLogin']);?></td>
				</tr>
<?php
}
?>
			</tbody>
		</table>
	</div>
<?
}

?>
