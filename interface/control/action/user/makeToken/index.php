<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'userid' => array('id')
	) 
);
require ROOT . '/library/preprocessor.php';

requireStrictRoute();
requirePrivilege('group.creators');

$authtoken = md5(User::__generatePassword());
$result = POD::query("INSERT INTO `{$database['prefix']}UserSettings` (userid, name, value) VALUES ('".$_GET['userid']."', 'AuthToken', '$authtoken')");
if ($result) {
	Respond::PrintResult(array('error' => 0));
	echo "s";
}
else {
	$result = _t('임시 암호 발급에 실패하였습니다.');
	Respond::PrintResult(array('error' => -1 , 'result' =>$result));
}
?>
