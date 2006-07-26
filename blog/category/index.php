<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($owner, $suri['value']);
if (($suri['page'] === true || $suri['page'] === '1') && $skinSetting['showListOnCategory'])
	$list = array('title' => (empty($suri['value']) ? _text('전체') : $suri['value']), 'items' => getEntryListByCategory($owner, $category));
list($entries, $paging) = getEntriesWithPagingByCategory($owner, $category, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>