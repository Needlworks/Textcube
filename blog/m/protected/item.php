<?php
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
require ROOT . '/lib/include.php';

$entry = getEntry($owner, $suri['value']);
if(isset($_POST['password']) && $entry['password'] == $_POST['password']) {
	setcookie('GUEST_PASSWORD', $_POST['password'], time() + 86400, "$blogURL/");
	header("Location: $blogURL/{$suri['value']}");
}
else
	printMobileErrorPage(_text('비밀번호 확인'), _text('패스워드가 틀렸습니다.'), "$blogURL/{$suri['value']}");
?>