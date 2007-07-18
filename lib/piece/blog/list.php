<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


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
				fireEvent('ViewListDate', Timestamp::format3($item['published'])),
				fireEvent('ViewListName', htmlspecialchars($author)),
				fireEvent('ViewListName', htmlspecialchars($author)),
				"$blogURL/" . ($blog['useSlogan'] ? 'entry/' . encodeURL($item['slogan']) : $item['id']),
				fireEvent('ViewListTitle', $item['title']),
				($item['comments'] > 0) ? "({$item['comments']})" : ''
			),
			$skin->listItem
		);
	}
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', fireEvent('ViewListHeadTitle', htmlspecialchars($list['title']) ), $listView);
	dress('list_count', isset($list['count']) ? $list['count'] : '0', $listView);
	dress('list', fireEvent('ViewList', $listView, $list), $view);
	if(isset($cache)) { 
		$cache->contents = $listView;
		$cache->update();
	}
}
?>
