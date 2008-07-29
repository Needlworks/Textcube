<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// This file...
//   is executed AT FIRST.
//   specifies the main workflow of Textcube 2.0.

define('TEXTCUBE_VERSION', '2.0');

/* Load config.php. */
if (file_exists('config.php')) {
	require_once('config.php');
} else {
	echo "TODO: redirect to setup.";
	exit;
}

/* Initialize class loader. */
include('loader.php');

/* TODO: Unify the environment and do work-arounds. (For IIS, Apache - mod_php or fastcgi, lighttpd, and etc.) */

/* TODO: Parse and normalize URI. */
// Structure of fancy URL:
//   host + blog prefix + interface path + pagination info + extra arguments not in $_REQUEST

/* TODO: Session management. */

/* TODO: Special pre-handlers. (favicon.ico, index.gif) */

/* TODO: Load final interface handler. */
// Check existence of interface path.
// Each interface...
//   validates passed arguments through IV.
//   specify required ACL/permissions and check them.
//   loads its necessary libraries, models and components.
// before actual execution.

?>
