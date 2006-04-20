<?
if (!empty($_GET['TSSESSION']))
	$_COOKIE['TSSESSION'] = $_GET['TSSESSION'];
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$file = array_pop($_FILES);
$attachment = addAttachment($owner, $suri['id'], $file);
?>
