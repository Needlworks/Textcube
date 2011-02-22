<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

if(!empty($suri['id'])) $period = $suri['id'];
else $period = Timestamp::getYearMonth();

fireEvent('OBStart');
require ROOT . '/interface/common/blog/begin.php';
if ($skinSetting['showListOnArchive'] != 0) {
	$listWithPaging = getEntryListWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	if($skinSetting['showListOnArchive'] == 1) $skinSetting['showListWithTotalEntries'] = true;
	require ROOT . '/interface/common/blog/list.php';
}
$entries = array();
if ($skinSetting['showListOnArchive'] != 2) {
	list($entries, $paging) = getEntriesWithPagingByPeriod($blogid, $period, $suri['page'], ($skinSetting['showListOnArchive'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList'] ));
	require ROOT . '/interface/common/blog/entries.php';
}
require ROOT . '/interface/common/blog/end.php';
fireEvent('OBEnd');
?>
