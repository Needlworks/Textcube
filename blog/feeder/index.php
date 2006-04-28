<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
list($status, $url) = updateRandomFeed();
printRespond(array('error' => $status, 'url' => $url));
?>