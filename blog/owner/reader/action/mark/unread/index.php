<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(markAsUnread($owner, $_POST['id']));
?>