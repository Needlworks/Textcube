<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'skinName' => array('directory' ,'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
$result = $isAjaxRequest ? selectSkin($owner, $_POST['skinName']) : selectSkin($owner, $_GET['skinName']);

if ($result === true) {
	$isAjaxRequest ? printRespond(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$isAjaxRequest ? printRespond(array('error' => 1, 'msg' => _t('스킨을 변경하지 못했습니다.'))) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
