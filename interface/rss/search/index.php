<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireStrictBlogURL();

$search = isset($_GET['search']) ? $_GET['search'] : $suri['value'];
$search = isset($_GET['q']) ? $_GET['q'] : $search; // Consider the common search query GET name. (for compatibility)

$blogid = getBlogId();
list($entries, $paging) = getEntriesWithPagingBySearch($blogid, $search, 1, 1, 1);

if(empty($entries)) {
	header ("Location: ".$context->getProperty('uri.host').$context->getProperty('uri.blog')."/rss");
	exit;	
}

$cache = pageCache::getInstance();
$cache->reset('searchRSS-'.$search);
if(!$cache->load()) {
	importlib("model.blog.feed");
	$result = getSearchFeedByKeyword(getBlogId(),$search,'rss',$search);
	if($result !== false) {
		$cache->reset('searchRSS-'.$search);
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewSearchRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
