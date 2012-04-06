<?php
/// Copyright (c) 2004-2012, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
$context = Model_Context::getInstance();
printMobileHTMLHeader();
printMobileHTMLMenu();
if(!empty($suri['id'])) $period = $suri['id'];
else $period = Timestamp::getYearMonth();

if(isset($period)) {
	$blog['entriesOnList'] = 8;
	$listWithPaging = getEntryListWithPagingByPeriod($blogid, $period, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => getPeriodLabel($period), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	print printMobileEntryListView($list['items'],'archive_'.$suri['page'],getPeriodLabel($period),$paging, $list['count']);
	print printMobileListNavigation($paging,'archive/'.$period);
	printMobileHTMLFooter();
}
?>
