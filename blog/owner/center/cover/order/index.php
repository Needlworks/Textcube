<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'REQUEST' => array(
		'metapageNumber' => array('int'),
		'modulePos' => array('int'),
		'targetMetapageNumber' => array('int'),
		'targetPos' => array('int'),
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
$metapageOrder = getMetapageModuleOrderData($metapageCount);

if ($_REQUEST['targetPos'] < 0 || $_REQUEST['targetPos'] > count($metapageOrder[$_REQUEST['metapageNumber']]) || $_REQUEST['targetMetapageNumber'] < 0 || $_REQUEST['targetMetapageNumber'] >= count($metapageOrder)) {
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header('Location: '. $blogURL . '/owner/center/metapage' . $_REQUEST['viewMode']);
	else
		respondResultPage(-1);
} else {
	if (($_REQUEST['metapageNumber'] == $_REQUEST['targetMetapageNumber'])
		&& ($_REQUEST['modulePos'] < $_REQUEST['targetPos'])) 
	{
		$_REQUEST['targetPos']--;
	}
	$temp = array_splice($metapageOrder[$_REQUEST['metapageNumber']], $_REQUEST['modulePos'], 1);
	array_splice($metapageOrder[$_REQUEST['targetMetapageNumber']], $_REQUEST['targetPos'], 0, $temp);
	
	setBlogSetting("metapageOrder", serialize($metapageOrder));
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header('Location: '. $blogURL . '/owner/center/metapage' . $_REQUEST['viewMode']);
else
	respondResultPage(0);
?>
