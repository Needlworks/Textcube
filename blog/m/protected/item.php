<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
$IV = array(
	'POST' => array(
		'password' => array('string','default' => null)
	)
);
require ROOT . '/lib/includeForBlog.php';
$entry = getEntry($owner, $suri['id']);
if(isset($_POST['password']) && $entry['password'] == $_POST['password']) {
	setcookie('GUEST_PASSWORD', $_POST['password'], time() + 86400, "$blogURL/");
	header("Location: $blogURL/{$suri['id']}");
}
else
	printMobileErrorPage(_text('비밀번호 확인'), _text('패스워드가 틀렸습니다.'), "$blogURL/{$suri['id']}");
?>
