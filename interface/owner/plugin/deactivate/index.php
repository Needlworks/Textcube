<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default'=> null)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('common.plugin');
requireStrictRoute();

if(empty($_POST['name'])) Respond::ResultPage(1);
$pluginInfo = getPluginInformation(trim($_POST['name']));
$pluginScope = $pluginInfo['scope'];
if(in_array('editor',$pluginScope) && $editorCount == 1)
	Respond::ResultPage(2);
if(in_array('formatter',$pluginScope) && $formatterCount == 1)
	Respond::ResultPage(2);
if (deactivatePlugin($_POST['name']))
	Respond::ResultPage(0);
Respond::ResultPage(1);
?>
