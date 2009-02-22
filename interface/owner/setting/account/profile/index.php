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
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
if (User::changeSetting(getUserId(), $_POST['email'], $_POST['nickname'])) {
	respond::ResultPage(0);
}
respond::ResultPage( - 1);
?>
