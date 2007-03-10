<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('TATTERTOOLS_NAME', 'Tattertools');
define('TATTERTOOLS_VERSION', '1.1.2 release candidate : Animato');
define('TATTERTOOLS_COPYRIGHT', 'Copyright &copy; 2004-2007. Tatter &amp; Company / Tatter &amp; Friends. All rights reserved. Licensed under the GPL.');
define('TATTERTOOLS_HOMEPAGE', 'http://www.tattertools.com/');
define('TATTERTOOLS_SYNC_URL', 'http://ping.eolin.com/');
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
$service['skin'] = 'tistory';
//$service['flashuploader'] = false;

if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
	$service['port'] = $_SERVER['SERVER_PORT'];

function requireComponent($name) {
	//if (!ereg('^[[:alnum:]]+[[:alnum:].]+$', $name))		return;
	include_once (ROOT . "/components/$name.php");
}
requireComponent('Eolin.PHP.UnifiedEnvironment');
requireComponent('Eolin.PHP.Core');
requireComponent('Tattertools.Core');
requireComponent('Tattertools.Core.BackwardCompatibility');

if (isset($IV)) {
	if (!Validator::validate($IV)) {
		header('HTTP/1.1 404 Not Found');
		exit;
	}
}

$basicIV = array(
	'SCRIPT_NAME' => array('string'),
	'REQUEST_URI' => array('string'),
	'REDIRECT_URL' => array('string', 'mandatory' => false)
);
Validator::validateArray($_SERVER, $basicIV);
?>
