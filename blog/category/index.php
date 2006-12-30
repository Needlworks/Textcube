<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($owner, $suri['value']);
if(!doesHaveOwnership() && getCategoryVisibility($owner, $category) < 2)
	$category = null;
if ($skinSetting['showListOnCategory'] == 1 || $skinSetting['showListOnCategory'] == 2) {
	$listWithPaging = getEntryListWithPagingByCategory($owner, $category, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => (empty($suri['value']) ? _t('전체') : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
}
$entries = array();
if ($skinSetting['showListOnCategory'] == 1 || $skinSetting['showListOnCategory'] == 0)
	list($entries, $paging) = getEntriesWithPagingByCategory($owner, $category, $suri['page'], $blog['entriesOnList'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/list.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
