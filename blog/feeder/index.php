<?
define('ROOT', '../..');
require ROOT . '/lib/includeForOwner.php';
list($status, $url) = updateRandomFeed();
printRespond(array('error' => $status, 'url' => $url));
?>