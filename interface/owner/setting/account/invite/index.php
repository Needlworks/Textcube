<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
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
require ROOT . '/lib/includeForBlogOwner.php';
requireModel('blog.user');
requireStrictRoute();
if (($service['type'] == 'single') || !Acl::check("group.creators"))
	respondResultPage(false);
$useradd = addUser($_POST['email'], $_POST['name']);
if($useradd !== true && $useradd != 9) {
	respondResultPage($useradd);
}
$blogadd = addBlog(null, getUserIdByEmail($_POST['email']),$_POST['identify']);
if($blogadd !== true) {
	if($useradd != 9) { // If user is created at this time, delete that user.
		deleteUser(getUserIdByEmail($_POST['email']));
	}
	respondResultPage($blogadd);
}
$result = sendInvitationMail(null, getUserIdByEmail($_POST['email']),$_POST['name'],$_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
respondResultPage($result);
?>
