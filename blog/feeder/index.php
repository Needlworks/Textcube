<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
list($status, $url) = updateRandomFeed();
printRespond(array('error' => $status, 'url' => $url));
?>
