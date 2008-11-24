<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

header('Content-Type: text/html; charset=utf-8');
require ROOT . '/library/dispatcher.php';
requireModel("blog.cron");
doCronJob();
echo "\r\n<!-- cron -->";
?>
