<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'email'    => array('email'),
		'nickname' => array('string')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (Model_User::changeSetting(getUserId(), $_POST['email'], $_POST['nickname'])) {
	Utils_Respond::ResultPage(0);
}
Utils_Respond::ResultPage( - 1);
?>
