<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'email'    => array('email'),
		'nickname' => array('string')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (User::changeSetting(getUserId(), $_POST['email'], $_POST['nickname'])) {
	Respond::ResultPage(0);
}
Respond::ResultPage( - 1);
?>
