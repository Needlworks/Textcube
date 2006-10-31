<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = deleteSidebarModuleOrderData(getSidebarModuleOrderData($sidebarCount), $_GET['sidebarNumber'], $_GET['modulePos']);
setUserSetting("sidebarOrder", serialize($sidebarOrder));

//printRespond(array('error' => 0));
header("Location: ..");
?>
