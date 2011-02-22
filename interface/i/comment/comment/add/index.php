<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
requireStrictRoute();
$replyId = $suri['id'];
$IV = array(
	'GET' => array(
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
if (!doesHaveOwnership() && empty($_GET["name_$replyId"])) {
	printIphoneErrorPage(_text('댓글 작성 오류.'), _text('이름을 입력해 주세요.'), "$blogURL/comment/comment/$replyId");
} else if (!doesHaveOwnership() && empty($_GET["comment_$replyId"])) {
	printIphoneErrorPage(_text('댓글 작성 오류.'), _text('내용을 입력해 주세요.'), "$blogURL/comment/comment/$replyId");
} else {
	$comment = array();
	$comment['entry'] = $entryId;
	$comment['parent'] = $replyId;
	$comment['name'] = empty($_GET["name_$replyId"]) ? '' : $_GET["name_$replyId"];
	$comment['password'] = empty($_GET["password_$replyId"]) ? '' : $_GET["password_$replyId"];
	$comment['homepage'] = empty($_GET["homepage_$replyId"]) || ($_GET["homepage_$replyId"] == 'http://') ? '' : $_GET["homepage_$replyId"];
	$comment['secret'] = empty($_GET["secret_$replyId"]) ? 0 : 1;
	$comment['comment'] = $_GET["comment_$replyId"];
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	$result = addComment($blogid, $comment);
	if (in_array($result, array('ip', 'name', 'homepage', 'comment', 'openidonly', 'etc'))) {
		if ($result == 'openidonly') {
			$blockMessage = _text('댓글을 쓰기 위해서는 OpenID로 로그인해야 합니다.');
		} else {
			$blockMessage = _textf('%1 은 차단되었습니다.', $result);
		}
		printIphoneErrorPage(_text('댓글 작성이 차단되었습니다.'), $blockMessage, "$blogURL/comment/$entryId");
	} else if ($result === false) {
		printIphoneErrorPage(_text('댓글 작성 오류.'), _text('댓글을 작성할 수 없었습니다.'), "$blogURL/comment/$entryId");
	} else {
		setcookie('guestName', $comment['name'], time() + 2592000, $blogURL);
		setcookie('guestHomepage', $comment['homepage'], time() + 2592000, $blogURL);
		printIphoneSimpleMessage(_text('댓글이 등록되었습니다.'), _text('댓글 페이지로 이동'), "$blogURL/comment/$entryId");
	}
}
?>
