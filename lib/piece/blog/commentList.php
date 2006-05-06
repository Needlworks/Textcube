<?
$commentListView = $skin->commentList;
$itemsView = '';
foreach ($commentList['items'] as $item) {
	$itemView = $skin->commentListItem;
	dress('rplist_rep_regdate', Timestamp::format3($item['written']), $itemView);
	dress('rplist_rep_link', "$blogURL/{$item['entry']}#comment{$item['id']}", $itemView);
	dress('rplist_rep_name', htmlspecialchars($item['name']), $itemView);
	dress('rplist_rep_body', htmlspecialchars(UTF8::lessenAsEm($item['comment'], 70)), $itemView);
	$itemsView .= $itemView;
}
dress('rplist_rep', $itemsView, $commentListView);
dress('rplist_conform', htmlspecialchars($commentList['title']), $commentListView);
dress('rplist_count', count($commentList['items']), $commentListView);
dress('rplist', $commentListView, $view);
?>