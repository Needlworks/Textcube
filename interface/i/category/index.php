<?php
/// Copyright (c) 2004-2013, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu('','list');
$category = $suri['id'];
if(isset($category)) {
	$blog['entriesOnList'] = 8;
	if(!$listWithPaging = getEntriesWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList'],1))
		$listWithPaging = array(array(), array('total' => 0));
	$list = array('title' => (empty($suri['value']) ? getCategoryLabelById($blogid, 0) : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	print printMobileEntryListView($list['items'],'category_'.$suri['page'],($category == 0 ? _text('모든 카테고리') : ucwords(getCategoryNameById($blogid, $category))),$paging, $list['count']);
	print printMobileListNavigation($paging,'category/' . $category);
}
?>
