<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'pwd' => array('string'),
		'prevPwd' => array('string')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (changePassword($owner, $_POST['pwd'], $_POST['prevPwd'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
