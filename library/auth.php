<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function login($loginid, $password, $preKnownPassword = null) {
	$ctx = Model_Context::getInstance();
	$loginid = POD::escapeString($loginid);
	$blogid = getBlogId();
	$userid = Auth::authenticate($blogid , $loginid, $password );

	if( $userid === false ) {
		return false;
	}

	if (empty($_POST['save'])) {
		setcookie('TSSESSION_LOGINID', '', time() - 31536000, $ctx->getProperty('service.path') . '/', $ctx->getProperty('service.domain'));
	} else {
		setcookie('TSSESSION_LOGINID', $loginid, time() + 31536000, $ctx->getProperty('service.path') . '/', $ctx->getProperty('service.domain'));
	}

	if( in_array( "group.writers", Acl::getCurrentPrivilege() ) ) {
		Session::authorize($blogid, $userid);
	}
	return true;
}

function logout() {
	fireEvent("Logout");
	Acl::clearAcl();
	Transaction::clear();
	session_destroy();
}

function requireLogin() {
	global $service, $hostURL, $blogURL;
	if(isset($_POST['refererURI'])) $_GET['refererURI'] = $_POST['refererURI'];
	else if(isset($_SESSION['refererURI'])) { 
		$_GET['refererURI'] = $_SESSION['refererURI'];
		unset($_SESSION['refererURI']);
	}
	if (!empty($service['loginURL'])) {
		header("Location: {$service['loginURL']}?requestURI=" . rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}") . (isset($_GET['refererURI']) && !empty($_GET['refererURI']) ? "&refererURI=". rawurlencode($_GET['refererURI']) : ''));
	} else {
		$requestURI = rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}") .  (isset($_GET['refererURI']) && !empty($_GET['refererURI']) ? "&refererURI=". rawurlencode($_GET['refererURI']) : '');

		header ("Location: $hostURL$blogURL/login?requestURI=" . $requestURI );
	}
	exit;
}

function doesHaveMembership() {
	return Acl::getIdentity('textcube') !== null;
}

function requireMembership() {
	global $hostURL;
	if( doesHaveMembership() ) return true;
	$_SESSION['refererURI'] = $hostURL.$_SERVER['REQUEST_URI'];
	requireLogin();
}

function getUserId() {
	return intval(Acl::getIdentity('textcube'));
}


function getBlogId() {
	global $blogid;
	return intval($blogid);
}

function setBlogId($id) {
	global $blogid;
	$blogid = $id;
}

function doesHaveOwnership($extra_aco=null) {
	return Acl::check( array("group.administrators","group.writers"), $extra_aco);
}

function requireOwnership() {
	if (doesHaveOwnership())
		return true;
	requireLogin();
	return false;
}

function requireStrictRoute() {
	if (isset($_SERVER['HTTP_REFERER']) && ($url = parse_url($_SERVER['HTTP_REFERER'])) && ($url['host'] == $_SERVER['HTTP_HOST']))
		return;
	header('HTTP/1.1 412 Precondition Failed');
	header('Content-Type: text/html');
	header("Connection: close");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo _t('Precondition Failed');?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	<h1><?php echo _t('Precondition Failed');?></h1>
</body>
</html>
<?php
	exit;
}

function requireStrictBlogURL() {
	$context = Model_Context::getInstance();
	if($context->getProperty('uri.isStrictBlogURL') == true) return;
	header('HTTP/1.1 404 Not found');
	exit;
}

function requirePrivilege($AC) {
	if(Acl::check($AC)) return true;
	else header('HTTP/1.1 404 Not found');
	exit;
}

function validateAPIKey($blogid, $loginid, $key) {
	global $service;
	$loginid = POD::escapeString($loginid);
	$key = POD::escapeString($key);
	$userid = User::getUserIdByEmail($loginid);
	if( $userid === false ) { return false; }
	$currentAPIKey = Setting::getUserSettingGlobal('APIKey',null,$userid);
	if($currentAPIKey == null) {
		if(!User::confirmPassword($userid, $key)) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
	} else if($currentAPIKey != $key) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	return true;
}

function isLoginId($blogid, $loginid) {
	global $database;
	$loginid = POD::escapeString($loginid);
	
	// 팀블로그 :: 팀원 확인
	$result = POD::queryCount("SELECT u.userid 
			FROM {$database['prefix']}Users u, 
				{$database['prefix']}Privileges t 
			WHERE t.blogid = $blogid 
				AND u.loginid = '$loginid' 
				AND t.userid = u.userid");
	// End TeamBlog
	if ($result && $result === 1)
		return true;
	return false;
}

function generatePassword() {
	return strtolower(substr(base64_encode(rand(0x10000000, 0x70000000)), 3, 8));
}

function resetPassword($blogid, $loginid) {
	global $service, $blog, $hostURL, $blogURL, $serviceURL;
	if (!isLoginId($blogid, $loginid))
		return false;
	$userid = User::getUserIdByEmail($loginid);
	$password = POD::queryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = $userid");
	$authtoken = md5(generatePassword());
	
	$query = DBModel::getInstance();
	$query->reset('UserSettings');
	$query->setAttribute('userid',$userid);
	$query->setAttribute('name','Authtoken',true);
	$query->setAttribute('value',$authtoken,true);
	$query->setQualifier('userid',$userid);
	$query->setQualifier('name','Authtoken',true);
	$query->replace();
	
	if(empty($result)) {
		return false;
	}
	//$headers = "From: Your Textcube Blog <textcube@{$service['domain']}>\n" . 'X-Mailer: ' . TEXTCUBE_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	$message = file_get_contents(ROOT . "/resources/style/letter/letter.html");
	$message = str_replace('[##_title_##]', _text('텍스트큐브 블로그 로그인 정보'), $message);
	$message = str_replace('[##_content_##]', _text('블로그 로그인을 위한 임시 암호가 생성 되었습니다. 이 이메일에 로그인할 수 있는 인증 정보가 포함되어 있습니다.'), $message);
	$message = str_replace('[##_images_##]', $serviceURL."/resources/style/letter", $message);
	$message = str_replace('[##_link_##]', "$hostURL$blogURL/login?loginid=" . rawurlencode($loginid) . '&password=' . rawurlencode($authtoken) . '&requestURI=' . rawurlencode("$hostURL$blogURL/owner/setting/account?password=" . rawurlencode($password)), $message);
	$message = str_replace('[##_link_title_##]', _text('여기를 클릭하시면 로그인하여 암호를 변경하실 수 있습니다.'), $message);
	$message = str_replace('[##_sender_##]', '', $message);
	$ret = sendEmail('Your Textcube Blog',"textcube@{$service['domain']}",'',$loginid, encodeMail(_text('블로그 로그인 암호가 초기화되었습니다.')), $message );
	if (true !== $ret) {
		return false;
	}
	return true;
}
?>
