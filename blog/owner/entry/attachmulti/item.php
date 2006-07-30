<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'Filename' => array('filename')
	),
	'FILES' => array(
		'Filedata' => array('file')
	),
	'GET' => array( 
		'TSSESSION' => array( 'string' , 'default' => null) 
	)
);
if (!empty($_GET['TSSESSION']))
	$_COOKIE['TSSESSION'] = $_GET['TSSESSION'];
require ROOT . '/lib/includeForOwner.php';
$file = array_pop($_FILES);
$attachment = addAttachment($owner, $suri['id'], $file);
?>
