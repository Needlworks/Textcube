<?
if (isset($list)) {
	$listView = $skin->list;
	$itemsView = '';
	foreach ($list['items'] as $item) {
		$itemView = $skin->listItem;
		dress('list_rep_regdate', Timestamp::format3($item['published']), $itemView);
		dress('list_rep_link', "$blogURL/" . ($blog['useSlogan'] ? "entry/{$item['slogan']}" : $item['id']), $itemView);
		dress('list_rep_title', htmlspecialchars($item['title']), $itemView);
		if ($item['comments'] > 0)
			dress('list_rep_rp_cnt', "({$item['comments']})", $itemView);
		$itemsView .= $itemView;
	}
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', htmlspecialchars($list['title']), $listView);
	dress('list_count', count($list['items']), $listView);
	dress('list', $listView, $view);
}
?>