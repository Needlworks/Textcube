<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'name' => array('filename')
	)
);
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['name']) && activatePlugin($_GET['name']))
	respondResultPage(0);
respondResultPage(1);
?>
