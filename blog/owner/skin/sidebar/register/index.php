<?php
define('ROOT', '../../../../..');

$IV = array(
	'POST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int', 'mandatory' => false),
		'moduleId' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = addSidebarModuleOrderData(getSidebarModuleOrderData($sidebarCount), $_POST['sidebarNumber'], $_POST['modulePos'], array("id" => $_POST['moduleId'], "parameters" => NULL));
setUserSetting("sidebarOrder", serialize($sidebarOrder));

//printRespond(array('error' => 0));
header("Location: ".$_SERVER['HTTP_REFERER']);
?>