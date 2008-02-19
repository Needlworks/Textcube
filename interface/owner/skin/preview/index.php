<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
requireModel('blog.entry');

if(!Validator::filename($_GET['skin']) && $_GET['skin'] != "customize/$blogid")
	respond::NotFoundPage();
$skinSetting['skin'] = $_GET['skin'];
$skin = new Skin($skinSetting['skin'], true);
list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);

require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';

$pageTitle = _t('스킨 미리보기');

require ROOT . '/lib/piece/blog/end.php';
?>
