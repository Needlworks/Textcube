<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}

$cache = new pageCache;
$author = empty($suri['value']) ? '' : $suri['value'];
$authorId = User::getUserIdByName($author);
if(empty($authorId)) exit;

if ($skinSetting['showListOnCategory'] != 0) {
	$cache->name = 'authorList_'.$authorId."_".$suri['page']."_";
	if (!$cache->load()) {
		if(!$listWithPaging = getEntryListWithPagingByAuthor($blogid, $author, $suri['page'], $blog['entriesOnList']))
			$listWithPaging = array(array(), array('total' => 0));
		$list = array('title' => $author, 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
		$paging = $listWithPaging[1];
	} else {
		$paging = $cache->dbContents;
	}
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/list.php';
}

$entries = array();
if ($skinSetting['showListOnCategory'] != 2) {
	unset($cache);
	list($entries, $paging) = getEntriesWithPagingByAuthor($blogid, $author, $suri['page'], $blog['entriesOnList'], ($skinSetting['showListOnCategory'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
	if($skinSetting['showListOnCategory'] == 0) require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';

?>
