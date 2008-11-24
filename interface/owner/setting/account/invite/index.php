<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
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
require ROOT . '/library/dispatcher.php';
requireModel('blog.user');
requireStrictRoute();
if (($service['type'] == 'single') || !Acl::check("group.creators"))
	respond::ResultPage(false);
$useradd = User::add($_POST['email'], $_POST['name']);
if($useradd !== true && $useradd != 9) {
	respond::ResultPage($useradd);
}
$blogadd = addBlog(null, User::getUserIdByEmail($_POST['email']),$_POST['identify']);
if($blogadd !== true) {
	if($useradd != 9) { // If user is created at this time, delete that user.
		User::removePermanent(User::getUserIdByEmail($_POST['email']));
	}
	respond::ResultPage($blogadd);
}
$result = sendInvitationMail(null, User::getUserIdByEmail($_POST['email']),$_POST['name'],$_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
respond::ResultPage($result);
?>
