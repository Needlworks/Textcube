<?php
define('TATTERTOOLS_NAME', 'Tattertools');
define('TATTERTOOLS_VERSION', '1.1a7 development branch');
define('TATTERTOOLS_COPYRIGHT', 'Copyright ⓒ 2004-2006. Tatter &amp; Company / Tatter &amp; Friends.');
define('TATTERTOOLS_HOMEPAGE', 'http://www.tattertools.com/');
define('TATTERTOOLS_SYNC_URL', 'http://sync.eolin.com/');
define('CRLF', "\r\n");
define('TAB', "	");

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
$service['timezone'] = 'Asia/Seoul';
$service['encoding'] = 'EUC-KR';
$service['umask'] = 0;
$service['skin'] = 'Tattertools_skyline_ko';
//$service['flashuploader'] = false;

if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
	$service['port'] = $_SERVER['SERVER_PORT'];

function requireComponent($name) {
	if (!ereg('^[[:alnum:]]+[[:alnum:].]+$', $name))
		return;
	include_once (ROOT . "/components/$name.php");
}
requireComponent('Eolin.PHP.UnifiedEnvironment');
requireComponent('Eolin.PHP.Core');
requireComponent('Tattertools.Core');
requireComponent('Tattertools.Core.BackwardCompatibility');
?>
