<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);

requireModel("blog.feed");
requireModel("blog.entry");

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
$cache = new pageCache;
if(!empty($suri['id'])) {
	$cache->name = 'responseATOM_'.$suri['id'];
	if(!$cache->load()) {
		$result = getResponseFeedByEntryId(getBlogId(),$suri['id'],'atom');
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
} else {
	$cache->name = 'responseATOM';
	if(!$cache->load()) {
		$result = getResponseFeedTotal(getBlogId(),'atom');
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
}
header('Content-Type: text/xml; charset=utf-8');
echo fireEvent('ViewResponseATOM', $cache->contents);
?>
