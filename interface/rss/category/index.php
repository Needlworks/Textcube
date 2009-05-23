<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireModel("blog.feed");
requireModel("blog.entry");
requireModel("blog.category");

requireStrictBlogURL();
if(in_array($categoryId, getCategoryVisibilityList($blogid, 'private'))) return false;

$children = array();
$cache = new Cache_Page;
if(!empty($suri['id'])) {
	$categoryId = $suri['id'];
	$categotyTitle = getCategoryNameById($categoryId);
} else if (!empty($suri['value'])) {
 	$categoryId = getCategoryIdByLabel(getBlogId(), $suri['value']);
	$categoryTitle = $suri['value'];
} else { 	// If no category is mentioned, redirect it to total rss.
	header ("Location: $hostURL$blogURL/rss");
	exit;
}

$categoryIds = array($categoryId);
$parent = getParentCategoryId(getBlogId(),$categoryId);
if($parent === null) {	// It's parent. let's find childs.
	$children = getChildCategoryId(getBlogId(),$categoryId);
	if(!empty($children)){
		$categoryIds = array_merge($categoryIds, $children);
	}
}

$cache->name = 'categoryRSS_'.$categoryId;
if(!$cache->load()) {
	$result = getCategoryFeedByCategoryId(getBlogId(),$categoryIds,'rss',$categoryTitle);
	if($result !== false) {
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewCategoryRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
