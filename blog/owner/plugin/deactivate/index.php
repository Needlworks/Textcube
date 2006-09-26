<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default'=> null),
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_POST['name']) && deactivatePlugin($_POST['name']))
	respondResultPage(0);
respondResultPage(1);
?>
