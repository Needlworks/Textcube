<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		'viewMode' => array('string', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = deleteSidebarModuleOrderData(getSidebarModuleOrderData($sidebarCount), $_GET['sidebarNumber'], $_GET['modulePos']);
setBlogSetting("sidebarOrder", serialize($sidebarOrder));
$skin->purgeCache();

//Respond::ResultPage(0);
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $blogURL . '/owner/skin/sidebar' . $_GET['viewMode']);
?>
