<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
if(empty($suri['id'])) {
$IV = array(
	'POST' => array(
		'replyId' => array('id'),
		'password' => array('string', 'mandatory' => false)
	)
);
}
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
requireStrictRoute();

if(empty($suri['id'])) {
	list($entryId) = getCommentAttributes($blogid, $_POST['replyId'], 'entry');
	if (deleteComment($blogid, $_POST['replyId'], $entryId, isset($_POST['password']) ? $_POST['password'] : '') === false) {
		printMobileErrorPage(_text('댓글을 삭제할 수 없습니다.'), _text('비밀번호가 일치하지 않습니다.'), "$blogURL/comment/delete/{$_POST['replyId']}");
		exit();
	}
} else {
	list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
	if (deleteComment($blogid, $suri['id'], $entryId, '') === false) {
		printMobileErrorPage(_t('댓글을 삭제할 수 없습니다'), _t('관리자가 아닙니다'), "$blogURL/comment/delete/{$suri['id']}");
		exit();
	}
}
list($entries, $paging) = getEntryWithPaging($blogid, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?php echo _t('댓글이 삭제됐습니다');?></h2>
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>
