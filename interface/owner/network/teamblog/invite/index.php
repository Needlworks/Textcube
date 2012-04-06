<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
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
	Respond::ResultPage(false);

$result = Blog::addUser($_POST['email'], $_POST['name'], $_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
Respond::ResultPage($result);
?>
