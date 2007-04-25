<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'write' => array(array('0', '1')),
		'comment' => array(array('0', '1'))
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (setGuestbook($owner, $_GET['write'], $_GET['comment'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>