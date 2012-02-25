<?php
/// Copyright (c) 2004-2012, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
requireStrictRoute();
$replyId = $suri['id'];
$IV = array(
	'POST' => array(
		"name_$replyId" => array('string', 'default' => null),
		"password_$replyId" => array('string', 'default' => ''),
		"secret_$replyId" => array('string', 'default' => null),
		"homepage_$replyId" => array('string', 'default' => 'http://'),
		"comment_$replyId" => array('string', 'default' => '')
	)
);
if(!Validator::validate($IV))
	Respond::NotFoundPage();
list($entryId) = getCommentAttributes($blogid, $replyId, 'entry');
if (!doesHaveOwnership() && empty($_POST["name_$replyId"])) {
	printMobileErrorPage(_text('댓글 작성 오류.'), _text('이름을 입력해 주세요.'), "$blogURL/comment/comment/$replyId");
} else if (!doesHaveOwnership() && empty($_POST["comment_$replyId"])) {
	printMobileErrorPage(_text('댓글 작성 오류.'), _text('내용을 입력해 주세요.'), "$blogURL/comment/comment/$replyId");
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
	$result = addComment($blogid, $comment);
	if (in_array($result, array('ip', 'name', 'homepage', 'comment', 'openidonly', 'etc'))) {
		if ($result == 'openidonly') {
			$blockMessage = _text('댓글을 쓰기 위해서는 OpenID로 로그인해야 합니다.');
		} else {
			$blockMessage = _textf('%1 은 차단되었습니다.', $result);
		}
		printMobileErrorPage(_text('댓글 작성이 차단되었습니다.'), $blockMessage, "$blogURL/comment/$entryId");
	} else if ($result === false) {
		printMobileErrorPage(_text('댓글 작성 오류.'), _text('댓글을 작성할 수 없었습니다.'), "$blogURL/comment/$entryId");
	} else {
		setcookie('guestName', $comment['name'], time() + 2592000, $blogURL);
		setcookie('guestHomepage', $comment['homepage'], time() + 2592000, $blogURL);
		printMobileSimpleMessage(_text('댓글이 등록되었습니다.'), _text('댓글 페이지로 이동'), "$blogURL/comment/$entryId");
	}
}
?>
