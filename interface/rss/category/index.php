<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireModel("blog.category");

requireStrictBlogURL();

$children = array();
$cache = pageCache::getInstance();
if(!empty($suri['id'])) {
	$categoryId = $suri['id'];
	if(in_array($categoryId, getCategoryVisibilityList($blogid, 'private'))) return false;
	$categotyTitle = getCategoryNameById($categoryId);
} else if (!empty($suri['value'])) {
 	$categoryId = getCategoryIdByLabel(getBlogId(), $suri['value']);
	if(in_array($categoryId, getCategoryVisibilityList($blogid, 'private'))) return false;
	$categoryTitle = $suri['value'];
} else { 	// If no category is mentioned, redirect it to total rss.
	header ("Location: $hostURL$blogURL/rss");
	exit;
}

$cache->reset('categoryRSS-'.$categoryId);
if(!$cache->load()) {
	$categoryIds = array($categoryId);
	$parent = getParentCategoryId(getBlogId(),$categoryId);
	if($parent === null) {	// It's parent. let's find childs.
		$children = getChildCategoryId(getBlogId(),$categoryId);
		if(!empty($children)){
			$categoryIds = array_merge($categoryIds, $children);
		}
	}
	requireModel("blog.feed");
	$result = getCategoryFeedByCategoryId(getBlogId(),$categoryIds,'rss',$categoryTitle);
	if($result !== false) {
		$cache->reset('categoryRSS-'.$categoryId);
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewCategoryRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
