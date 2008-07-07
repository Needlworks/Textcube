<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// This file...
//   is executed AT FIRST.
//   specifies the main workflow of Textcube 2.0.

/* TODO: Load config.php and vital libraries. */
// Redirect to setup process if there's no config.php.

/* TODO: Unify the environment and do work-arounds. (For IIS, Apache - mod_php or fastcgi, lighttpd, and etc.) */

/* TODO: Parse and normalize URI. */
// Structure of fancy URL:
//   host + blog prefix + interface path + pagination info + extra arguments not in $_REQUEST

/* TODO: Session management. */

/* TODO: Special pre-handlers. (favicon.ico, index.gif) */

/* TODO: Load more libraries depending on handler types. (outside, admin, feeder, ...) */
// integrate includeForBlog.php, includeForBlogOwner.php, includeForFeeder.php, etc. here.

/* TODO: Load final interface handler. */
// Check existence of interface path.
// Each interface...
//   validates passed arguments through IV.
//   specify required ACL/permissions and check them.
//   loads its necessary libraries, models and components.
// before actual execution.

?>
