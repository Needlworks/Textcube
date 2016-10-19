<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$ajaxcall = (isset($_REQUEST['ajaxcall']) && $_REQUEST['ajaxcall'] == true)  ? true : false;

$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		'targetSidebarNumber' => array('int'),
		'targetPos' => array('int'),
		'viewMode' => array('string', 'default' => '')
	)
);

require ROOT . '/library/preprocessor.php';
importlib('blogskin');
importlib("model.blog.sidebar");
requireStrictRoute();
$ctx = Model_Context::getInstance();

$skin = new Skin($ctx->getProperty('skin.skin'));
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrder = getSidebarModuleOrderData($sidebarCount);

if ($_REQUEST['targetPos'] < 0 || $_REQUEST['targetPos'] > count($sidebarOrder[$_REQUEST['sidebarNumber']]) || $_REQUEST['targetSidebarNumber'] < 0 || $_REQUEST['targetSidebarNumber'] >= count($sidebarOrder)) {
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
	else
		Respond::ResultPage(-1);
} else {
	if (($_REQUEST['sidebarNumber'] == $_REQUEST['targetSidebarNumber'])
		&& ($_REQUEST['modulePos'] < $_REQUEST['targetPos'])) 
	{
		$_REQUEST['targetPos']--;
	}
	$temp = array_splice($sidebarOrder[$_REQUEST['sidebarNumber']], $_REQUEST['modulePos'], 1);
	array_splice($sidebarOrder[$_REQUEST['targetSidebarNumber']], $_REQUEST['targetPos'], 0, $temp);
	
	Setting::setBlogSettingGlobal("sidebarOrder", serialize($sidebarOrder));
	$skin->purgeCache();
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
else
	Respond::ResultPage(0);
?>
