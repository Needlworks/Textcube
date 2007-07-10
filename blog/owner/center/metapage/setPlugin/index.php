<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$ajaxcall= false;
if (isset($_REQUEST['ajaxcall'])) {
	$ajaxcall= true;
}


/*$IV = array(
	'REQUEST' => array(
		'metapageNumber' => array('int'),
		'modulePos' => array('int'),
		)
	);*/
	
if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';

require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.metapage");
requireStrictRoute();

$metapageOrderData = getMetapageModuleOrderData();

if (!isset($_REQUEST['metapageNumber']) || !is_numeric($_REQUEST['metapageNumber'])) respondNotFoundPage();
if (!isset($_REQUEST['modulePos']) || !is_numeric($_REQUEST['modulePos'])) respondNotFoundPage();

$metapageNumber = $_REQUEST['metapageNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($metapageNumber < 0)) respondErrorPage();
if (!isset($metapageOrderData[$metapageNumber]) || !isset($metapageOrderData[$metapageNumber][$modulePos])) respondErrorPage();

$pluginData = $metapageOrderData[$metapageNumber][$modulePos];
if ($pluginData['type'] != 3) respondErrorPage();

$plugin = $pluginData['id']['plugin'];
$handler = $pluginData['id']['handler'];
$oldParameters = $pluginData['parameters'];

$identifier = $plugin . '/' . $handler;

$parameters = array();
foreach($metapageMappings as $item) {
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

$metapageOrderData[$metapageNumber][$modulePos]['parameters'] = $newParameter;
setBlogSetting("metapageOrder", serialize($metapageOrderData));

if ($ajaxcall == false) {
	if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];
	header('Location: '. $blogURL . '/owner/center/metapage' . $_REQUEST['viewMode']);
}
?>