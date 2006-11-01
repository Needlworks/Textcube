<?php
define('ROOT', '../../../../..');
$ajaxcall= false;
if (isset($_REQUEST['ajaxcall'])) {
	$ajaxcall= true;
}


/*$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		)
	);*/
	
if (!array_key_exists($_REQUEST, 'viewMode')) $_REQUEST['viewMode'] = '';

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrderData = getSidebarModuleOrderData($sidebarCount);

if (!isset($_REQUEST['sidebarNumber']) || !is_numeric($_REQUEST['sidebarNumber'])) respondNotFoundPage();
if (!isset($_REQUEST['modulePos']) || !is_numeric($_REQUEST['modulePos'])) respondNotFoundPage();

$sidebarNumber = $_REQUEST['sidebarNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($sidebarNumber < 0) || ($sidebarNumber >= $sidebarCount)) respondErrorPage();
if (!isset($sidebarOrderData[$sidebarNumber]) || !isset($sidebarOrderData[$sidebarNumber][$modulePos])) respondErrorPage();

$pluginData = $sidebarOrderData[$sidebarNumber][$modulePos];
if ($pluginData['type'] != 3) respondErrorPage();

$plugin = $pluginData['id']['plugin'];
$handler = $pluginData['id']['handler'];
$oldParameters = $pluginData['parameters'];

$identifier = $plugin . '/' . $handler;

$parameters = array();
foreach($sidebarMappings as $item) {
	if (($item['plugin'] == $plugin) && ($item['handler'] == $handler)) {
		$parameters = $item['parameters'];
		break;
	}
}

$newParameter = array();

foreach($parameters as $item)
{
	if (isset($_REQUEST[$item['name']])) {
		switch($item['type']) {
			case 'string':
				break;
			case 'int':
				if (!is_numeric($_REQUEST[$item['name']])) {
					continue;
				}
				break;
			default:
				continue;
				break;
		}	
	}
	$newParameter[$item['name']] = $_REQUEST[$item['name']];
}

$sidebarOrderData[$sidebarNumber][$modulePos]['parameters'] = $newParameter;
setUserSetting("sidebarOrder", serialize($sidebarOrderData));

if ($ajaxcall == false) {
	if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];
	header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
}
?>