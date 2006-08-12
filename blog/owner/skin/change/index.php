<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'skinName' => array('directory' ,'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$isAjaxRequest = isset($_GET['javascript']) && $_GET['javascript'] == "disabled" ? false : true;
$result = $branchFlag ? selectSkin($owner, $_GET['skinName']) : selectSkin($owner, $_POST['skinName']);
if ($result === true) {
	$isAjaxRequest ? printRespond(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$isAjaxRequest ? printRespond(array('error' => 1, 'msg' => "!!")) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
