<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'name' => array('string', 'default' => ''),
		'identify' => array('string'),
		'comment' => array('string', 'default' => ''),
		'senderName' => array('string', 'default' => ''),
		'senderEmail' => array('email')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (($service['type'] == 'single') || (getUserId() > 1))
	respondResultPage(false);
$result = addUser($_POST['email'], $_POST['name'], $_POST['identify'], $_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
respondResultPage($result);
?>
