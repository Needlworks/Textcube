<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/**
This file...
   is executed AT FIRST.
   specifies the main workflow of Textcube 2.0.
*/
define('TEXTCUBE_VERSION', '2.0');
define('ROOT', '.');

/// Load config.php.
if (file_exists(ROOT.'/config.php')) {
	require_once(ROOT.'/config.php');
} else {
	require(ROOT.'/setup.php');
	exit;
}

/// Initialize class loader.
include(ROOT.'/framework/base.php');
include(ROOT.'/framework/settings.php');
include(ROOT.'/framework/loader.php');
$config = Config::getInstance();

// Parse and normalize URI. */
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

/* Special pre-handlers. (favicon.ico, index.gif) */
if ($context->accessInfo['prehandler']) {
	// Skip further processes such as session management.
	require(ROOT.'/'.$context->accessInfo['interfacePath']);
	exit;
}

/* TODO: Session management. */

// TODO: Do input validation as soon as possible?
/* Load final interface handler. */
// Each interface...
//   validates passed arguments through IV.
//   specify required ACL/permissions and check them.
//   loads its necessary libraries, models and components.
// before actual execution.
require(ROOT.'/'.$context->accessInfo['interfacePath']);

?>
