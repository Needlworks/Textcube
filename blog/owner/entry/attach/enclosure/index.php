<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'fileName' => array('filename'),
		'order' => array(array('0', '1'))
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = setEnclosure($_POST['fileName'], $_POST['order']);
printRespond(array('error' => $result < 3 ? 0 : 1, 'order' => $result));
?>