<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$skin = new KeywordSkin($skinSetting['keylogSkin']);
$out = str_replace("[##_t3_##]", '<script type="text/javascript">var servicePath = "' . $service['path'] . '"; var blogURL = "' . $blogURL . '"; var adminSkin = "' . $adminSkinSetting['skin'] . '";</script><script type="text/javascript" src="' . $service['path'] . '/script/common2.js"></script><script type="text/javascript" src="' . $service['path'] . '/script/gallery.js"></script>' . $skin->skin, $skin->outter);
$keylogView = $skin->keylog;
$itemsView = '';
foreach ($keylog as $item) {
	$itemView = $skin->keylogItem;
	dress('blog_rep_link', "$blogURL/{$item['id']}", $itemView);
	dress('blog_rep_title', htmlspecialchars($item['title']), $itemView);
	dress('blog_rep_regdate', Timestamp::format3($item['published']), $itemView);
	if ($item['comments'] > 0)
		dress('blog_rep_rp_cnt', "({$item['comments']})", $itemView);
	$itemsView .= $itemView;
}
dress('blog_rep', $itemsView, $keylogView);
dress('blog_desc', getEntryContentView($owner, $keyword['id'], $keyword['content'], 'Keyword'), $keylogView);
dress('blog_conform', htmlspecialchars($keyword['title']), $keylogView);
dress('blog', $keylogView, $out);
dress('blog_word', htmlspecialchars($keyword['title']), $out);
$out = removeAllTags($out);
fireEvent('OBStart');
print $out;
fireEvent('OBEnd');
?>
