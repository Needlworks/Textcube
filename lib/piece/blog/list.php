<?
if (isset($list)) {
	$listView = $skin->list;
	$itemsView = '';
	foreach ($list['items'] as $item) {		
		$lo_src = array(
			"[##_list_rep_regdate_##]",
			"[##_list_rep_link_##]",
			"[##_list_rep_title_##]",
			"[##_list_rep_rp_cnt_##]"
		);				
		$lo_tar = array(
			Timestamp::format3($item['published']),
			"$blogURL/" . ($blog['useSlogan'] ? "entry/".urlencoder($item['slogan']) : $item['id']),
			htmlspecialchars($item['title']),
			($item['comments'] > 0) ? "(".$item['comments'].")" : ""
		);		
		$itemView = str_replace($lo_src,$lo_tar,$skin->listItem);		
		unset($lo_src);
		unset($lo_tar);		
		$itemsView .= $itemView;
	}		
	dress('list_rep', $itemsView, $listView);
	dress('list_conform', htmlspecialchars($list['title']), $listView);
	dress('list_count', count($list['items']), $listView);
	dress('list', $listView, $view);
}
?>
