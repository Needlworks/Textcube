<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$period = Timestamp::getYearMonth();
require ROOT . '/lib/piece/blog/begin.php';
if ($skinSetting['showListOnArchive'] == 1 || $skinSetting['showListOnArchive'] == 2) {
	$listWithPaging = getEntryListWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	require ROOT . '/lib/piece/blog/list.php';
}
$entries = array();
if ($skinSetting['showListOnArchive'] == 1 || $skinSetting['showListOnArchive'] == 0) {
	list($entries, $paging) = getEntriesWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnPage']);
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
