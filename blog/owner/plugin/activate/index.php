<?php
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'name' => array('directory' , 'default' => null)
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_GET['name']) && activatePlugin($_GET['name']))
	respondResultPage(0);
respondResultPage(1);
?>
