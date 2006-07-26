<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
$period = $suri['id'];
if (($suri['page'] === true || $suri['page'] === '1') && $skinSetting['showListOnArchive'])
	$list = array('title' => getPeriodLabel($period), 'items' => getEntryListByPeriod($owner, $period));
list($entries, $paging) = getEntriesWithPagingByPeriod($owner, $period, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>