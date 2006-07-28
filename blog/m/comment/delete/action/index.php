<?
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'replyId' => array('id'),
		'password' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/include.php';
list($entryId) = getCommentAttributes($owner, $_POST['replyId'], 'entry');
if (deleteComment($owner, $_POST['replyId'], $entryId, isset($_POST['password']) ? $_POST['password'] : '') === false) {
	printMobileErrorPage(_t('답글을 삭제할 수 없습니다'), _t('비밀번호가 틀렸습니다'), "$blogURL/comment/delete/{$_POST['replyId']}");
	exit();
}
list($entries, $paging) = getEntryWithPaging($owner, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?=_t('답글이 삭제됐습니다')?></h2>
</div>
<?
printMobileNavigation($entry);
printMobileHtmlFooter();
?>