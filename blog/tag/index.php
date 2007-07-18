<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
if (strlen($suri['value'])) {
	$tag = getTagId($blogid, $suri['value']);

	$cache = new pageCache;

	require ROOT . '/lib/piece/blog/begin.php';
	if(getBlogSetting('useKeywordAsTag',true)==true){
		$cache->name = 'keyword_'.$tag;
		if($cache->load()) {
			require ROOT . '/lib/piece/blog/entries.php';
		} else {
			$entries = getKeyword(getBlogId(), $suri['value']);
			if(isset($entries)) {
				require ROOT . '/lib/piece/blog/entries.php';
				unset($entries);
			}
		}
	}
	
	if ($skinSetting['showListOnTag'] != 0) {
		$cache->reset();
		$cache->name = 'tagList_'.$tag.'_'.$suri['page'];
		if(!$cache->load()){
			$listWithPaging = getEntryListWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList']);
			if (!array_key_exists('total',$listWithPaging[1])) $listWithPaging[1]['total'] = 0;
			$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
			$paging = $listWithPaging[1];
		}
		require ROOT . '/lib/piece/blog/list.php';
	}
	if ($skinSetting['showListOnTag'] != 2) {
		$cache->reset();
		$cache->name = 'tagList_'.$tag.'_'.$suri['page'];
		if(!$cache->load()){
			list($entries, $paging) = getEntriesWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList'],($skinSetting['showListOnTag'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
		}
		require ROOT . '/lib/piece/blog/entries.php';
	}
} else {
	require ROOT . '/lib/piece/blog/begin.php';
	$siteTags = getSiteTags($blogid);
	require ROOT . '/lib/piece/blog/siteTags.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
