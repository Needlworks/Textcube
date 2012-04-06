<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel('blog.entry');

if(!Validator::filename($_GET['skin']) && $_GET['skin'] != "customize/$blogid")
	Respond::NotFoundPage();
$skinSetting['skin'] = $_GET['skin'];
$skin = new Skin($skinSetting['skin'], true);
list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);

require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/entries.php';

$pageTitle = _t('스킨 미리보기');

require ROOT . '/interface/common/blog/end.php';
?>
