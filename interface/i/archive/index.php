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
	?>
	<ul data-role="listview" class="posts" id="archive_<?php echo $suri['page'];?>" title="<?php echo getPeriodLabel($period);?>" selected="false">
	<?php
		$itemsView = '<li class="group ui-bar ui-bar-e">'.CRLF;
		$itemsView .= '	<span class="left">' . getPeriodLabel($period) . ' ('.$list['count'].')</span>'.CRLF;
		$itemsView .= '	<span class="right">Page <span class="now_page">' . $paging['page'] . '</span> / '.$paging['pages'].'</span>'.CRLF;
		$itemsView .= '</li>'.CRLF;
		foreach ($list['items'] as $item) {	
			$author = User::getName($item['userid']);
			if($imageName = printIphoneAttachmentExtract(printIphoneEntryContent($blogid, $item['userid'], $item['id']))){
				$imageSrc = printIphoneImageResizer($blogid, $imageName, 28);
			}else{
				$imageSrc = $context->getProperty('service.path') . '/resources/style/iphone/image/noPostThumb.png';
			}
			$itemsView .= '<li class="post_item">'.CRLF;
			$itemsView .= '	<span class="image"><img src="' . $imageSrc . '" width="28" height="28" /></span>'.CRLF;
			$itemsView .= '	<a href="' . $context->getProperty('uri.blog') . '/entry/' . $item['id'] . '" class="link">'.CRLF;
			$itemsView .= '		<div class="post">'.CRLF;
			$itemsView .= '			<span class="title">' . fireEvent('ViewListTitle', htmlspecialchars($item['title'])) . '</span>'.CRLF;
			$itemsView .= '			<span class="description">' . Timestamp::format5($item['published']) . ', ' ._text('댓글'). '(' . ($item['comments'] > 0 ? $item['comments'] : 0) . ')' . '</span>'.CRLF;
			$itemsView .= '		</div>'.CRLF;
			$itemsView .= '	</a>'.CRLF;
			$itemsView .= '</li>'.CRLF;
		}
		$itemsView .= '</ul>'.CRLF;
		print $itemsView;
		print printMobileListNavigation($paging,'archive/',$period);
printMobileHTMLFooter();
}
?>
