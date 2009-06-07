<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

$cache = new pageCache;
if(!isset($suri['id']) || (getBlogSetting('useSloganOnCategory',1) == 1)) {
	$category = empty($suri['value']) ? 0 : getCategoryIdByLabel($blogid, $suri['value']);
} else {
	$category = $suri['id'];
	$suri['value'] = getCategoryLabelById($blogid, $category);
}
if(!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2)
	$category = null;

if ($skinSetting['showlistoncategory'] != 0) {
	$cache->name = 'categoryList_'.$category."_".$suri['page']."_";
	if (!$cache->load()) {
		if(!$listWithPaging = getEntryListWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList']))
			$listWithPaging = array(array(), array('total' => 0));
		$list = array('title' => (empty($suri['value']) ? getCategoryLabelById($blogid, 0) : $suri['value']), 
				'items' => $listWithPaging[0], 
				'count' => $listWithPaging[1]['total'],
				'category' => $category);
		$paging = $listWithPaging[1];
	} else {
		$paging = $cache->dbContents;
	}
	require ROOT . '/library/piece/blog/begin.php';
	require ROOT . '/library/piece/blog/list.php';
}

$entries = array();
if ($skinSetting['showlistoncategory'] != 2) {
	unset($cache);
	list($entries, $paging) = getEntriesWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList'], ($skinSetting['showlistoncategory'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
	if($skinSetting['showlistoncategory'] == 1) $skinSetting['showListWithTotalEntries'] = true;
	if($skinSetting['showlistoncategory'] == 0) require ROOT . '/library/piece/blog/begin.php';
	require ROOT . '/library/piece/blog/entries.php';
}
require ROOT . '/library/piece/blog/end.php';

?>
