<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$IV = array(
	'GET' => array(
		'user' => array('email'),
		'blogid' => array('id')
	) 
);
requireStrictRoute();
requirePrivilege('group.creators');

$userid = User::getUserIdByEmail($_GET['user']);
$bid = $_GET['blogid'];
if (empty($userid)) {
	Respond::ResultPage(array(-1,"존재하지 않는 사용자"));
}

$acl = POD::queryCell("SELECT acl FROM {$database['prefix']}Privileges WHERE blogid='$bid' and userid='$userid'");

if( $acl === null ) { // If there is no ACL, add user into the blog.
	POD::query("INSERT INTO `{$database['prefix']}Privileges`  
		VALUES('$bid', '$userid',0, UNIX_TIMESTAMP(), '0')");
	Respond::ResultPage(0);
}
else {
	Respond::ResultPage(array(-2,"이미 참여중인 사용자"));
}
?>
