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

/// Initialize class loader.
include(ROOT.'/library/base.php');
include(ROOT.'/library/settings.php');
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
/*if(isset($accessInfo)) {
	$basicIV = array(
		'fullpath' => array('string'),
		'input'    => array('string'),
		'position' => array('string'),
		'root'     => array('string'),
		'input'    => array('string', 'mandatory' => false)
	);
	$accessInfo['fullpath'] = urldecode($accessInfo['fullpath']);
	Validator::validateArray($accessInfo, $basicIV);
}*/

/* TODO: Database Initialization (if necessary)
/* TODO: Parse virtual blog information (if necessary)
/* TODO: Session management. (if necessary) */
/* TODO: ACL validation */

/* Load final interface handler. */
// Each interface...
//   validates passed arguments through IV.
//   specify required ACL/permissions and check them.
//   loads its necessary libraries, models and components.
// before actual execution.
require(ROOT.'/'.$context->accessInfo['interfacePath']);

?>
