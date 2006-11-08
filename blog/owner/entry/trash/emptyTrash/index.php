<?php
define('ROOT', '../../../../..');

$IV = array (
		'GET' => array(
			'type' => array('int'),
			'ajaxcall' => array ('any', 'mandatory' => false)
			)
		);

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if ($_GET['type'] == 1) {
	emptyTrash(true);
} else if ($_GET['type'] == 2) {
	emptyTrash(false);
} else {
	respondNotFoundPage();
}

if (array_key_exists('ajaxcall', $_GET)) respondResultPage(0);
else header("Location: " . $_SERVER['HTTP_REFERER']);
?>