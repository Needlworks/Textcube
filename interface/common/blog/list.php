<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


if (isset($cache->contents)) {
	dress('list', $cache->contents, $view);
} else if (isset($list) && isset($skin)) {
	$listView = $skin->list;
	$itemsView = '';
	foreach ($list['items'] as $item) {	
		$author = User::getName($item['userid']);
		$itemsView .= str_replace(
			array(
				'[##_list_rep_regdate_##]',
				'[##_list_rep_name_##]',
				'[##_list_rep_author_##]',
				'[##_list_rep_link_##]',
				'[##_list_rep_title_##]',
				'[##_list_rep_rp_cnt_##]'
			),
			array(
				fireEvent('ViewListDate', Timestamp::formatDate($item['published']), $item['published']),
				fireEvent('ViewListName', htmlspecialchars($author)),
				fireEvent('ViewListName', htmlspecialchars($author)),
				((!empty($skinSetting['showListWithTotalEntries'])) ? "#entry_".$item['id'] :
				"$blogURL/" . ($blog['useSloganOnPost'] ? 'entry/' . URL::encode($item['slogan'],$service['useEncodedURL']) : $item['id'])).(isset($list['category']) ? '?category='.$list['category'] : ''),
				fireEvent('ViewListTitle', htmlspecialchars($item['title'])),
				($item['comments'] > 0) ? "({$item['comments']})" : ''
			),
			$skin->listItem
		);
	}
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', fireEvent('ViewListHeadTitle', htmlspecialchars($list['title']) ), $listView);
	dress('list_count', isset($list['count']) ? $list['count'] : '0', $listView);
	dress('list_rss_url', $context->getProperty('uri.default').'/rss/'.$listFeedURL, $listView);
	dress('list_atom_url', $context->getProperty('uri.default').'/atom/'.$listFeedURL, $listView);
	$listView = fireEvent('ViewList', $listView, $list);
	if(empty($entries)) $listView = $listView.CRLF.'[##_paging_list_##]';
	
	dress('list', $listView, $view);
	if(isset($cache)) { 
		$cache->contents = $listView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
