<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
if(!empty($suri['id'])) $period = $suri['id'];
else $period = Timestamp::getYearMonth();

fireEvent('OBStart');
require ROOT . '/library/piece/blog/begin.php';
if ($skinSetting['showlistonarchive'] != 0) {
	$listWithPaging = getEntryListWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	if($skinSetting['showlistonarchive'] == 1) $skinSetting['showListWithTotalEntries'] = true;
	require ROOT . '/library/piece/blog/list.php';
}
$entries = array();
if ($skinSetting['showlistonarchive'] != 2) {
	list($entries, $paging) = getEntriesWithPagingByPeriod($blogid, $period, $suri['page'], ($skinSetting['showlistonarchive'] == 3 ? $blog['entriesOnPage'] : $blog['entriesOnList'] ));
	require ROOT . '/library/piece/blog/entries.php';
}
require ROOT . '/library/piece/blog/end.php';
fireEvent('OBEnd');
?>
