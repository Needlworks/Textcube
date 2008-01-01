<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Basics
require ROOT .'/lib/config.php';
require ROOT .'/lib/database.php';
require ROOT .'/lib/auth.php';
// Models
require ROOT .'/lib/model/blog.service.php';
require ROOT .'/lib/model/common.plugin.php';
require ROOT .'/lib/model/common.setting.php';
// Initialize
define('NO_SESSION',true);
require ROOT .'/lib/initialize.php';

header('Content-Type: text/html; charset=utf-8');
?>
