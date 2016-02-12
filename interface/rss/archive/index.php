<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireStrictBlogURL();
$context = Model_Context::getInstance();
$period = $suri['id'];
$blogid = getBlogId();

$cache = pageCache::getInstance();
$cache->reset('archiveRSS-'.$period);
if(!$cache->load()) {
	importlib("model.blog.feed");
	list($entries, $paging) = getEntriesWithPagingByPeriod($blogid, $period, 1, 1, 1);	
	//var_dump($entries);
	if(empty($entries)) {
		header ("Location: ".$context->getProperty('uri.host').$context->getProperty('uri.blog')."/rss");
		exit;	
	}
	$result = getFeedWithEntries($blogid,$entries,_textf('%1 기간의 글 목록',$period),'rss');
	if($result !== false) {
		$cache->reset('archiveRSS-'.$period);
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewArchiveRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
