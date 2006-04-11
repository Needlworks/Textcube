<?

function login($loginid, $password) {
	global $database;
	global $service;
	$loginid = mysql_escape_string($loginid);
	if ((strlen($password) == 32) && preg_match('/[0-9a-f]/i', $password))
		$secret = '(`password` = \'' . md5($password) . "' OR `password` = '$password')";
	else
		$secret = '`password` = \'' . md5($password) . '\'';
	if ($result = mysql_query("SELECT userid, loginid, name FROM {$database['prefix']}Users WHERE loginid = '$loginid' AND $secret")) {
		if ($session = mysql_fetch_array($result)) {
			authorizeSession($session['userid']);
			if (empty($_POST['save']))
				setcookie('TSSESSION_LOGINID', '', time() - 31536000, $service['path'] . '/', $service['domain']);
			else
				setcookie('TSSESSION_LOGINID', $loginid, time() + 31536000, $service['path'] . '/', $service['domain']);
			executeQuery("UPDATE  {$database['prefix']}Users SET lastLogin = unix_timestamp() WHERE loginid = '$loginid'");
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
	if (!empty($service['loginURL']))
		header("Location: {$service['loginURL']}?requestURI=" . rawurlencode("$hostURL{$_SERVER['REQUEST_URI']}"));
	else {
		header("Location: $blogURL/login?requestURI=" . rawurlencode("{$_SERVER['REQUEST_URI']}"));
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

function isLoginId($userid, $loginid) {
	global $database;
	$loginid = mysql_escape_string($loginid);
	$result = mysql_query("select userid from {$database['prefix']}Users where userid = $userid and loginid = '$loginid'");
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
	$password = fetchQueryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = $userid");
	$headers = "From: Your Tattertools Blog <tattertools@{$service['domain']}>\n" . 'X-Mailer: ' . TATTERTOOLS_NAME . "\n" . "MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	$message = file_get_contents(ROOT . "/style/letter/letter.html");
	$message = str_replace('[##_title_##]', _t('태터툴즈 블로그 로그인 정보'), $message);
	$message = str_replace('[##_content_##]', _t('블로그 로그인 암호가 초기화되었습니다. 이 이메일에 로그인할 수 있는 인증 정보가 포함되어 있습니다.'), $message);
	$message = str_replace('[##_images_##]', "$hostURL{$service['path']}/style/letter", $message);
	$message = str_replace('[##_link_##]', "$hostURL$blogURL/login?loginid=" . rawurlencode($loginid) . '&password=' . rawurlencode($password) . '&requestURI=' . rawurlencode("$hostURL$blogURL/owner/setting/account?password=" . rawurlencode($password)), $message);
	$message = str_replace('[##_link_title_##]', _t('여기를 클릭하시면 로그인하여 암호를 변경하실 수 있습니다.'), $message);
	$message = str_replace('[##_sender_##]', '', $message);
	if (!mail($loginid, encodeMail(_t('블로그 로그인 암호가 초기화되었습니다.')), $message, $headers))
		return false;
	$loginid = mysql_escape_string($loginid);
	return true;
}
?>