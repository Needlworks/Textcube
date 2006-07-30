<?php
define('ROOT', '../../../../../..');
$VI = array(
	'POST' => array(
		'url' => array('url')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
set_time_limit(60);
$result = importOPMLFromURL($owner, $_POST['url']);
printRespond($result);
?>