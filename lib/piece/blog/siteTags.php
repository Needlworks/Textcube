<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


if(isset($cache->contents)) {
	$tagView = $cache->contents;
} else {
	$tagView = $skin->siteTag;
	list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
	$itemsView = '';
	foreach ($siteTags as $siteTag) {
		$itemView = $skin->siteTagItem;
		dress('tag_name', htmlspecialchars($siteTag), $itemView);
		dress('tag_link', "$blogURL/tag/" . encodeURL($siteTag), $itemView);
		dress('tag_class', "cloud" . getTagFrequency($siteTag, $maxTagFreq, $minTagFreq), $itemView);
		$itemsView .= $itemView;
	}
	dress('tag_rep', $itemsView, $tagView);
	if(isset($cache)) {
		$cache->contents = $tagView;
		$cache->update();
	}
}
dress('tag', $tagView, $view);
?>
