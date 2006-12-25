<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
function login($loginid, $password) {
	global $database;
	global $service;
	$loginid = mysql_tt_escape_string($loginid);
	if ((strlen($password) == 32) && preg_match('/[0-9a-f]/i', $password))
		$secret = '(`password` = \'' . md5($password) . "' OR `password` = '$password')";
	else
		$secret = '`password` = \'' . md5($password) . '\'';
	if ($result = DBQuery::query("SELECT userid, loginid, name FROM {$database['prefix']}Users WHERE loginid = '$loginid' AND $secret")) {
//	if ($result = DBQuery::query("SELECT userid, loginid, name FROM {$database['prefix']}Users WHERE name = '$loginid' AND $secret")) {
		if ($session = mysql_fetch_array($result)) {
			authorizeSession($session['userid']);
			if (empty($_POST['save']))
				setcookie('TSSESSION_LOGINID', '', time() - 31536000, $service['path'] . '/', $service['domain']);
			else
				setcookie('TSSESSION_LOGINID', $loginid, time() + 31536000, $service['path'] . '/', $service['domain']);
			DBQuery::execute("UPDATE  {$database['prefix']}Users SET lastLogin = unix_timestamp() WHERE loginid = '$loginid'");
			return true;
		}
	}
	return false;
}

function logout() {
	session_destroy();
}

function requireLogin() {
	global $service, $hostURL, $blogURL;
	if (!empty($service['loginURL'])) {
		header("Location: {$service['loginURL']}?requestURI=" . rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}"));
	} else {
		if (String::endsWith($_SERVER['HTTP_HOST'], '.' . $service['domain']))
			header("Location: $blogURL/login?requestURI=" . rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}"));
		else
			header('Location: ' . getBlogURL() . '/login?requestURI=' . rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}"));
	}
	exit;
}

function doesHaveMembership() {
	return empty($_SESSION['userid']) ? false : true;
}

function requireMembership() {
	global $owner;
	if (empty($_SESSION['userid']))
		requireLogin();
	else
		return true;
}

function getUserId() {
	return empty($_SESSION['userid']) ? false : $_SESSION['userid'];
}

function doesHaveOwnership() {
	global $owner;
	if (empty($_SESSION['userid']) || ($_SESSION['userid'] != $owner))
		return false;
	return true;
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

function isLoginId($userid, $loginid) {
	global $database;
	$loginid = mysql_tt_escape_string($loginid);
	$result = DBQuery::query("select userid from {$database['prefix']}Users where userid = $userid and loginid = '$loginid'");
	if ($result && (mysql_num_rows($result) == 1))
		return true;
	return false;
}

function generatePassword() {
	return strtolower(substr(base64_encode(rand(0x10000000, 0x70000000)), 3, 8));
}

function resetPassword($userid, $loginid) {
	global $database;
	global $service, $blog, $hostURL, $blogURL;
	if (!isLoginId($userid, $loginid))
		return false;
	$password = DBQuery::queryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = $userid");
	$headers = "From: Your Tattertools Blog <tattertools@{$service['domain']}>\n" . 'X-Mailer: ' . TATTERTOOLS_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	$message = file_get_contents(ROOT . "/style/letter/letter.html");
	$message = str_replace('[##_title_##]', _text('태터툴즈 블로그 로그인 정보'), $message);
	$message = str_replace('[##_content_##]', _text('블로그 로그인 암호가 초기화되었습니다. 이 이메일에 로그인할 수 있는 인증 정보가 포함되어 있습니다.'), $message);
	$message = str_replace('[##_images_##]', "$hostURL{$service['path']}/style/letter", $message);
	$message = str_replace('[##_link_##]', "$hostURL$blogURL/login?loginid=" . rawurlencode($loginid) . '&password=' . rawurlencode($password) . '&requestURI=' . rawurlencode("$hostURL$blogURL/owner/setting/account?password=" . rawurlencode($password)), $message);
	$message = str_replace('[##_link_title_##]', _text('여기를 클릭하시면 로그인하여 암호를 변경하실 수 있습니다.'), $message);
	$message = str_replace('[##_sender_##]', '', $message);
	if (!mail($loginid, encodeMail(_text('블로그 로그인 암호가 초기화되었습니다.')), $message, $headers))
		return false;
	$loginid = mysql_tt_escape_string($loginid);
	return true;
}
?>
