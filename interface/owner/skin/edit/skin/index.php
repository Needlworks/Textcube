<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'body' => array('string'),
		'mode' => array('string'),
		'file' => array('string')
	)
);

require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireStrictRoute();
$skin = new Skin($skinSetting['skin']);
	
$result = writeSkinHtml($blogid, $_POST['body'], $_POST['mode'], $_POST['file']);
if ($result === true) {
	$skin->purgeCache();
	Respond::PrintResult(array('error' => 0));
} else {
	Respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
