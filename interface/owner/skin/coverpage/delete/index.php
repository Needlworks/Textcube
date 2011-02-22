<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel("blog.coverpage");


$skin = new Skin($skinSetting['skin']);
$coverpageCount = count($skin->coverpageBasicModules);
$coverpageOrder = deleteCoverpageModuleOrderData(getCoverpageModuleOrderData($coverpageCount), $_GET['coverpageNumber'], $_GET['modulePos']);
setBlogSetting("coverpageOrder", serialize($coverpageOrder));

//Respond::PrintResult(array('error' => 0));
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $blogURL . '/owner/skin/coverpage' . $_GET['viewMode']);
?>
