<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';

$cache = pageCache::getInstance();
$context = Model_Context::getInstance();
$suri = $context->getAllFromNamespace('suri');
if (strlen($suri['value'])) {
	if(!isset($suri['id']) || (Setting::getBlogSettingGlobal('useSloganOnTag',1) == 1)) {
		$tag = getTagId($blogid, $suri['value']);
		$listFeedURL = 'tag/'.$suri['value'];
	} else {
		$tag = $suri['id'];
		$suri['value'] = getTagById($blogid, $suri['id']);
		$listFeedURL = 'tag/'.$suri['id'];
	}

	$preservedEntries =
	$entryCache =
	$listCache =
		null;
	if ($skinSetting['showListOnTag'] != 0) {
		$cache->reset();
		$cache->name = 'tagList-'.$tag.'-'.$suri['page'].'-';
		if(!$cache->load()) {
			$listWithPaging = getEntryListWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList']);
			if (!array_key_exists('total',$listWithPaging[1])) $listWithPaging[1]['total'] = 0;
			$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
			$paging = $listWithPaging[1];
		} else {
			$paging = $cache->dbContents;
			$listCache = $cache;	//preserve for ordering
		}
	}

	if ($skinSetting['showListOnTag'] != 2) {
		list($entries, $paging) = getEntriesWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList'],($skinSetting['showListOnTag'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
		if($skinSetting['showListOnTag'] == 1) $skinSetting['showListWithTotalEntries'] = true;
		$preservedEntries = $entries;
		unset($entries);
	}

	// Now, print starts.
	require ROOT . '/interface/common/blog/begin.php';
	if(Setting::getBlogSettingGlobal('useKeywordAsTag',true)==true) {
		$cache->reset();
		$cache->name = 'keyword-'.$tag.'-';
		if($cache->load()) {
			require ROOT . '/interface/common/blog/entries.php';
		} else {
			$entries[0] = getKeylogByTitle(getBlogId(), $suri['value']);
			if(!empty($entries[0])) {
				require ROOT . '/interface/common/blog/entries.php';
				unset($entries);
			}
		}
	}

	if ($skinSetting['showListOnTag'] != 0) {
		$cache = $listCache;
		require ROOT . '/interface/common/blog/list.php';
	}
	unset($cache);
	if ($skinSetting['showListOnTag'] != 2) {
		$entries = $preservedEntries;
		require ROOT . '/interface/common/blog/entries.php';
	}

} else {
	require ROOT . '/interface/common/blog/begin.php';
	$cache->reset();
	$cache->name = 'tagPage';
	if(!$cache->load()) {
		$siteTags = getSiteTags($blogid);
	}
	require ROOT . '/interface/common/blog/siteTags.php';
	unset($cache);
}
require ROOT . '/interface/common/blog/end.php';
?>
