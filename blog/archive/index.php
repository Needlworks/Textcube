<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$period = Timestamp::getYearMonth();
if ($skinSetting['showListOnArchive'] == 1 || $skinSetting['showListOnArchive'] == 2) {
	$listWithPaging = getEntryListWithPagingByPeriod($owner, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
}
$entries = array();
if ($skinSetting['showListOnCategory'] == 1 || $skinSetting['showListOnCategory'] == 0)
	list($entries, $paging) = getEntriesWithPagingByPeriod($owner, $period, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
