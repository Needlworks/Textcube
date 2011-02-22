<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$ajaxcall= false;
if (isset($_REQUEST['ajaxcall'])) {
	$ajaxcall= true;
}


/*$IV = array(
	'REQUEST' => array(
		'coverpageNumber' => array('int'),
		'modulePos' => array('int'),
		)
	);*/
	
if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';

require ROOT . '/library/preprocessor.php';
requireModel("blog.coverpage");

requireStrictRoute();

$coverpageOrderData = getCoverpageModuleOrderData();

if (!isset($_REQUEST['coverpageNumber']) || !is_numeric($_REQUEST['coverpageNumber'])) Respond::NotFoundPage();
if (!isset($_REQUEST['modulePos']) || !is_numeric($_REQUEST['modulePos'])) Respond::NotFoundPage();

$coverpageNumber = $_REQUEST['coverpageNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($coverpageNumber < 0)) Respond::ErrorPage();
if (!isset($coverpageOrderData[$coverpageNumber]) || !isset($coverpageOrderData[$coverpageNumber][$modulePos])) Respond::ErrorPage();

$pluginData = $coverpageOrderData[$coverpageNumber][$modulePos];
if ($pluginData['type'] != 3) Respond::ErrorPage();

$plugin = $pluginData['id']['plugin'];
$handler = $pluginData['id']['handler'];
$oldParameters = $pluginData['parameters'];

$identifier = $plugin . '/' . $handler;

$parameters = array();
foreach($coverpageMappings as $item) {
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
$eventName = 'ModifyPluginParam_'.$plugin;
fireEvent($eventName,null,$plugin);
$coverpageOrderData[$coverpageNumber][$modulePos]['parameters'] = $newParameter;
setBlogSetting("coverpageOrder", serialize($coverpageOrderData));

if ($ajaxcall == false) {
	if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];
	header('Location: '. $blogURL . '/owner/skin/coverpage' . $_REQUEST['viewMode']);
}
?>
