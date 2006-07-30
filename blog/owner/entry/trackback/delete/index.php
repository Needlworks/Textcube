<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'targets' => array('list')
	)
);
require ROOT . '/lib/includeForOwner.php';
foreach(explode(',', $_POST['targets']) as $target)
	trashTrackback($owner, $targets);
respondResultPage(0);
?>
