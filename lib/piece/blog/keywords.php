<?php
$keywordView = $skin->keyword;
$previousGroup = '';
$itemsView = '';
foreach ($keywords as $item) {
	$itemView = $skin->keywordItem;
	dress('keyword_label', htmlspecialchars($item['title']), $itemView);
	dress('onclick_keyword', "openKeyword('$blogURL/keylog/" . escapeJSInAttribute($item['title']) . "'); return false;", $itemView);
	$itemsView .= $itemView;
}
dress('keyword_date_rep', $itemsView, $keywordView);
dress('keyword', $keywordView, $view);
?>
