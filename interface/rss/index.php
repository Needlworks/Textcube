<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('NO_SESSION', true);
require ROOT . '/lib/includeForBlog.php';
requireModel("blog.rss");
requireModel("blog.entry");

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
publishEntries();
if (!file_exists(ROOT . "/cache/rss/$blogid.xml"))
	refreshRSS($blogid);
header('Content-Type: text/xml; charset=utf-8');
$fileHandle = fopen(ROOT . "/cache/rss/$blogid.xml", 'r+');
$result = fread($fileHandle, filesize(ROOT . "/cache/rss/$blogid.xml"));
fclose($fileHandle);
echo fireEvent('ViewRSS', $result);
?>
