<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


if(isset($cache->contents)) {
	$tagView = $cache->contents;
} else {
	$tagView = $skin->siteTag;
	list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
	$itemsView = '';
	foreach ($siteTags as $siteTag) {
		$itemView = $skin->siteTagItem;
		dress('tag_name', htmlspecialchars($siteTag['name']), $itemView);
		dress('tag_link', $context->getProperty('uri.blog')."/tag/" . ($context->getProperty('blog.useSloganOnTag',true) ? URL::encode($siteTag['name'],$service['useEncodedURL']) : $siteTag['id']), $itemView);
		dress('tag_class', "cloud" . getTagFrequency($siteTag['name'], $maxTagFreq, $minTagFreq), $itemView);
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
