<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.feed");

requireStrictBlogURL();

$children = array();
$cache = pageCache::getInstance();

$cache->reset('linesATOM');
if(!$cache->load()) {
	$result = getLinesFeed(getBlogId(),'public','atom');
	if($result !== false) {
		$cache->reset('linesATOM');
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/atom+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewLinesATOM', $cache->contents);
fireEvent('FeedOBEnd');
?>
