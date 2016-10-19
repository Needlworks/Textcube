<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'REQUEST' => array(
		'coverpageNumber' => array('int'),
		'modulePos' => array('int', 'default' => -1),
		'moduleId' => array('string', 'default' => ''),
		'viewMode' => array('string', 'default' => '')
		)
	);
require ROOT . '/library/preprocessor.php';
importlib('blogskin');
importlib("model.blog.sidebar");
importlib("model.blog.coverpage");
requireStrictRoute();

$ctx = Model_Context::getInstance();

$skin = new Skin($ctx->getProperty('skin.skin'));
$coverpageCount = count($skin->coverpageBasicModules);

$module = explode(':', $_REQUEST['moduleId']);

if (($module !== false) && (count($module) == 3) && 
	($_REQUEST['coverpageNumber'] >= 0) 	&& ($_REQUEST['coverpageNumber'] < $coverpageCount))
{
	$coverpageOrder = getCoverpageModuleOrderData($coverpageCount);
	$coverpageOrder = addCoverpageModuleOrderData($coverpageOrder, $_REQUEST['coverpageNumber'], $_REQUEST['modulePos'], $module);
	if (!is_null($coverpageOrder)) {
		Setting::setBlogSettingGlobal("coverpageOrder", serialize($coverpageOrder));
	}
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/coverpage' . $_REQUEST['viewMode']);
?>
