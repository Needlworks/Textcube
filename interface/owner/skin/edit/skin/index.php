<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
	
$result = writeSkinHtml($blogid, $_POST['body'], $_POST['mode'], $_POST['file']);
if ($result === true) {
	Skin::purgeCache();
	respond::PrintResult(array('error' => 0));
} else {
	respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
