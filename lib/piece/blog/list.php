<?php
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
				Timestamp::format3($item['published']),
				"$blogURL/" . ($blog['useSlogan'] ? 'entry/' . encodeURL($item['slogan']) : $item['id']),
				htmlspecialchars(fireEvent('ViewListRepTitle', $item['title'])),
				($item['comments'] > 0) ? "({$item['comments']})" : ''
			),
			$skin->listItem
		);
	}
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', htmlspecialchars($list['title']), $listView);
	dress('list_count', count($list['items']), $listView);
	dress('list', $listView, $view);
}
?>
