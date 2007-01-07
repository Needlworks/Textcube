<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'write' => array(array('0', '1')),
		'comment' => array(array('0', '1'))
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setGuestbook($owner, $_GET['write'], $_GET['comment'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>