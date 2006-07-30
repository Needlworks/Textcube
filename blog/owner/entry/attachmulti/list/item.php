<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'name' => array('filename')
	)
);
require ROOT . '/lib/includeForOwner.php';
$file = array_pop($_FILES);
$attachment = getAttachmentByLabel($owner, $suri['id'], $_GET['name']);
$result = escapeJSInCData(getPrettyAttachmentLabel($attachment)) . '!^|' . escapeJSInCData(getAttachmentValue($attachment));
echo 'result=' . trim($result);
?>
