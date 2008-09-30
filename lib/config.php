<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Define basic signatures.
define('TEXTCUBE_NAME', 'Textcube');
define('TEXTCUBE_VERSION', '1.6.3.1 : Tenuto');
define('TEXTCUBE_COPYRIGHT', 'Copyright &copy; 2004-2008. Needlworks / Tatter Network Foundation. All rights reserved. Licensed under the GPL.');
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
// Define global variable.
global $database, $service, $blog;

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
if(defined('__TEXTCUBE_NO_FANCY_URL__')) $service['fancyURL'] = 1;
else $service['fancyURL'] = 2;
$service['useEncodedURL'] = false;
$service['debugmode'] = false;
$service['reader'] = true;
$service['flashclipboardpoter'] = true;
$service['allowBlogVisibilitySetting'] = true;
//$service['flashuploader'] = false;

// Map port setting.
if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
	$service['port'] = $_SERVER['SERVER_PORT'];

// Define Binders.
function requireComponent($name) {
	//if (!preg_match('/^[a-zA-Z0-9\.]+$/', $name))		return;
	$name = str_replace('Tattertools', 'Textcube',$name); // Legacy routine.
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

// Include core components.
// Due to the global variable scope issues, use require here instead of requireComponent.
requireComponent('Eolin.PHP.UnifiedEnvironment');
requireComponent('Eolin.PHP.Core');
requireComponent('Textcube.Core');
requireComponent('Textcube.Core.BackwardCompatibility');
requireComponent('Textcube.Function.Respond');
requireComponent('Needlworks.Cache.PageCache');

// Include installation configuration.
if(!defined('__TEXTCUBE_SETUP__')) @include ROOT . '/config.php';
if($service['debugmode'] == true) requireComponent("Needlworks.Function.Debug");

// Basic POST/GET variable validation.
if (isset($IV)) {
	if (!Validator::validate($IV)) {
		header('HTTP/1.1 404 Not Found');
		exit;
	}
}
// Basic SERVER variable validation.
$basicIV = array(
	'SCRIPT_NAME' => array('string'),
	'REQUEST_URI' => array('string'),
	'REDIRECT_URL' => array('string', 'mandatory' => false)
);
Validator::validateArray($_SERVER, $basicIV);
if(isset($accessInfo)) {
	$basicIV = array(
		'fullpath' => array('string'),
		'input'    => array('string'),
		'position' => array('string'),
		'root'     => array('string'),
		'input'    => array('string', 'mandatory' => false)
	);
	$accessInfo['fullpath'] = urldecode($accessInfo['fullpath']);
	Validator::validateArray($accessInfo, $basicIV);
}
?>
