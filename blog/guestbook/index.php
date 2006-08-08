<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
notifyComment();
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/guestbook.php';
require ROOT . '/lib/piece/blog/end.php';
?>
