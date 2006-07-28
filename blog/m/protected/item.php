<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
$IV = array(
	'POST' => array(
		'password' => array('string','default' => null)
	)
);
require ROOT . '/lib/include.php';
$entry = getEntry($owner, $suri['id']);
if(isset($_POST['password']) && $entry['password'] == $_POST['password']) {
	setcookie('GUEST_PASSWORD', $_POST['password'], time() + 86400, "$blogURL/");
	header("Location: $blogURL/{$suri['id']}");
}
else
	printMobileErrorPage(_t('비밀번호 확인'), _t('패스워드가 틀렸습니다.'), "$blogURL/{$suri['id']}");
?>