<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setGuestbook($owner, $_GET['write'], $_GET['comment'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>