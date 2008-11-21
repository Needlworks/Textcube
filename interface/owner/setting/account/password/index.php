<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'pwd' => array('string','default'=>''),
		'prevPwd' => array('string','default'=>'')
	)
);
requireStrictRoute();
$result = false;
$isAuthToken = getUserSetting('AuthToken',false) ? true : false;
if($_POST['pwd'] != '' && (($_POST['prevPwd'] != '') || ($isAuthToken != false))) {
	$result = changePassword(getUserId(), $_POST['pwd'], $_POST['prevPwd'], $isAuthToken);
}
if($result) Respond::ResultPage(0);
else Respond::ResultPage(-1);
?>
