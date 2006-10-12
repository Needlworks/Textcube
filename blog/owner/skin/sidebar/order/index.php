<?php
define('ROOT', '../../../../..');
$IV = array(
	'REQUEST' => array(
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

if ($_REQUEST['targetPos'] < 0 || $_REQUEST['targetPos'] >= count($sidebarOrder[$_REQUEST['sidebarNumber']]) || $_REQUEST['targetSidebarNumber'] < 0 || $_REQUEST['targetSidebarNumber'] >= count($sidebarOrder)) {
	respondResultPage(-1);
} else {
	if (($_REQUEST['sidebarNumber'] == $_REQUEST['targetSidebarNumber'])
		&& ($_REQUEST['modulePos'] < $_REQUEST['targetPos'])) 
	{
		$_REQUEST['targetPos']--;
	}
	$temp = array_splice($sidebarOrder[$_REQUEST['sidebarNumber']], $_REQUEST['modulePos'], 1);
	array_splice($sidebarOrder[$_REQUEST['targetSidebarNumber']], $_REQUEST['targetPos'], 0, $temp);
	
	setUserSetting("sidebarOrder", serialize($sidebarOrder));
	respondResultPage(0);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header("Location: ".$_SERVER['HTTP_REFERER']);

?>
