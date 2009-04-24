<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$IV = array(
	'GET' => array(
		'user' => array('email'),
		'blogid' => array('id')
	) 
);
requireStrictRoute();
requirePrivilege('group.creators');

$userid = Model_User::getUserIdByEmail($_GET['user']);
$bid = $_GET['blogid'];
if (empty($userid)) {
	respond::ResultPage(array(-1,"존재하지 않는 사용자"));
}

$acl = POD::queryCell("SELECT acl FROM {$database['prefix']}Privileges WHERE blogid='$bid' and userid='$userid'");

if( $acl === null ) { // If there is no ACL, add user into the blog.
	POD::query("INSERT INTO `{$database['prefix']}Privileges`  
		VALUES('$bid', '$userid',0, UNIX_TIMESTAMP(), '0')");
	respond::ResultPage(0);
}
else {
	respond::ResultPage(array(-2,"이미 참여중인 사용자"));
}
?>
