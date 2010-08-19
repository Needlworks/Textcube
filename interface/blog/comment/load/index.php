<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';
requireModel('blog.entry');
$context = Model_Context::getInstance();
$skin = new Skin($context->getProperty('skin.skin'));
$entry = array();
$entry['id'] = $suri['id'];
$entry['slogan'] = getSloganById($blogid, $entry['id']);
$IV = array(
	'POST' => array(
		'page' => array('int',1),
		'listOnly' => array('int',0,1)
	)
);
$result['error'] = 0;
$result['commentBlock'] = revertTempTags(removeAllTags(getCommentView($entry, $skin, ($_POST['listOnly'] ? false:true),
	$_POST['page'], 20,true)));
Respond::PrintResult($result);
?>
