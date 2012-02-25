<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
			if($imageName = printMobileAttachmentExtract(printMobileEntryContent($blogid, $item['userid'], $item['id']))){
				$imageSrc = printMobileImageResizer($blogid, $imageName, 64);
			}else{
				$imageSrc = $service['path'] . '/resources/style/iphone/image/noPostThumb.png';
			}
			$itemsView .= '<li class="post_item">'.CRLF;
			$itemsView .= '	<a href="' . $context->getProperty('uri.blog') . '/entry/' . $item['id'] . '" class="link">'.CRLF;
		
			$itemsView .= '	<img src="' . $imageSrc . '"  />'.CRLF;
			$itemsView .= '	<h3>'.fireEvent('ViewListTitle', htmlspecialchars($item['title'])) . '</h3>'.CRLF;
			$itemsView .= '	<p>' . Timestamp::format5($item['published']) . '</span><span class="ui-li-count"> ' . _textf('댓글 %1개',($item['comments'] > 0 ? $item['comments'] : 0))  . '</p>'.CRLF;
			$itemsView .= '	</a>'.CRLF;
			$itemsView .= '</li>'.CRLF;
		}
		$itemsView .= '</ul>'.CRLF;
		print $itemsView;
		print printMobileListNavigation($paging,'archive/',$period);
printMobileHTMLFooter();
}

?>
