<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'name' => array('string'),
		'rss' => array('string', 'default' => ''),
		'url' => array('string'),
		'category' => array('int','mandatory'=>false),
		'newCategory' => array('string','mandatory'=>false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

requireStrictRoute();
if(strpos($_POST['rss'],'http://') !== 0) $_POST['rss'] = 'http://'.$_POST['rss'];
if(strpos($_POST['url'],'http://') !== 0) $_POST['url'] = 'http://'.$_POST['url'];
Respond::ResultPage(addLink($blogid, $_POST));
?>
