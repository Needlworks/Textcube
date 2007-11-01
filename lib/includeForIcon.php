<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Basics
require 'config.php';
require 'database.php';
require 'auth.php';
// Models
require 'model/blog.service.php';
require 'model/common.plugin.php';
require 'model/common.setting.php';
// Initialize
define('NO_SESSION',true);
require 'initialize.php';

header('Content-Type: text/html; charset=utf-8');
?>
