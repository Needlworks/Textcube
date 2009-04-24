<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'userid' => array('id')
	) 
);
require ROOT . '/library/preprocessor.php';

requireStrictRoute();
requirePrivilege('group.creators');

$authtoken = md5(Model_User::__generatePassword());
$result = POD::query("INSERT INTO `{$database['prefix']}UserSettings` (userid, name, value) VALUES ('".$_GET['userid']."', 'AuthToken', '$authtoken')");
if ($result) {
	respond::PrintResult(array('error' => 0));
	echo "s";
}
else {
	$result = _t('임시 암호 발급에 실패하였습니다.');
	respond::PrintResult(array('error' => -1 , 'result' =>$result));
}
?>
