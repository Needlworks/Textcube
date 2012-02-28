<?php
/// Copyright (c) 2004-2012, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
$context = Model_Context::getInstance();
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu();

if(strlen($suri['value'])) {
	if(!isset($suri['id'])) {
		$tag = getTagId($blogid, $suri['value']);
	} else {
		$tag = $suri['id'];
		$suri['value'] = getTagById($blogid, $suri['id']);
	}

	$blog['entriesOnList'] = 8;
	$listWithPaging = getEntryListWithPagingByTag($blogid, $tag, $suri['page'], $blog['entriesOnList']);
	if (!array_key_exists('total',$listWithPaging[1])) $listWithPaging[1]['total'] = 0;
	$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	print printMobileEntryListView($list['items'],'tag_'.$suri['page'],getTagById($blogid, $suri['id']),$paging, $list['count']);
	print printMobileListNavigation($paging,'tag/' . $suri['id']);
}
printMobileHTMLFooter();
?>
