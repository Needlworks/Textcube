<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '..');
require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
$fp = @fopen(ROOT . "/attach/$owner/index.gif", 'rb');
if (!$fp) {
	$fp = @fopen(ROOT . '/image/icon_blogIcon_default.png', 'rb');
	if (!$fp)
		respondNotFoundPage();
}
$fstat = fstat($fp);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	if ($modifiedSince && ($modifiedSince == $fstat['mtime'])) {
		fclose($fp);
		header('HTTP/1.1 304 Not Modified');
		header("Connection: close");
		exit;
	}
}
header('Last-Modified: ' . Timestamp::getRFC1123GMT($fstat['mtime']));
header('Cache-Control:');
header('Content-Type: image/gif');
header("Connection: close");
fpassthru($fp);
fclose($fp);
?>
