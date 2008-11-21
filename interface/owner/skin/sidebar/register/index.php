<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$ajaxcall = (isset($_REQUEST['ajaxcall']) && $_REQUEST['ajaxcall'] == true) ? true : false;

$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int', 'default' => -1),
		'moduleId' => array('string', 'default' => ''),
		'viewMode' => array('string', 'default' => '')
		)
	);
 
requireModel("blog.sidebar");
requireStrictRoute();

$skin = new BlogSkin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);

$module = explode(':', $_REQUEST['moduleId']);

if (($module !== false) && (count($module) == 3) && 
	($_REQUEST['sidebarNumber'] >= 0) 	&& ($_REQUEST['sidebarNumber'] < $sidebarCount))
{
	$sidebarOrder = getSidebarModuleOrderData($sidebarCount);
	$sidebarOrder = addSidebarModuleOrderData($sidebarOrder, $_REQUEST['sidebarNumber'], $_REQUEST['modulePos'], $module);
	if (!is_null($sidebarOrder)) {
		setBlogSetting("sidebarOrder", serialize($sidebarOrder));
		Skin::purgeCache();
	}
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if($ajaxcall == false) {
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
} else {
	Respond::ResultPage(0);
}
?>
