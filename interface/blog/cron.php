<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

header('Content-Type: text/html; charset=utf-8');
require ROOT . '/library/preprocessor.php';
requireModel("blog.cron");
doCronJob();
echo "\r\n<!-- cron -->";
?>
