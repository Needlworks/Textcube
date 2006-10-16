<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'name' => array('string', 'default' => null),
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_POST['name']) && clearPluginTable($_POST['name']))
	respondResultPage(0);
respondResultPage(1);
?>
