<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
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
importlib('blogskin');
importlib("model.blog.sidebar");
$ctx = Model_Context::getInstance();

$skin = new Skin($ctx->getProperty('skin.skin'));
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = deleteSidebarModuleOrderData(getSidebarModuleOrderData($sidebarCount), $_GET['sidebarNumber'], $_GET['modulePos']);
Setting::setBlogSettingGlobal("sidebarOrder", serialize($sidebarOrder));
$skin->purgeCache();

//Respond::ResultPage(0);
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/sidebar' . $_GET['viewMode']);
?>
