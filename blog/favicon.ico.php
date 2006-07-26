<?php
define('ROOT', '..');
require ROOT . '/lib/include.php';
$fp = @fopen(ROOT . "/attach/$owner/favicon.ico", 'rb');
if (!$fp) {
	$fp = @fopen(ROOT . '/image/Tattertools.ico', 'rb');
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