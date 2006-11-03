<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($owner, $suri['value']);
if ($skinSetting['showListOnCategory'] == 0 || $skinSetting['showListOnCategory'] == 2) {
	$listWithPaging = getEntryListWithPagingByCategory($owner, $category, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => (empty($suri['value']) ? _t('전체') : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
}
$entries = array();
if ($skinSetting['showListOnCategory'] == 0 || $skinSetting['showListOnCategory'] == 1)
	list($entries, $paging) = getEntriesWithPagingByCategory($owner, $category, $suri['page'], $blog['entriesOnList']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
