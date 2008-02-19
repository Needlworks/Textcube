<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
	
if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';

require ROOT . '/lib/includeForBlogOwner.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrderData = getSidebarModuleOrderData($sidebarCount);

if (!isset($_REQUEST['sidebarNumber']) || !is_numeric($_REQUEST['sidebarNumber'])) respond::NotFoundPage();
if (!isset($_REQUEST['modulePos']) || !is_numeric($_REQUEST['modulePos'])) respond::NotFoundPage();

$sidebarNumber = $_REQUEST['sidebarNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($sidebarNumber < 0) || ($sidebarNumber >= $sidebarCount)) respond::ErrorPage();
if (!isset($sidebarOrderData[$sidebarNumber]) || !isset($sidebarOrderData[$sidebarNumber][$modulePos])) respond::ErrorPage();

$pluginData = $sidebarOrderData[$sidebarNumber][$modulePos];
if ($pluginData['type'] != 3) respond::ErrorPage();

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
        $newParameter[$item['name']] = $_REQUEST[$item['name']];
	}
}

$sidebarOrderData[$sidebarNumber][$modulePos]['parameters'] = $newParameter;
setBlogSetting("sidebarOrder", serialize($sidebarOrderData));
Skin::purgeCache();
if ($ajaxcall == false) {
	if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];
	header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
}
?>
