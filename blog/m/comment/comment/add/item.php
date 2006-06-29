<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../../..');
require ROOT . '/lib/include.php';
$replyId = $suri['id'];
list($entryId) = getCommentAttributes($owner, $replyId, 'entry');
if (!doesHaveOwnership() && empty($_POST["name_$replyId"])) {
	printMobileErrorPage(_t('답글을 작성할 수 없습니다.'), _t('이름을 입력해 주십시오.'), "$blogURL/comment/comment/$replyId");
} else if (!doesHaveOwnership() && empty($_POST["comment_$replyId"])) {
	printMobileErrorPage(_t('답글을 작성할 수 없습니다.'), _t('본문을 입력해 주십시오.'), "$blogURL/comment/comment/$replyId");
} else {
	$comment = array();
	$comment['entry'] = $entryId;
	$comment['parent'] = $replyId;
	$comment['name'] = empty($_POST["name_$replyId"]) ? '' : $_POST["name_$replyId"];
	$comment['password'] = empty($_POST["password_$replyId"]) ? '' : $_POST["password_$replyId"];
	$comment['homepage'] = empty($_POST["homepage_$replyId"]) || ($_POST["homepage_$replyId"] == 'http://') ? '' : $_POST["homepage_$replyId"];
	$comment['secret'] = empty($_POST["secret_$replyId"]) ? 0 : 1;
	$comment['comment'] = $_POST["comment_$replyId"];
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	$result = addComment($owner, $comment);
	if ($result === 'blocked') {
		printMobileErrorPage(_t('답글쓰기가 차단됐습니다.'), "$blogURL/comment/$entryId");
	} else if ($result === false) {
		printMobileErrorPage(_t('답글을 쓸 수 없습니다.'), "$blogURL/comment/$entryId");
	} else {
		setcookie('guestName', $comment['name'], time() + 2592000, $blogURL);
		setcookie('guestHomepage', $comment['homepage'], time() + 2592000, $blogURL);
		printMobileSimpleMessage(_t('답글이 작성됐습니다.'), _t('답글 보기 페이지로'), "$blogURL/comment/$entryId");
	}
}
?>