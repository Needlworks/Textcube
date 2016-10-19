<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);
define('__TEXTCUBE_CUSTOM_HEADER__', true);

if(isset($_GET['loginid'])) $_POST['loginid'] = $_GET['loginid'];
if(isset($_GET['key'])) $_POST['key'] = $_GET['key'];

$IV = array(
	'POST' => array(
		'loginid' => array('email'),
		'key' => array('string')
		)
);
require ROOT . '/library/preprocessor.php';
importlib("model.blog.feed");
importlib("model.blog.entry");

requireStrictBlogURL();
validateAPIKey(getBlogId(),$_POST['loginid'],$_POST['key']);

$cache = pageCache::getInstance();
$cache->reset('commentNotifiedRSS');
if(!$cache->load()) {
	$result = getCommentNotifiedFeedTotal(getBlogId());
	if($result !== false) {
		$cache->reset('commentNotifiedRSS');
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewCommentNotifiedRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>
