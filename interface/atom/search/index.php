<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireStrictBlogURL();

$search = isset($_GET['search']) ? $_GET['search'] : $suri['value'];
$search = isset($_GET['q']) ? $_GET['q'] : $search; // Consider the common search query GET name. (for compatibility)
$list = array('title' => '', 'items' => array(), 'count' => 0);

$blogid = getBlogId();
list($entries, $paging) = getEntriesWithPagingBySearch($blogid, $search, 1, 1, 1);

if(empty($entries)) {
	header ("Location: $hostURL$blogURL/atom");
	exit;	
}

$cache = pageCache::getInstance();
$cache->name = 'searchATOM-'.$search;
if(!$cache->load()) {
	requireModel("blog.feed");
	$result = getSearchFeedByKeyword(getBlogId(),$search,'atom',$search);
	if($result !== false) {
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/atom+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewSearchATOM', $cache->contents);
fireEvent('FeedOBEnd');
?>
