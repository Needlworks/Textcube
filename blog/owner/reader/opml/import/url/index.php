<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
set_time_limit(60);
$result = importOPMLFromURL($owner, $_POST['url']);
printRespond($result);
?>