<?php
define('ROOT', '../../../../..');

$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int', 'default' => -1),
		'moduleId' => array('string', 'default' => '')
		)
	);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);

$module = explode(':', $_REQUEST['moduleId']);
if (($module !== false) && (count($module) == 3) && 
	($_REQUEST['sidebarNumber'] >= 0) 	&& ($_REQUEST['sidebarNumber'] < $sidebarCount))
{
	$sidebarOrder = getSidebarModuleOrderData($sidebarCount);
	$sidebarOrder = addSidebarModuleOrderData($sidebarOrder, $_REQUEST['sidebarNumber'], $_REQUEST['modulePos'], $module);
	if ($sidebarOrder != null) {
		setUserSetting("sidebarOrder", serialize($sidebarOrder));
	}
}

header("Location: ".$_SERVER['HTTP_REFERER']);
?>