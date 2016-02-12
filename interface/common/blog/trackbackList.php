<?php 
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$trackbackListView = $skin->trackbackList;
$itemsView = '';
foreach ($trackbackList['items'] as $item) {
	$itemView = $skin->trackbackListItem;
	dress('tblist_rep_regdate', fireEvent('ViewTrackbackListDate', Timestamp::format3($item['written'])), $itemView);
	dress('tblist_rep_link', $context->getProperty('uri.blog')."/".($context->getProperty('blog.useSloganOnPost'( ? "entry/".URL::encode($item['slogan'],$context->getProperty('service.useEncodedURL')) : $item['entry'])."#trackback{$item['id']}", $itemView);
	dress('tblist_rep_subject', htmlspecialchars($item['subject']), $itemView);
	dress('tblist_rep_body', htmlspecialchars(fireEvent('ViewTrackbackListTitle', Utils_Unicode::lessenAsEm($item['excerpt'], 100))), $itemView);
	$itemsView .= $itemView;
}
dress('tblist_rep', $itemsView, $trackbackListView);
dress('tblist_conform', htmlspecialchars(fireEvent('ViewTrackbackListHeadTitle', $trackbackList['title'])), $trackbackListView);
dress('tblist_count', count($trackbackList['items']), $trackbackListView);
dress('tblist', $trackbackListView, $view);
?>
