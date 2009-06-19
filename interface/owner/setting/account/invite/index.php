<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'name' => array('string', 'default' => ''),
		'identify' => array('string'),
		'comment' => array('string', 'default' => ''),
		'senderName' => array('string', 'default' => ''),
		'senderEmail' => array('email')
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('blog.user');
requireStrictRoute();
if (($service['type'] == 'single') || !Acl::check("group.creators"))
	Utils_Respond::ResultPage(false);
$useradd = Model_User::add($_POST['email'], $_POST['name']);
if($useradd !== true && $useradd != 9) {
	Utils_Respond::ResultPage($useradd);
}
$blogadd = addBlog(null, Model_User::getUserIdByEmail($_POST['email']),$_POST['identify']);
if($blogadd !== true) {
	if($useradd != 9) { // If user is created at this time, delete that user.
		Model_User::removePermanent(Model_User::getUserIdByEmail($_POST['email']));
	}
	Utils_Respond::ResultPage($blogadd);
}
$result = sendInvitationMail(null, Model_User::getUserIdByEmail($_POST['email']),$_POST['name'],$_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
Utils_Respond::ResultPage($result);
?>
