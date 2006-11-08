<?php
define('ROOT', '../../../../..');

$IV = array (
		'GET' => array(
			'type' => array('int')
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

header("Location: " . $_SERVER['HTTP_REFERER']);
?>