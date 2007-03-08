<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'replyId' => array('id'),
		'password' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForBlog.php';
requireStrictRoute();
list($entryId) = getCommentAttributes($owner, $_POST['replyId'], 'entry');
if (deleteComment($owner, $_POST['replyId'], $entryId, isset($_POST['password']) ? $_POST['password'] : '') === false) {
	printMobileErrorPage(_text('답글을 삭제할 수 없습니다.'), _text('비밀번호가 일치하지 않습니다.'), "$blogURL/comment/delete/{$_POST['replyId']}");
	exit();
}
list($entries, $paging) = getEntryWithPaging($owner, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?php echo _text('답글이 삭제됐습니다.');?></h2>
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>