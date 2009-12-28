<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
$IV = array(
	'POST' => array(
		'password' => array('string','default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
$entry = getEntry($blogid, $suri['id']);
if(!is_null($entry) && isset($_POST['password']) && $entry['password'] == $_POST['password']) {
	setcookie('GUEST_PASSWORD', $_POST['password'], time() + 86400, "$blogURL/");
	header("Location: $blogURL/{$suri['id']}");
}
else
	printMobileErrorPage(_text('비밀번호 확인'), _text('패스워드가 틀렸습니다.'), "$blogURL/{$suri['id']}");
?>
