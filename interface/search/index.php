<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$search = isset($_GET['search']) ? $_GET['search'] : $suri['value'];
$search = isset($_GET['q']) ? $_GET['q'] : $search; // Consider the common search query GET name. (for compatibility)
$list = array('title' => '', 'items' => array(), 'count' => 0);

if (strlen($search) > 0 && !empty($suri['page'])) {
	$listWithPaging = getEntryListWithPagingBySearch($blogid, $search, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => $search, 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/list.php';
}

if ($suri['page'] === true || $suri['page'] === '1') {
	$commentList = getCommentList($blogid, $search);
	$trackbackList = getTrackbackList($blogid, $search);
	if(!isset($view)) require ROOT . '/lib/piece/blog/begin.php';
	if(isset($commentList))   require ROOT . '/lib/piece/blog/commentList.php';
	if(isset($trackbackList)) require ROOT . '/lib/piece/blog/trackbackList.php';
}

if ($skinSetting['showListOnSearch'] != 2) {
	if(!isset($view)) require ROOT . '/lib/piece/blog/begin.php';
	list($entries, $paging) = getEntriesWithPagingBySearch($blogid, $search, $suri['page'], $blog['entriesOnList'], $blog['entriesOnList']);
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
