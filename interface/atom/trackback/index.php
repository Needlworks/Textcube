<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.feed");
importlib("model.blog.entry");

requireStrictBlogURL();
$cache = pageCache::getInstance();
if(!empty($suri['id'])) {
	$cache->reset('trackbackATOM-'.$suri['id']);
	if(!$cache->load()) {
		$result = getTrackbackFeedByEntryId(getBlogId(),$suri['id'],false,'atom');
		if($result !== false) {
			$cache->reset('trackbackATOM-'.$suri['id']);
			$cache->contents = $result;
			$cache->update();
		}
	}
} else {
	$cache->reset('trackbackATOM');
	if(!$cache->load()) {
		$result = getTrackbackFeedTotal(getBlogId(),false,'atom');
		if($result !== false) {
			$cache->reset('trackbackATOM');
			$cache->contents = $result;
			$cache->update();
		}
	}
}
header('Content-Type: application/atom+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewTrackbackATOM', $cache->contents);
fireEvent('FeedOBEnd');
?>
