<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
if(empty($suri['id'])) {
	$IV = array(
		'POST' => array(
			'replyId' => array('id'),
			'password' => array('string', 'mandatory' => false)
		)
	);
}
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
requireStrictRoute();

if(empty($suri['id'])) {
	list($entryId) = getCommentAttributes($blogid, $_POST['replyId'], 'entry');
	if (deleteComment($blogid, $_POST['replyId'], $entryId, isset($_POST['password']) ? $_POST['password'] : '') === false) {
		printMobileErrorPage(_text('댓글을 삭제하지 못했습니다'), _text('비밀번호가 맞지 않습니다.'), "$blogURL/comment/delete/{$_POST['replyId']}");
		exit();
	}
} else {
	list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
	if (deleteComment($blogid, $suri['id'], $entryId, '') === false) {
		printMobileErrorPage(_t('댓글을 삭제하지 못했습니다'), _t('관리자 권한이 필요합니다.'), "$blogURL/comment/delete/{$suri['id']}");
		exit();
	}
}
list($entries, $paging) = getEntryWithPaging($blogid, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileSimpleMessage(_text('댓글이 삭제되었습니다.'), _text('댓글 페이지로 이동'), "$blogURL/comment/$entryId");
?>
