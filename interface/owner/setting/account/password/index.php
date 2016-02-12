<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'pwd' => array('string','default'=>''),
		'prevPwd' => array('string','default'=>'')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$result = false;
$isAuthToken = Setting::getUserSetting('AuthToken',false,true) ? true : false;
if($_POST['pwd'] != '' && (($_POST['prevPwd'] != '') || ($isAuthToken != false))) {
	$result = changePassword(getUserId(), $_POST['pwd'], $_POST['prevPwd'], $isAuthToken);
}
if($result) Respond::ResultPage(0);
else Respond::ResultPage(-1);
?>
