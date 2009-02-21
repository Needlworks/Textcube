<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
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
publishEntries();
if (!file_exists(ROOT . "/cache/rss/$blogid.xml"))
	refreshFeed($blogid,'rss');
header('Content-Type: text/xml; charset=utf-8');
$fileHandle = fopen(ROOT . "/cache/rss/$blogid.xml", 'r+');
$result = fread($fileHandle, filesize(ROOT . "/cache/rss/$blogid.xml"));
fclose($fileHandle);
fireEvent('FeedOBStart');
echo fireEvent('ViewRSS', $result);
fireEvent('FeedOBEnd');

requireModel("blog.cron");
checkCronJob();
?>
