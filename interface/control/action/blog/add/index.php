<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'identify' => array('string', 'min' => 1),
		'owner' => array('email')
	) 
);
require ROOT . '/library/preprocessor.php';

requireStrictRoute();
requirePrivilege('group.creators');
if ($uid = User::getUserIdByEmail($_GET['owner'])) {
	$result = addBlog('',$uid, $_GET['identify']);
	if ($result===true) {
		if($_GET['owner'] == User::getEmail(getUserId())) {	/// Update current user's access list.
			$priv = array();
			array_push($priv, "group.administrators");
			Acl::setAcl( $rec['blogid'], $priv, true );
		}
		Respond::PrintResult(array('error' => 0));
	}
	else {
		Respond::PrintResult(array('error' => -1 , 'result' =>$result));
	}
} else {
	Respond::PrintResult(array('error' => -2 , 'result' => _t('등록되지 않은 소유자 E-mail 입니다.')));
}
?>
