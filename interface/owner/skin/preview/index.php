<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/dispatcher.php';
requireModel('blog.entry');

if(!Validator::filename($_GET['skin']) && $_GET['skin'] != "customize/$blogid")
	respond::NotFoundPage();
$skinSetting['skin'] = $_GET['skin'];
$skin = new Skin($skinSetting['skin'], true);
list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);

require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/entries.php';

$pageTitle = _t('스킨 미리보기');

require ROOT . '/interface/common/blog/end.php';
?>
