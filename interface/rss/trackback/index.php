<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/includeForBlog.php';
requireModel("blog.feed");
requireModel("blog.entry");

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
$cache = new pageCache;
if(!empty($suri['id'])) {
	$cache->name = 'trackbackRSS_'.$suri['id'];
	if(!$cache->load()) {
		$result = getTrackbackFeedByEntryId(getBlogId(),$suri['id']);
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
} else {
	$cache->name = 'trackbackRSS';
	if(!$cache->load()) {
		$result = getTrackbackFeedTotal(getBlogId());
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
}
header('Content-Type: text/xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewTrackbackRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
