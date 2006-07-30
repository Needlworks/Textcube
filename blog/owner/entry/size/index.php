<?php
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'parent' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = getAttachmentSizeLabel($owner, $_GET['parent']);
printRespond(array ('error' => empty($result) ? 1 : 0, 'result' => $result));
?> 