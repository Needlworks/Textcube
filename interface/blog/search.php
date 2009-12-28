<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

$search = isset($_GET['search']) ? $_GET['search'] : $suri['value'];
$search = isset($_GET['q']) ? $_GET['q'] : $search; // Consider the common search query GET name. (for compatibility)
$list = array('title' => '', 'items' => array(), 'count' => 0);

if (strlen($search) > 0 && !empty($suri['page'])) {
	$listWithPaging = getEntryListWithPagingBySearch($blogid, $search, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => $search, 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$listFeedURL = 'search/'.$search;
	$paging = $listWithPaging[1];
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/list.php';
}

if ($suri['page'] === true || $suri['page'] === '1') {
	$commentList = getCommentList($blogid, $search);
	$trackbackList = getTrackbackList($blogid, $search);
	if(!isset($view)) require ROOT . '/interface/common/blog/begin.php';
	if(isset($commentList))   require ROOT . '/interface/common/blog/commentList.php';
	if(isset($trackbackList)) require ROOT . '/interface/common/blog/trackbackList.php';
}

if ($skinSetting['showListOnSearch'] != 2) {
	if(!isset($view)) require ROOT . '/interface/common/blog/begin.php';
	list($entries, $paging) = getEntriesWithPagingBySearch($blogid, $search, $suri['page'], $blog['entriesOnList'], $blog['entriesOnList']);
	require ROOT . '/interface/common/blog/entries.php';
}
require ROOT . '/interface/common/blog/end.php';
?>
