<?php
define('ROOT', '../../..');
$IV = array(
	'POST' => array(
		'name' => array('string', 'default' => ''),
		'comment' => array('string' , 'default' => ''),
		'mode' => array(array('commit') , 'default' => ''),
		'homepage' => array('string', 'default' => ''),
		'email' => array('string', 'default' => ''),
		'password' => array('string' , 'default' => ''),
		'secret' => array(array('on'), 'default' => null)
	)
);
require ROOT . '/lib/include.php';
if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment']) && !empty($_POST['mode']) && ($_POST['mode'] == 'commit')) {
	if (!empty($_POST['name']))
		setcookie('guestName', $_POST['name'], time() + 2592000, "$blogURL/");
	if (!empty($_POST['email']))
		setcookie('guestEmail', $_POST['email'], time() + 2592000, "$blogURL/");
	if (!empty($_POST['homepage']) && ($_POST['homepage'] != 'http://')) {
		if (strpos($_POST['homepage'], 'http://') === 0)
			setcookie('guestHomepage', $_POST['homepage'], time() + 2592000, "$blogURL/");
		else
			setcookie('guestHomepage', 'http://' . $_POST['homepage'], time() + 2592000, "$blogURL/");
	}
	$comment = array();
	list($comment['entry']) = getCommentAttributes($owner, $suri['id'], 'entry');
	if (count($comment) == 0)
		respondErrorPage(_text('댓글이 존재하지 않습니다.'));
	$comment['parent'] = $suri['id'];
	$comment['name'] = empty($_POST['name']) ? '' : $_POST['name'];
	$comment['password'] = empty($_POST['password']) ? '' : $_POST['password'];
	$comment['email'] = empty($_POST['email']) || ($_POST['email'] == '') ? '' : $_POST['email'];
	$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : $_POST['homepage'];
	$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
	$comment['comment'] = $_POST['comment'];
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	if (addComment($owner, $comment) !== false) {
		$skin = new Skin($skinSetting['skin']);
		printHtmlHeader();
?>
<script type="text/javascript">	
	alert("<?php echo _text('댓글이 등록되었습니다.');?>");
	
<?php
		notifyComment();
?>
	var obj = opener.document.getElementById("entry<?php echo $comment['entry'];?>Comment");
	obj.innerHTML = "<?php echo str_innerHTML(removeAllTags(getCommentView($comment['entry'], $skin)));?>";
	try {
	obj = opener.document.getElementById("recentComments");
	obj.innerHTML = "<?php echo str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments));?>";
	} catch(e) { }
	try {
<?php
		$commentCount = getCommentCount($owner, $comment['entry']);
		$commentCount = ($commentCount > 0) ? "$commentCount" : '';
		list($tempTag, $commentView) = getCommentCountPart($commentCount, $skin);
?>
	obj = opener.document.getElementById("commentCount<?php echo $comment['entry'];?>");
	obj.innerHTML = "<?php echo str_replace('"', '\"', $commentView);?>";
	} catch(e) { }
	try {
	obj = opener.document.getElementById("commentCountOnRecentEntries<?php echo $comment['entry'];?>");
	obj.innerHTML = "<?php echo $commentCount;?>";
	} catch(e) { }
	window.close();
</script>
<?php
		printHtmlFooter();
		exit;
	}
}
$pageTitle = _text('댓글에 댓글 달기');
$comment = array('name' => '', 'password' => '', 'email' => '', 'homepage' => 'http://', 'secret' => 0, 'comment' => '');
require ROOT . '/lib/view/replyEditorView.php';
?>
