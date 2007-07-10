<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');

$IV = array(
	'REQUEST' => array(
		'metapageNumber' => array('int'),
		'modulePos' => array('int', 'default' => -1),
		'moduleId' => array('string', 'default' => ''),
		'viewMode' => array('string', 'default' => '')
		)
	);
require ROOT . '/lib/includeForBlogOwner.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel("blog.metapage");
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$metapageCount = count($skin->metapageBasicModules);

$module = explode(':', $_REQUEST['moduleId']);

if (($module !== false) && (count($module) == 3) && 
	($_REQUEST['metapageNumber'] >= 0) 	&& ($_REQUEST['metapageNumber'] < $metapageCount))
{
	$metapageOrder = getMetapageModuleOrderData($metapageCount);
	$metapageOrder = addMetapageModuleOrderData($metapageOrder, $_REQUEST['metapageNumber'], $_REQUEST['modulePos'], $module);
	if ($metapageOrder != null) {
		setBlogSetting("metapageOrder", serialize($metapageOrder));
	}
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header('Location: '. $blogURL . '/owner/center/metapage' . $_REQUEST['viewMode']);
?>