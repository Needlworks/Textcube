<?
if (!empty($_GET['TSSESSION']))
	$_COOKIE['TSSESSION'] = $_GET['TSSESSION'];
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'Filename' => array('filename')
	),
	'FILES' => array(
		'Filedata' => array('file')
	)
);
require ROOT . '/lib/includeForOwner.php';
$file = array_pop($_FILES);
$attachment = addAttachment($owner, $suri['id'], $file);
?>
