<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$ajaxcall = (isset($_REQUEST['ajaxcall']) && $_REQUEST['ajaxcall'] == true) ? true : false;

/*$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		'viewMode' => array('string','default'=>'')
	)
);*/
	
require ROOT . '/library/includeForBlogOwner.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireComponent("Textcube.Function.Respond");
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrderData = getSidebarModuleOrderData($sidebarCount);

if (!isset($_REQUEST['sidebarNumber']) || !is_numeric($_REQUEST['sidebarNumber'])) respond::NotFoundPage($ajaxcall);
if (!isset($_REQUEST['modulePos']) || !is_numeric($_REQUEST['modulePos'])) respond::NotFoundPage($ajaxcall);

$sidebarNumber = $_REQUEST['sidebarNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($sidebarNumber < 0) || ($sidebarNumber >= $sidebarCount)) respond::ErrorPage(null,null,null,$ajaxcall);
if (!isset($sidebarOrderData[$sidebarNumber]) || !isset($sidebarOrderData[$sidebarNumber][$modulePos])) respond::ErrorPage(null,null,null,$ajaxcall);

$pluginData = $sidebarOrderData[$sidebarNumber][$modulePos];
if ($pluginData['type'] != 3) respond::ErrorPage(null,null,null,$ajaxcall);

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
setting::setBlogSettingGlobal("sidebarOrder", serialize($sidebarOrderData));
Skin::purgeCache();
if ($ajaxcall == false) {
	if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];
	header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
} else {
	respond::ResultPage(0);
}
?>
