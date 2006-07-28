<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
requireStrictRoute();
$entryId = $suri['id'];
$IV = array(
	'POST' => array(
		"name_$entryId" => array('string', 'default' => null),
		"password_$entryId" => array('string', 'default' => ''),
		"secret_$entryId" => array(array('on'), 'default' => null),
		"homepage_$entryId" => array('string', 'default' => 'http://'),
		"comment_$entryId" => array('string', 'default' => '')
	)
);
if(!Validator::validate($IV))
	respondNotFoundPage();
if (!doesHaveOwnership() && empty($_POST["name_$entryId"])) {
	printMobileErrorPage(_t('답글을 작성할 수 없습니다'), _t('이름을 입력해주세요'), "$blogURL/comment/$entryId");
} else if (!doesHaveOwnership() && empty($_POST["comment_$entryId"])) {
	printMobileErrorPage(_t('답글을 작성할 수 없습니다'), _t('본문을 입력해주세요'), "$blogURL/comment/$entryId");
} else {
	$comment = array();
	$comment['entry'] = $entryId;
	$comment['parent'] = null;
	$comment['name'] = empty($_POST["name_$entryId"]) ? '' : $_POST["name_$entryId"];
	$comment['password'] = empty($_POST["password_$entryId"]) ? '' : $_POST["password_$entryId"];
	$comment['homepage'] = empty($_POST["homepage_$entryId"]) || ($_POST["homepage_$entryId"] == 'http://') ? '' : $_POST["homepage_$entryId"];
	$comment['secret'] = empty($_POST["secret_$entryId"]) ? 0 : 1;
	$comment['comment'] = $_POST["comment_$entryId"];
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	$result = addComment($owner, $comment);
	if ($result === 'blocked') {
	} else if ($result === false) {
		printMobileErrorPage(_t('답글을 쓸 수 없습니다'), "$blogURL/comment/$entryId");
	} else {
		setcookie('guestName', $comment['name'], time() + 2592000, $blogURL);
		setcookie('guestHomepage', $comment['homepage'], time() + 2592000, $blogURL);
		printMobileSimpleMessage(_t('답글이 작성됐습니다'), _t('답글보기 페이지로'), "$blogURL/comment/$entryId");
	}
}
?>