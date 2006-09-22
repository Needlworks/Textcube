<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		'targetSidebarNumber' => array('int'),
		'targetPos' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = getSidebarModuleOrderData($sidebarCount);

if ($_GET['targetPos'] < 0 || $_GET['targetPos'] >= count($sidebarOrder[$_GET['sidebarNumber']]) || $_GET['targetSidebarNumber'] < 0 || $_GET['targetSidebarNumber'] >= count($sidebarOrder)) {
	//printRespond(array('error' => 1, 'msg' => _t('더 이상 이동할 수 없습니다.')));
	header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	$tempPos = $sidebarOrder[$_GET['sidebarNumber']][$_GET['modulePos']];
	$sidebarOrder[$_GET['sidebarNumber']][$_GET['modulePos']] = $sidebarOrder[$_GET['targetSidebarNumber']][$_GET['targetPos']];
	$sidebarOrder[$_GET['targetSidebarNumber']][$_GET['targetPos']] = $tempPos;
	
	setUserSetting("sidebarOrder", serialize($sidebarOrder));
	//printRespond(array('error' => 0));
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
