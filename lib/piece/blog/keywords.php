<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$keywordView = $skin->keyword;
$previousGroup = '';
$itemsView = '';
foreach ($keywords as $item) {
	$itemView = $skin->keywordItem;
	dress('keyword_label', htmlspecialchars($item), $itemView);
	dress('onclick_keyword', "openKeyword('$blogURL/keylog/" . escapeJSInAttribute($item) . "'); return false;", $itemView);
	$itemsView .= $itemView;
}
dress('keyword_rep', $itemsView, $keywordView);
dress('keyword', $keywordView, $view);
?>
