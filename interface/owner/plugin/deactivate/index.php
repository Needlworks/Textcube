<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default'=> null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel('common.plugin');
requireStrictRoute();

if(empty($_POST['name'])) respondResultPage(1);
$pluginInfo = getPluginInformation(trim($_POST['name']));
$pluginScope = $pluginInfo['scope'];
if(in_array('editor',$pluginScope) && $editorCount == 1)
	respondResultPage(2);
if(in_array('formatter',$pluginScope) && $formatterCount == 1)
	respondResultPage(2);
if (deactivatePlugin($_POST['name']))
	respondResultPage(0);
respondResultPage(1);
?>
