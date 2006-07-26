<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
publishEntries();
if (!file_exists(ROOT . "/cache/rss/$owner.xml"))
	refreshRSS($owner);
header('Content-Type: text/xml; charset=utf-8');
$fileHandle = fopen(ROOT . "/cache/rss/$owner.xml", 'r+');
$result = fread($fileHandle, filesize(ROOT . "/cache/rss/$owner.xml"));
fclose($fileHandle);
echo fireEvent('ViewRSS', $result);
?>