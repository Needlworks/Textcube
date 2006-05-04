<?
/// Copyright © 2004-2006, Tatter & Company. All rights reserved.

ini_set('session.use_trans_sid', '0');
if (intval(ini_get("session.auto_start")) == 1) {
   @session_destroy();
   @ini_set('session.auto_start', '0');
}

if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) {
	function stripSlashesRecursively($value) {
		if (is_array($value))
			return array_map('stripSlashesRecursively', $value);
		else if (is_string($value))
			return stripslashes($value);
		else
			return $value;
	}

	$_GET = array_map('stripSlashesRecursively', $_GET);
	$_POST = array_map('stripSlashesRecursively', $_POST);
	$_COOKIE = array_map('stripSlashesRecursively', $_COOKIE);
	$_ENV = array_map('stripSlashesRecursively', $_ENV);
	//$_FILES = array_map('stripSlashesRecursively', $_FILES);
	$_REQUEST = array_map('stripSlashesRecursively', $_REQUEST);
	$_SERVER = array_map('stripSlashesRecursively', $_SERVER);
}

$host = explode(':', $_SERVER['HTTP_HOST']);
if (count($host) > 1) {
	$_SERVER['HTTP_HOST'] = $host[0];
	$_SERVER['SERVER_PORT'] = $host[1];
}
unset($host);

if (!function_exists('iconv')) {
	if (function_exists('mb_convert_encoding')) {
		function iconv($in, $out, $str) {
			return mb_convert_encoding($str, $out, $in);
		}
	} else {
		include_once(ROOT . '/iconv.php');
	}
}
?>