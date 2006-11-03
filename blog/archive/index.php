<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$period = Timestamp::getYearMonth();
if ($skinSetting['showListOnArchive'] == 0 || $skinSetting['showListOnArchive'] == 2) {
	$listWithPaging = getEntryListWithPagingByPeriod($owner, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
}
if ($skinSetting['showListOnCategory'] == 0 || $skinSetting['showListOnCategory'] == 1)
	list($entries, $paging) = getEntriesWithPagingByPeriod($owner, $period, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
