<?php
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id'),
	)
);
require ROOT . '/lib/includeForOwner.php';
respondResultPage(markAsStar($owner, $_POST['id'], false));
?>