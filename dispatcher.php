<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/**
This file...
   is executed AT FIRST.
   specifies the main workflow of Textcube 2.0.
*/
define('ROOT', '.');

/// Check config.php.
if (!file_exists(ROOT.'/config.php')) {
	require(ROOT.'/setup.php');
	exit;
}
/// Initialize PHP environment.
include(ROOT.'/library/environment/Needlworks.PHP.UnifiedEnvironment.php');

/// Initialize class loader.
include(ROOT.'/library/base.php');
include(ROOT.'/library/settings.php');
include(ROOT.'/library/context.php');
include(ROOT.'/library/loader.php');
$config = Config::getInstance();
/** Parse and normalize URI. */
/* TODO: Unify the environment and do work-arounds. (For IIS, Apache - mod_php or fastcgi, lighttpd, and etc.) */
// Structure of fancy URL:
//   host + blog prefix + interface path + pagination info + extra arguments not in $_REQUEST
// TODO: Apply this structure to $context->accessInfo...

try {
	$context = Context::getInstance(); // automatic initialization via first instanciation
} catch (URIError $e) {
	// Check existence of interface path.
	if ($config->service['debugmode'])
		echo $e;
	exit;
}

/// Special pre-handlers. (favicon.ico, index.gif)
if ($context->accessInfo['prehandler']) {
	// Skip further processes such as session management.
	require(ROOT.'/'.$context->accessInfo['interfacePath']);
	exit;
}

/// Input Validation
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

/* Database Initialization (if necessary) */
if(POD::bind($config->database) === false) {
	Respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
	exit;
}

/* TODO: Parse virtual blog information (if necessary)*/
$context->URIParser();
$gCacheStorage = new GlobalCacheStorage;
$context->globalVariableParser();

/* TODO: Include required files */

/* TODO: Session management. (if necessary) */
if (!defined('NO_SESSION')) {
	session_name(Session::getName());
	Session::set();
	session_set_save_handler( array('Session','open'), array('Session','close'), array('Session','read'), array('Session','write'), array('Session','destroy'), array('Session','gc') );
	session_cache_expire(1);
	session_set_cookie_params(0, '/', $service['domain']);
	if (session_start() !== true) {
		header('HTTP/1.1 503 Service Unavailable');
	}
}

/* TODO: ACL validation */

/* Load final interface handler. */
// Each interface...
//   validates passed arguments through IV.
//   specify required ACL/permissions and check them.
//   loads its necessary libraries, models and components.
// before actual execution.

require(ROOT.'/'.$context->accessInfo['interfacePath']);

?>
