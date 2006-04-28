<?
define('TATTERTOOLS_NAME', 'Tattertools');
define('TATTERTOOLS_VERSION', '1.0.5 beta1');
define('TATTERTOOLS_COPYRIGHT', 'Copyright © 2004-2006, Tatter & Company');
define('TATTERTOOLS_HOMEPAGE', 'http://www.tattertools.com/');
define('TATTERTOOLS_SYNC_URL', 'http://sync.eolin.com/');
define('CRLF', "\r\n");
$database['server'] = 'localhost';
$database['database'] = '';
$database['username'] = '';
$database['password'] = '';
$database['prefix'] = '';
$service['timeout'] = 3600;
$service['type'] = 'single';
$service['domain'] = '';
$service['path'] = '';
$service['language'] = 'ko';
$service['timezone'] = 'GMT';
$service['encoding'] = 'EUC-KR';
$service['umask'] = 0;
$service['skin'] = 'Tattertools_skyline_ko';
ini_set('session.use_trans_sid', '0');
if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $value)
		$_GET[$key] = stripslashes($value);
	foreach ($_POST as $key => $value)
		$_POST[$key] = stripslashes($value);
	foreach ($_COOKIE as $key => $value)
		$_COOKIE[$key] = stripslashes($value);
}
$host = explode(':', $_SERVER['HTTP_HOST']);
if (count($host) > 1) {
	$_SERVER['HTTP_HOST'] = $host[0];
	$_SERVER['SERVER_PORT'] = $host[1];
}
unset($host);
if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
	$service['port'] = $_SERVER['SERVER_PORT'];

function requireComponent($name) {
	if (!ereg('^[[:alnum:]]+[[:alnum:].]+$', $name))
		return;
	include_once (ROOT . "/components/$name.php");
}
requireComponent('Eolin.PHP.Core');
requireComponent('Tattertools.Core');
?>
