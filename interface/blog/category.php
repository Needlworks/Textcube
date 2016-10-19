<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

$cache = pageCache::getInstance();

if(!isset($suri['id']) || (Setting::getBlogSettingGlobal('useSloganOnCategory',1) == 1)) {
	$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($blogid, $suri['value']);
	$listFeedURL = 'category/'.$suri['value'];
} else {
	$category = $suri['id'];
	$suri['value'] = getCategoryLabelById($blogid, $category);
	$listFeedURL = 'category/'.$suri['id'];
}
if(!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2)
	$category = null;

// Keylog as category description
$keylogEntry = null;
if(Setting::getBlogSettingGlobal('useKeywordAsCategory',true)==true) {
	$cache->reset();
	$cache->name = 'keyword-category-'.$category.'-';
	if($cache->load()) {
		$keylogEntry = unserialize($cache->contents);
	} else {
		$entries = array();
		$entries[0] = getKeylogByTitle(getBlogId(), $suri['value']);
		if(!empty($entries[0])) {
			$keylogEntry = $entries;
			$cache->contents = serialize($entries);
			$cache->update();
		}
		unset($entries);
	}
	$cache->reset();
}

//require ROOT . '/interface/common/blog/begin.php';

if ($skinSetting['showListOnCategory'] != 0) {
	require ROOT . '/interface/common/blog/begin.php';
	if(!empty($keylogEntry)) {
		$entries = $keylogEntry;
		require ROOT . '/interface/common/blog/entries.php';
		unset($keylogEntry);
		unset($entries);
	}
	$cache->reset();
	$cache->name = 'categoryList-'.$category."-".$suri['page']."-";
	if (!$cache->load()) {
		if(!$listWithPaging = getEntryListWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList']))
			$listWithPaging = array(array(), array('total' => 0));
		$list = array('title' => (empty($suri['value']) ? getCategoryLabelById($blogid, 0) : $suri['value']),
				'items' => $listWithPaging[0],
				'count' => $listWithPaging[1]['total'],
				'category' => $category);
		$paging = $listWithPaging[1];
		$listView = null;
	} else {
		$paging = $cache->dbContents;
		$listView = $cache->contents;
	}
	require ROOT . '/interface/common/blog/list.php';
}

$entries = array();
if ($skinSetting['showListOnCategory'] != 2) {
	unset($cache);
	if($skinSetting['showListOnCategory'] == 1) $skinSetting['showListWithTotalEntries'] = true;
	if($skinSetting['showListOnCategory'] == 0) require ROOT . '/interface/common/blog/begin.php';
	if(!empty($keylogEntry)) {
		$entries = $keylogEntry;
		require ROOT . '/interface/common/blog/entries.php';
		unset($keylogEntry);
		unset($entries);
	}
	list($entries, $paging) = getEntriesWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList'], ($skinSetting['showListOnCategory'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
	require ROOT . '/interface/common/blog/entries.php';
}
require ROOT . '/interface/common/blog/end.php';

?>
