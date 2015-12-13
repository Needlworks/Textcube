<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'REQUEST' => array(
		'coverpageNumber' => array('int'),
		'modulePos' => array('int'),
		'targetCoverpageNumber' => array('int'),
		'targetPos' => array('int'),
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
$coverpageOrder = getCoverpageModuleOrderData($coverpageCount);

if ($_REQUEST['targetPos'] < 0 || $_REQUEST['targetPos'] > count($coverpageOrder[$_REQUEST['coverpageNumber']]) || $_REQUEST['targetCoverpageNumber'] < 0 || $_REQUEST['targetCoverpageNumber'] >= count($coverpageOrder)) {
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/coverpage' . $_REQUEST['viewMode']);
	else
		Respond::ResultPage(-1);
} else {
	if (($_REQUEST['coverpageNumber'] == $_REQUEST['targetCoverpageNumber'])
		&& ($_REQUEST['modulePos'] < $_REQUEST['targetPos'])) 
	{
		$_REQUEST['targetPos']--;
	}
	$temp = array_splice($coverpageOrder[$_REQUEST['coverpageNumber']], $_REQUEST['modulePos'], 1);
	array_splice($coverpageOrder[$_REQUEST['targetCoverpageNumber']], $_REQUEST['targetPos'], 0, $temp);
	
	Setting::setBlogSettingGlobal("coverpageOrder", serialize($coverpageOrder));
}

if ($_REQUEST['viewMode'] != '') $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/coverpage' . $_REQUEST['viewMode']);
else
	Respond::ResultPage(0);
?>
