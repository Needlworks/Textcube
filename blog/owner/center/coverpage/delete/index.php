<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'coverpageNumber' => array('int'),
		'modulePos' => array('int'),
		'viewMode' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel("blog.coverpage");

$skin = new Skin($skinSetting['skin']);
$coverpageCount = count($skin->coverpageBasicModules);
$coverpageOrder = deleteCoverpageModuleOrderData(getCoverpageModuleOrderData($coverpageCount), $_GET['coverpageNumber'], $_GET['modulePos']);
setBlogSetting("coverpageOrder", serialize($coverpageOrder));

//printRespond(array('error' => 0));
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $blogURL . '/owner/center/coverpage' . $_GET['viewMode']);
?>
