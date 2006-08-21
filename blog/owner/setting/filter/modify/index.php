<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'oldValue' => array('any', 'mandatory' => false),
		'newValue' => array('any', 'mandatory' => false ),
	)
);
require ROOT . '/lib/includeForOwner.php';
if (modifyFilter($owner, $mode, $_GET['oldValue'], $_GET['newValue']) === true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>