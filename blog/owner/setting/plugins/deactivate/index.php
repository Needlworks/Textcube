<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'name' => array('filename')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_GET['name'])) {
	deactivatePlugin($_GET['name']);
	respondResultPage(0);
}
respondResultPage(1);
?>
