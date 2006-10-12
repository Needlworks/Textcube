<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'useIndependentPanel' => array('string', 'default' => "on"),
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if ($_POST['useIndependentPanel'] == "on") {
	setUserSetting('tattertoolsDashboard', 1);
	respondResultPage(0);
} else if ($_POST['useIndependentPanel'] == "off") {
	setUserSetting('tattertoolsDashboard', 0);
	respondResultPage(0);
} else {
	respondResultPage(1);
}
?>
