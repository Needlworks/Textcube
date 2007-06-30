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
	$tag = getTagId($owner, $suri['value']);
	
	require ROOT . '/lib/piece/blog/begin.php';
	if(getBlogSetting('useKeywordAsTag',true)==true){
		$entries = getKeylog(getBlogId(), $suri['value']);
		if(isset($entries)) {
			require ROOT . '/lib/piece/blog/entries.php';
			unset($entries);
		}
	}
	
	if ($skinSetting['showListOnTag'] != 0) {
		$listWithPaging = getEntryListWithPagingByTag($owner, $tag, $suri['page'], $blog['entriesOnList']);
		if (!array_key_exists('total',$listWithPaging[1])) $listWithPaging[1]['total'] = 0;
		$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
		$paging = $listWithPaging[1];
	}
	if ($skinSetting['showListOnTag'] != 2)
		list($entries, $paging) = getEntriesWithPagingByTag($owner, $tag, $suri['page'], $blog['entriesOnList'],($skinSetting['showListOnTag'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));

	require ROOT . '/lib/piece/blog/list.php';
	require ROOT . '/lib/piece/blog/entries.php';
} else {
	$siteTags = getSiteTags($owner);
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/siteTags.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
