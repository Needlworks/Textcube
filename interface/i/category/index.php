<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
	if(!$listWithPaging = getEntryListWithPagingByCategory($blogid, $category, $suri['page'], $blog['entriesOnList']))
		$listWithPaging = array(array(), array('total' => 0));
	$list = array('title' => (empty($suri['value']) ? getCategoryLabelById($blogid, 0) : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
?>
	<ul data-role="listview" class="posts" id="category_<?php echo $suri['page'];?>" title="<?php echo ($category == 0 ? _text('모든 카테고리') : ucwords(getCategoryNameById($blogid, $category)));?>" selected="false">
<?php
	$itemsView = '<li class="group ui-bar ui-bar-e">'.CRLF;
	$itemsView .= '	<span class="left">' . ($category == 0 ? _text('모든 카테고리') : ucwords(getCategoryNameById($blogid, $category))) . ' ('.$list['count'].')</span>'.CRLF;
	$itemsView .= '	<span class="right">Page <span class="now_page">' . $paging['page'] . '</span> / '.$paging['pages'].'</span>'.CRLF;
	$itemsView .= '</li>'.CRLF;
	foreach ($list['items'] as $item) {	
		$author = User::getName($item['userid']);
		if($imageName = printMobileAttachmentExtract(printMobileEntryContent($blogid, $item['userid'], $item['id']))){
			$imageSrc = printMobileImageResizer($blogid, $imageName, 55);
		}else{
			$imageSrc = $service['path'] . '/resources/style/iphone/image/noPostThumb.png';
		}
		$itemsView .= '<li class="post_item">'.CRLF;
		$itemsView .= '	<span class="image"><img src="' . $imageSrc . '" width="55px" height="55px" /></span>'.CRLF;
		$itemsView .= '	<a href="' . $blogURL . '/entry/' . $item['id'] . '" class="link">'.CRLF;
		$itemsView .= '		<div class="post">'.CRLF;
		$itemsView .= '			<span class="title">' . fireEvent('ViewListTitle', htmlspecialchars($item['title'])) . '</span>'.CRLF;
		$itemsView .= '			<span class="description">' . Timestamp::format5($item['published']) . '</span><span class="ui-li-count"> ' . _textf('댓글 %1개',($item['comments'] > 0 ? $item['comments'] : 0))  . '</span>'.CRLF;
		$itemsView .= '		</div>'.CRLF;
		$itemsView .= '	</a>'.CRLF;
		$itemsView .= '</li>'.CRLF;
	}
	$itemsView .= '</ul>'.CRLF;
	print $itemsView;
	print printMobileListNavigation($paging,'category/' . $category);
}
?>
