<?php
define('ROOT', '../../..');
$IV = array(
	'POST' => array(
		'mode' => array( array('delete','edit','commit'), 'default' => null ),
		'password' => array( 'string' , 'default' => ''),
		'oldPassword' => array('string' , 'default' => ''),
		'name' => array('string' , 'default' => ''),
		'comment' => array('string' , 'default' => ''),
		'homepage' => array('string', 'default' => ''),
		'secret' => array(array('on'), 'default' => null)
	)
);
require ROOT . '/lib/include.php';
list($replier) = getCommentAttributes($owner, $suri['id'], 'replier');
if (!empty($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'delete':
			if (!list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry'))
				respondErrorPage(_text('댓글이 존재하지 않습니다.'));
			if (deleteComment($owner, $suri['id'], $entryId, isset($_POST['password']) ? $_POST['password'] : '')) {
				$skin = new Skin($skinSetting['skin']);
				printHtmlHeader();
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('댓글이 삭제되었습니다.')?>");
		var obj = opener.document.getElementById("entry<?php echo $entryId?>Comment");
		obj.innerHTML = "<?php echo str_innerHTML(removeAllTags(getCommentView($entryId, $skin)))?>";
		obj = opener.document.getElementById("recentComments");
		if(obj)
			obj.innerHTML = "<?php echo str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments))?>";
<?php
$commentCount = getCommentCount($owner, $entryId);
$commentCount = ($commentCount > 0) ? $commentCount : '';
list($tempTag, $commentView) = getCommentCountPart($commentCount, $skin);
?>
		try {
			obj = opener.document.getElementById("commentCount<?php echo $entryId?>");
			obj.innerHTML = "<?php echo str_replace('"', '\"', $commentView)?>";
		} catch(e) { }		
		try {
			obj = opener.document.getElementById("commentCountOnRecentEntries<?php echo $entryId?>");
			obj.innerHTML = "<?php echo $commentCount?>";
		} catch(e) { }		
		window.close();
	//]]>
</script>
<?php
				printHtmlFooter();
				exit;
			}
			respondErrorPage(_text('패스워드가 틀렸습니다.'));
		case 'edit':
			$comment = getComment($owner, $suri['id'], isset($_POST['password']) ? $_POST['password'] : '');
			if ($comment === false)
				respondErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			$pageTitle = _text('댓글을 수정합니다');
			require ROOT . '/lib/view/replyEditorView.php';
			exit;
		case 'commit':
			$comment = getComment($owner, $suri['id'], isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
			if ($comment === false)
				respondErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment'])) {
				if (!doesHaveMembership()) {
					$comment['name'] = empty($_POST['name']) ? '' : $_POST['name'];
					$comment['password'] = empty($_POST['password']) ? '' : $_POST['password'];
					$comment['email'] = empty($_POST['email']) || ($_POST['email'] == '') ? '' : $_POST['email'];
					$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : $_POST['homepage'];
					$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
					$comment['comment'] = $_POST['comment'];
					$comment['ip'] = $_SERVER['REMOTE_ADDR'];
				} else {
					$comment['comment'] = $_POST['comment'];
					$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
				}
				$result = updateComment($owner, $comment, isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
				if ($result === 'blocked') {
					printHtmlHeader();
?>
<script type="text/javascript">
	alert("<?php echo _text('귀하는 차단되었으므로 사용하실 수 없습니다.')?>");
	window.close();
</script>
<?php
					printHtmlFooter();
					exit;
				} else if ($result !== false) {
					$skin = new Skin($skinSetting['skin']);
					printHtmlHeader();
?>
<script type="text/javascript">
	//<![CDATA[		
		alert("<?php echo _text('댓글이 수정되었습니다.')?>");
		
		try {
			var obj = opener.document.getElementById("entry<?php echo $comment['entry']?>Comment");
			obj.innerHTML = "<?php echo str_innerHTML(removeAllTags(getCommentView($comment['entry'], $skin)))?>";
			var recentComment = opener.document.getElementById("recentComments");
			if(recentComment)
				recentComment.innerHTML = "<?php echo str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments))?>";
			window.close();
		} catch(e) {
			// alert(e.message);
		}
	//]]>
</script>
<?php
					printHtmlFooter();
					exit;
				}
			}
	}
	respondErrorPage();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo _text('댓글 삭제') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/owner.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path']?>";
			var blogURL = "<?php echo $blogURL?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin']?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
</head>
<body>
	<form name="deleteComment" method="post" action="<?php echo $blogURL?>/comment/delete/<?php echo $suri['id']?>">
		<div id="comment-box">
			<img src="<?php echo $service['path']?>/image/logo_CommentPopup.gif" alt="<?php echo _text('태터툴즈 로고')?>" />	
			
			<div id="command-box">
				<div class="edit-line">
					<input type="radio" id="edit" class="radio" name="mode" value="edit" checked="checked" /> <label for="edit"><?php echo _text('댓글을 수정합니다.')?></label>
				</div>
				<div class="delete-line">			
					<input type="radio" id="delete" class="radio" name="mode" value="delete" />  <label for="delete"><?php echo _text('댓글을 삭제합니다.')?></label>
				</div>
				<div class="password-line">
<?php
if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId()))) {
?>				  
					<label for="password"><?php echo _text('비밀번호')?><span class="divider"> | </span></label><input type="password" id="password" class="text-input" name="password" />
<?php
}
?>
					<input type="button" class="button-input" name="Submit" value="<?php echo _text('다음')?>" onclick="document.deleteComment.submit()" />				
				</div>
			</div>
		</div>
	</form>
</body>
</html>
