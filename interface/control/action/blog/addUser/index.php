<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
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
$pool = DBModel::getInstance();
$pool->init("Privileges");
$pool->setQualifier("blogid","eq",$bid);
$pool->setQualifier("userid","eq",$userid);
$acl = $pool->getCell("acl");

if( $acl === null ) { // If there is no ACL, add user into the blog.
    $pool->init("Privileges");
    $pool->setAttribute("blogid",$bid);
    $pool->setAttribute("userid",$userid);
    $pool->setAttribute("acl",0);
    $pool->setAttribute("created",Timestamp::getUNIXtime());
    $pool->setAttribute("lastlogin",0);
    $pool->insert();
	Respond::ResultPage(0);
}
else {
	Respond::ResultPage(array(-2,"이미 참여중인 사용자"));
}
?>
