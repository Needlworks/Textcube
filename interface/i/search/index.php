<?php
/// Copyright (c) 2004-2013, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
printMobileHTMLHeader();

$search = isset($_GET['search']) ? $_GET['search'] : $suri['value'];
$search = isset($_GET['q']) ? $_GET['q'] : $search; // Consider the common search query GET name. (for compatibility)
if(strlen($search) > 0 && !empty($suri['page'])) {
	printMobileHTMLMenu('','list');
	$blog['entriesOnList'] = 8;
	$listWithPaging = getEntryListWithPagingBySearch($blogid, $search, $suri['page'], $blog['entriesOnList']);
	$list = array('title' => $search, 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	?>
	<ul data-role="listview" class="search" id="search_<?php echo $suri['page'];?>" title="<?php echo _text('검색 결과');?>" selected="false" data-inset="true">
	<?php
		$itemsView = '<li class="group ui-bar ui-bar-e">'.CRLF;
		$itemsView .= '	<span class="left">'.htmlspecialchars($search).' '._text('검색 결과').'('.$list['count'].')</span>'.CRLF;
		$itemsView .= '	<span class="right ui-li-aside">'._text('페이지').'<span class="now_page">' . $paging['page'] . '</span> / '.$paging['pages'].'</span>'.CRLF;
		$itemsView .= '</li>'.CRLF;
		foreach ($list['items'] as $item) {	
			$author = User::getName($item['userid']);
			if($imageName = printMobileAttachmentExtract(printMobileEntryContent($blogid, $item['userid'], $item['id']))){
				$imageSrc = printMobileImageResizer($blogid, $imageName, 80);
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
		print printMobileListNavigation($paging,'search/'.$search);
	?>
	</ul>
<?php
	printMobileHTMLFooter();
} else {
?>
	<div data-role="page" data-theme="a" id="search">
		<form id="searchForm" method="GET" class="dialog snug editorBar" action="<?php echo $blogURL;?>/search">
			<fieldset class="ui-hide-label">
				<label for="qString"><?php echo _text('글 검색');?></label>
				<input id="qString" type="search" data-theme="c" name="search" autocomplete="off" unedited="true" class="search" placeholder="<?php echo _text('검색어를 입력하세요');?>" />
				<div data-role="controlgroup">
					<button data-role="button" class="button blueButton" data-theme="b" data-transition="slideup" type="submit"><?php echo _text('검색');?></button>
					<a data-role="button" class="button blueButton" data-role="button" data-transition="slideup" data-rel="back"><?php echo _text('취소');?></a>
				</div>
			</fieldset>
		</form>
	</div>
<?php
}
?>