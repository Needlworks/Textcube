<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_ADMINPANEL__',true);
require ROOT . '/library/preprocessor.php';
importlib('model.blog.comment');
$IV = array(
	'POST' => array(
		'name' => array('string', 'default' => ''),
		'comment' => array('string' , 'default' => ''),
		'mode' => array(array('commit') , 'default' => ''),
		'homepage' => array('string', 'default' => ''),
		'password' => array('string' , 'default' => ''),
		'secret' => array(array('on'), 'default' => null)
	)
);
$context = Model_Context::getInstance();

$customIV = fireEvent('ManipulateIVRules',$IV,$context->getProperty('uri.interfaceRoute'));
Validator::addRule($customIV);
if(!Validator::isValid())
	Respond::PrintResult(array('error' => 1, 'description' => 'Illegal parameters'));
requireStrictRoute();

if (!Setting::getBlogSettingGlobal('acceptComments',0) && !doesHaveOwnership()) {
	Respond::PrintResult(array('error' => 0, 'commentBlock' => '', 'recentCommentBlock' => ''));
	exit;
}
$pool = DBModel::getInstance();
if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment']) && !empty($_POST['mode']) && ($_POST['mode'] == 'commit')) {
	if (!empty($_POST['name']))
		setcookie('guestName', $_POST['name'], time() + 2592000, $context->getProperty('uri.blog')."/");
	if (!empty($_POST['homepage']) && ($_POST['homepage'] != 'http://')) {
		if (strpos($_POST['homepage'], 'http://') === 0)
			setcookie('guestHomepage', $_POST['homepage'], time() + 2592000, $context->getProperty('uri.blog')."/");
		else
			setcookie('guestHomepage', 'http://' . $_POST['homepage'], time() + 2592000, $context->getProperty('uri.blog')."/");
	}
	$comment = array();
	list($comment['entry']) = getCommentAttributes($blogid, $suri['id'], 'entry');
	if (count($comment) == 0)
		Respond::ErrorPage(_text('댓글이 존재하지 않습니다.'));
	$comment['parent'] = $suri['id'];
	$comment['name'] = empty($_POST['name']) ? '' : trim($_POST['name']);
	$comment['password'] = empty($_POST['password']) ? '' : $_POST['password'];
	$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : trim($_POST['homepage']);
	$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
	$comment['comment'] = trim($_POST['comment']);
	$comment['ip'] = $_SERVER['REMOTE_ADDR'];
	if (!doesHaveMembership() && !doesHaveOwnership() && $comment['name'] == '') {
	?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('이름을 입력해 주십시오.');?>");
	//]]>
</script>
<?php
	} else if ($comment['comment'] == '') {
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('본문을 입력해 주십시오.');?>");
	//]]>
</script>
<?php
	} else if (addComment($blogid, $comment) !== false) {
		if(!$comment['secret']) {
            $pool->init("Entries");
            $pool->setQualifier("blogid","eq",$blogid);
            $pool->setQualifier("id","eq",$comment['entry']);
            $pool->setQualifier("draft","eq",0);
            $pool->setQualifier("visibility","eq",3);
            $pool->setQualifier("acceptcomment","eq",1);
            if($row = $pool->getRow()) {
				sendCommentPing($comment['entry'], $context->getProperty('uri.default')."/".($context->getProperty('blog.useSloganOnPost') ? "entry/{$row['slogan']}": $comment['entry']), !doesHaveMembership() ? $comment['name'] : User::getName(), !doesHaveMembership() ? $comment['homepage'] : User::getHomepage());
            }
		}
		$skin = new Skin($context->getProperty('skin.skin'));
		printHtmlHeader();
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('댓글이 등록되었습니다.');?>");
<?php
			notifyComment();
			$entry = array();
			$entry['id'] = $comment['entry'];
			$entry['slogan'] = getSloganById($blogid, $entry['id']);
			$tempComments = revertTempTags(removeAllTags(getCommentView($entry, $skin)));
			$tempRecentComments = revertTempTags(getRecentCommentsView(getRecentComments($blogid), null, $skin->recentCommentItem));
?>
		if (opener == null) {
			loader = parent;
		} else {
			loader = opener;
		}
		try {
			var obj = loader.document.getElementById("entry<?php echo $comment['entry'];?>Comment");
			obj.innerHTML = "<?php echo str_innerHTML($tempComments);?>";
		} catch(e) { }
		try {
			obj = loader.document.getElementById("recentComments");
			obj.innerHTML = "<?php echo str_innerHTML($tempRecentComments);?>";
		} catch(e) { }
		try {
<?php
			$commentCount = getCommentCount($blogid, $comment['entry']);
			list($tempTag, $commentView) = getCommentCountPart($commentCount, $skin);
			$commentCount = ($commentCount > 0) ? "($commentCount)" : '';
?>
			obj = loader.document.getElementById("commentCount<?php echo $comment['entry'];?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML($commentView);?>";
		} catch(e) { }
		try {
			obj = loader.document.getElementById("commentCountOnRecentEntries<?php echo $comment['entry'];?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML($commentCount);?>";
		} catch(e) { }
		try {
			obj = loader.document.getElementById('list-form');
			if(obj != null) loader.document.getElementById('list-form').submit();
		} catch(e) { }
		if (opener == null) {
			parent.tcDialog.close();
		} else {
			window.close();
		}
	//]]>
</script>
<?php
		printHtmlFooter();
		exit;
	}
}
$pageTitle = _text('댓글에 댓글 달기');
$comment = array('name' => '', 'password' => '', 'homepage' => 'http://', 'secret' => 0, 'comment' => '');
$viewMode = 'comment';
require ROOT . '/library/view/replyEditorView.php';
?>
