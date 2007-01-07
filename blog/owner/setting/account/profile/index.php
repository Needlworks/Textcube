<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'nickname' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (changeSetting($owner, $_POST['email'], $_POST['nickname'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>