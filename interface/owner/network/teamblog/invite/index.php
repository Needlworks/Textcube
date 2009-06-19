<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'name' => array('string', 'default' => ''),
		'comment' => array('string', 'default' => ''),
		'senderName' => array('string', 'default' => ''),
		'senderEmail' => array('email')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (!acl::check('group.owners'))
	Utils_Respond::ResultPage(false);

$result = Model_Blog::addUser($_POST['email'], $_POST['name'], $_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
Utils_Respond::ResultPage($result);
?>
