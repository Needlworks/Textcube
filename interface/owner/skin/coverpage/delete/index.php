<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'coverpageNumber' => array('int'),
		'modulePos' => array('int'),
		'viewMode' => array('string', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
importlib('blogskin');
importlib("model.blog.sidebar");
importlib("model.blog.coverpage");


$skin = new Skin($skinSetting['skin']);
$coverpageCount = count($skin->coverpageBasicModules);
$coverpageOrder = deleteCoverpageModuleOrderData(getCoverpageModuleOrderData($coverpageCount), $_GET['coverpageNumber'], $_GET['modulePos']);
Setting::setBlogSettingGlobal("coverpageOrder", serialize($coverpageOrder));

//Respond::PrintResult(array('error' => 0));
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/coverpage' . $_GET['viewMode']);
?>
