<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'metapageNumber' => array('int'),
		'modulePos' => array('int'),
		'viewMode' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel("blog.metapage");

$skin = new Skin($skinSetting['skin']);
$metapageCount = count($skin->metapageBasicModules);
$metapageOrder = deleteMetapageModuleOrderData(getMetapageModuleOrderData($metapageCount), $_GET['metapageNumber'], $_GET['modulePos']);
setBlogSetting("metapageOrder", serialize($metapageOrder));

//printRespond(array('error' => 0));
if ($_GET['viewMode'] != '') $_GET['viewMode'] = '?' . $_GET['viewMode'];
header('Location: '. $blogURL . '/owner/center/metapage' . $_GET['viewMode']);
?>
