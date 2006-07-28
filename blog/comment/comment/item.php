<?
define('ROOT', '../../..');
require ROOT . '/lib/include.php';
if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment']) && !empty($_POST['mode']) && ($_POST['mode'] == 'commit')) {
	$IV = array(
		'POST' => array(
			'name' => array('string', 'default' => ''),
			'password' => array('string', 'default' => ''),
			'secret' => array(array('on'), 'mandatory' => false),
			'homepage' => array('string', 'default' => 'http://'),
			'comment' => array('string', 'default' => '')
		)
	);
	if(!Validator::validate($IV))
		respondNotFoundPage();
	if (!empty($_POST['name']))
		setcookie('guestName', $_POST['name'], time() + 2592000, "$blogURL/");
	if (!empty($_POST['homepage']) && ($_POST['homepage'] != 'http://')) {
		if (strpos($_POST['homepage'], 'http://') === 0)
			setcookie('guestHomepage', $_POST['homepage'], time() + 2592000, "$blogURL/");
		else
			setcookie('guestHomepage', 'http://' . $_POST['homepage'], time() + 2592000, "$blogURL/");
	}
	$comment = array();
	list($comment['entry']) = getCommentAttributes($owner, $suri['id'], 'entry');
	if (count($comment) == 0)
		respondErrorPage(_t('댓글이 존재하지 않습니다.'));
	$comment['parent'] = $suri['id'];
	$comment['name'] = empty($_POST['name']) ? '' : $_POST['name'];
	$comment['password'] = empty($_POST['password']) ? '' : $_POST['password'];
	$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : $_POST['homepage'];
	$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
	$comment['comment'] = $_POST['comment'];
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	if (addComment($owner, $comment) !== false) {
		$skin = new Skin($skinSetting['skin']);
		printHtmlHeader();
?>
<script type="text/javascript">	
	alert("<?=_t('댓글이 등록되었습니다.')?>");
	
<?
		notifyComment();
?>
	var obj = opener.document.getElementById("entry<?=$comment['entry']?>Comment");
	obj.innerHTML = "<?=str_innerHTML(removeAllTags(getCommentView($comment['entry'], $skin)))?>";
	try {
	obj = opener.document.getElementById("recentComments");
	obj.innerHTML = "<?=str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments))?>";
	} catch(e) { }
	try {
	<?
		$commentCount = getCommentCount($owner, $comment['entry']);
		$commentCount = ($commentCount > 0) ? "($commentCount)" : '';
	?>
	obj = opener.document.getElementById("commentCount<?=$comment['entry']?>");
	obj.innerHTML = "<?=$commentCount?>";
	} catch(e) { }
	try {
	obj = opener.document.getElementById("commentCountOnRecentEntries<?=$comment['entry']?>");
	obj.innerHTML = "<?=$commentCount?>";
	} catch(e) { }
	window.close();
</script>
<?
		printHtmlFooter();
		exit;
	}
}
$pageTitle = _t('댓글에 댓글 달기');
$comment = array('name' => '', 'password' => '', 'homepage' => 'http://', 'secret' => 0, 'comment' => '');
require ROOT . '/lib/view/replyEditorView.php';
?>