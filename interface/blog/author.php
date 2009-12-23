<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (false) {
	fetchConfigVal();
}

$cache = pageCache::getInstance();
$author = empty($suri['value']) ? '' : $suri['value'];
$authorId = User::getUserIdByName($author);
if(empty($authorId)) exit;

if ($skinSetting['showListOnAuthor'] != 0) {
	$cache->name = 'authorList-'.$authorId."-".$suri['page']."-";
	if (!$cache->load()) {
		if(!$listWithPaging = getEntryListWithPagingByAuthor($blogid, $author, $suri['page'], $blog['entriesOnList']))
			$listWithPaging = array(array(), array('total' => 0));
		$list = array('title' => $author, 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
		$paging = $listWithPaging[1];
	} else {
		$paging = $cache->dbContents;
	}
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/list.php';
}

$entries = array();
if ($skinSetting['showListOnAuthor'] != 2) {
	unset($cache);
	list($entries, $paging) = getEntriesWithPagingByAuthor($blogid, $author, $suri['page'], $blog['entriesOnList'], ($skinSetting['showListOnAuthor'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList']));
	if($skinSetting['showListOnAuthor'] == 0) require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/entries.php';
}
require ROOT . '/interface/common/blog/end.php';

?>
