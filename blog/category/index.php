<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

$cache = new pageCache;
$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($blogid, $suri['value']);

if(!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2)
	$category = null;

if ($skinSetting['showListOnCategory'] != 0) {
	$cache->name = 'categoryList_'.$category."_".$suri['page'];
	if (!$cache->load()) {
		if(!$listWithPaging = getEntryListWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList']))
			$listWithPaging = array(array(), array('total' => 0));
		$list = array('title' => (empty($suri['value']) ? _t('전체') : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
		$paging = $listWithPaging[1];
	} else {
		$paging = $cache->dbContents;
	}
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/list.php';
}

$entries = array();
if ($skinSetting['showListOnCategory'] != 2) {
	$cache->reset();
	$cache->name = 'categoryEntries_'.$category."_".$suri['page'];
	if (!$cache->load()) {
		list($entries, $paging) = getEntriesWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList'], ($skinSetting['showListOnCategory'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
	} else {
		$paging = $cache->dbContents;
	}
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';

?>
