<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
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
	Respond::ResultPage(false);
$useradd = User::add($_POST['email'], $_POST['name']);
if($useradd !== true && $useradd != 9) {
	Respond::ResultPage($useradd);
}
$blogadd = addBlog(null, User::getUserIdByEmail($_POST['email']),$_POST['identify']);
if($blogadd !== true) {
	if($useradd != 9) { // If user is created at this time, delete that user.
		User::removePermanent(User::getUserIdByEmail($_POST['email']));
	}
	Respond::ResultPage($blogadd);
}
if(User::getEmail() == $_POST['email']) {
	$result = array('error'=>0,'message'=>_t('이 계정에 새로운 블로그를 추가하였습니다.'));
	Respond::ResultPage($result);
} else {
	$result = sendInvitationMail(null, User::getUserIdByEmail($_POST['email']),$_POST['name'],$_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
	Respond::ResultPage($result);
}
?>
