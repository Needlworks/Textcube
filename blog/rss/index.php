<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
publishEntries();
if (!file_exists(ROOT . "/cache/rss/$owner.xml"))
	refreshRSS($owner);
header('Content-Type: text/xml; charset=utf-8');
$fileHandle = fopen(ROOT . "/cache/rss/$owner.xml", 'r+');
$result = fread($fileHandle, filesize(ROOT . "/cache/rss/$owner.xml"));
fclose($fileHandle);
echo fireEvent('ViewRSS', $result);
?>
