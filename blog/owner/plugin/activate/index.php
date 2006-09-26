<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default' => null),
		'scope' => array('string', 'default' => 'global')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_POST['name']) && activatePlugin($_POST['name']))
	respondResultPage(0);
respondResultPage(1);
?>
