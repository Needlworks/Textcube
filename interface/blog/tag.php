<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$cache = new pageCache;
if (strlen($suri['value'])) {
	if(!isset($suri['id']) || (getBlogSetting('useSloganOnTag',1) == 1)) {
		$tag = getTagId($blogid, $suri['value']);
	} else {
		$tag = $suri['id'];
		$suri['value'] = getTagById($blogid, $suri['id']);
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
	require ROOT . '/library/piece/blog/begin.php';
	$cache->reset();
	if(getBlogSetting('useKeywordAsTag',true)==true) {
		$cache->name = 'keyword-'.$tag.'-';
		if($cache->load()) {
			require ROOT . '/library/piece/blog/entries.php';
		} else {
			$entries[0] = getKeylogByTitle(getBlogId(), $suri['value']);
			if(!empty($entries[0])) {
				require ROOT . '/library/piece/blog/entries.php';
				unset($entries);
			}
		}
	}
	
	if ($skinSetting['showListOnTag'] != 0) {
		$cache = $listCache;
		require ROOT . '/library/piece/blog/list.php';
	}
	unset($cache);
	if ($skinSetting['showListOnTag'] != 2) {
		$entries = $preservedEntries;
		require ROOT . '/library/piece/blog/entries.php';
	}
	
} else {
	require ROOT . '/library/piece/blog/begin.php';
	$cache->reset();
	$cache->name = 'tagPage';
	if(!$cache->load()) {
		$siteTags = getSiteTags($blogid);
	}
	require ROOT . '/library/piece/blog/siteTags.php';
	unset($cache);
}
require ROOT . '/library/piece/blog/end.php';
?>
