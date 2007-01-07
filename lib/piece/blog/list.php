<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if (isset($list)) {
	$listView = $skin->list;
	$itemsView = '';
	foreach ($list['items'] as $item) {	
		$itemsView .= str_replace(
			array(
				'[##_list_rep_regdate_##]',
				'[##_list_rep_link_##]',
				'[##_list_rep_title_##]',
				'[##_list_rep_rp_cnt_##]'
			),
			array(
				fireEvent('ViewListDate', Timestamp::format3($item['published'])),
				"$blogURL/" . ($blog['useSlogan'] ? 'entry/' . encodeURL($item['slogan']) : $item['id']),
				htmlspecialchars(fireEvent('ViewListTitle', $item['title'])),
				($item['comments'] > 0) ? "({$item['comments']})" : ''
			),
			$skin->listItem
		);
	}
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', fireEvent('ViewListHeadTitle', htmlspecialchars($list['title']) ), $listView);
	dress('list_count', isset($list['count']) ? $list['count'] : '0', $listView);
	dress('list', fireEvent('ViewList', $listView, $list), $view);
}
?>
