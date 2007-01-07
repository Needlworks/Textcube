<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
$fp = @fopen(ROOT . "/attach/$owner/favicon.ico", 'rb');
if (!$fp) {
	$fp = @fopen(ROOT . '/image/icon_favicon_default.ico', 'rb');
	if (!$fp)
		respondNotFoundPage();
}
$fstat = fstat($fp);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	if ($modifiedSince && ($modifiedSince >= $fstat['mtime'])) {
		fclose($fp);
		header('HTTP/1.1 304 Not Modified');
		header("Connection: close");
		exit;
	}
}
header('Last-Modified: ' . Timestamp::getRFC1123GMT($fstat['mtime']));
header('Cache-Control:');
header('Content-Type: text/plain');
header("Connection: close");
fpassthru($fp);
fclose($fp);
?>
