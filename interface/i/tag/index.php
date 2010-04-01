<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
if (false) {
	fetchConfigVal();
}

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
	?>
	<ul class="posts" id="tag_<?php echo $suri['page'];?>" title="<?php echo getTagById($blogid, $suri['id']);?>" selected="false">
	<?php
		$itemsView = '<li class="group">'.CRLF;
		$itemsView .= '	<span class="left">' . getTagById($blogid, $suri['id']) . ' ('.$list['count'].')</span>'.CRLF;
		$itemsView .= '	<span class="right">Page <span class="now_page">' . $paging['page'] . '</span> / '.$paging['pages'].'</span>'.CRLF;
		$itemsView .= '</li>'.CRLF;
		foreach ($list['items'] as $item) {	
			$author = User::getName($item['userid']);
			if($imageName = printIphoneAttachmentExtract(printIphoneEntryContent($blogid, $item['userid'], $item['id']))){
				$imageSrc = printIphoneImageResizer($blogid, $imageName, 28);
			}else{
				$imageSrc = $service['path'] . '/resources/style/iphone/image/noPostThumb.png';
			}
			$itemsView .= '<li class="post_item">'.CRLF;
			$itemsView .= '	<span class="image"><img src="' . $imageSrc . '" width="28" height="28" /></span>'.CRLF;
			$itemsView .= '	<a href="' . $blogURL . '/entry/' . $item['id'] . '" class="link">'.CRLF;
			$itemsView .= '		<div class="post">'.CRLF;
			$itemsView .= '			<span class="title">' . fireEvent('ViewListTitle', htmlspecialchars($item['title'])) . '</span>'.CRLF;
			$itemsView .= '			<span class="description">' . Timestamp::format5($item['published']) . ', ' . 'Comments(' . ($item['comments'] > 0 ? $item['comments'] : 0) . ')' . '</span>'.CRLF;
			$itemsView .= '		</div>'.CRLF;
			$itemsView .= '	</a>'.CRLF;
			$itemsView .= '</li>'.CRLF;
		}

		$itemsView .= '<li class="pagination">'.CRLF;
		if(isset($paging['prev'])){
			$itemsView .= '<a href="' .$blogURL . '/tag/' . $suri['id'] . '?page=' . $paging['prev'] . '" class="previous">Page ' . $paging['prev'] . '</a>'.CRLF;
		}
		if (isset($paging['next'])) {
			$itemsView .= '<a href="' .$blogURL . '/tag/' . $suri['id'] . '?page=' . $paging['next'] . '" class="next">Page ' . $paging['next'] . '</a>'.CRLF;
		}
		if ($suri['page'] > 1 && $suri['page'] != $paging['pages']) {
			$itemsView .= '<strong>' . $suri['page'] . '</strong>'.CRLF;
		}
		$itemsView .= '</li>'.CRLF;
		print $itemsView;
	?>
	</ul>
<?php
}
//printIphoneNavigation($entry, false, true);
?>
