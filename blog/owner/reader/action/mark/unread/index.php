<?php
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id' => array('id')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(markAsUnread($owner, $_POST['id']));
?>
