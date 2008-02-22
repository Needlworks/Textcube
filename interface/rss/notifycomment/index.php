<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);

$IV = array(
	'POST' => array(
		'loginid' => array('string'),
		'key' => array('string')
		)
);
require ROOT . '/lib/includeForBlog.php';
requireModel("blog.rss");
requireModel("blog.entry");

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
validateAPIKey(getBlogId(),$_POST['loginid'],$_POST['key']);

$cache = new pageCache;
$cache->name = 'commentNotifiedRSS';
if(!$cache->load()) {
	$result = getCommentNotifiedRSSTotal(getBlogId());
	if($result !== false) {
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: text/xml; charset=utf-8');
echo fireEvent('ViewCommentNotifiedRSS', $cache->contents);
?>
