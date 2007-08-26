<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$period = $suri['id'];
if ($skinSetting['showListOnArchive'] != 0) {
	$listWithPaging = getEntryListWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	if(!isset($view)) require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/list.php';
}
$entries = array();
if ($skinSetting['showListOnArchive'] != 2) {
	list($entries, $paging) = getEntriesWithPagingByPeriod($blogid, $period, $suri['page'], ($skinSetting['showListOnArchive'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList'] ));
	if(!isset($view)) require ROOT . '/lib/piece/blog/begin.php';
	require ROOT . '/lib/piece/blog/entries.php';
}
require ROOT . '/lib/piece/blog/end.php';
?>
