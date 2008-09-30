<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('TEXTCUBE_NAME', 'Textcube');
define('TEXTCUBE_VERSION', '1.5.4.1 : Fermata');
define('TEXTCUBE_COPYRIGHT', 'Copyright &copy; 2004-2007. Needlworks / Tatter Network Foundation. All rights reserved. Licensed under the GPL.');
define('TEXTCUBE_HOMEPAGE', 'http://www.textcube.org/');
define('TEXTCUBE_SYNC_URL', 'http://ping.eolin.com/');
define('CRLF', "\r\n");
define('TAB', "	");
define('INT_MAX',2147483647);
if( strstr( PHP_OS, "WIN") !== false ) {
	define('DS', "\\");
} else {
	define('DS', "/");
}

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
$service['skin'] = 'coolant';
//$service['flashuploader'] = false;

if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
	$service['port'] = $_SERVER['SERVER_PORT'];

function requireComponent($name) {
	//if (!preg_match('/^[a-zA-Z0-9\.]+$/', $name))		return;
	$name = str_replace('Tattertools', 'Textcube',$name);
	include_once (ROOT . "/components/$name.php");
}

function requireModel($name) {
	include_once (ROOT . "/lib/model/$name.php");
}


function requireView($name) {
	include_once (ROOT . "/lib/view/$name.php");
}


function requireLibrary($name) {
	include_once (ROOT . "/lib/$name.php");
}

requireComponent('Eolin.PHP.UnifiedEnvironment');
requireComponent('Eolin.PHP.Core');
requireComponent('Textcube.Core');
requireComponent('Textcube.Core.BackwardCompatibility');
requireComponent('Needlworks.Cache.PageCache');

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
