<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
require ROOT . '/lib/includeForBlog.php';
list($replier) = getCommentAttributes($blogid,$suri['id'],'replier');

if(!Acl::check('group.administrators') && !Acl::check('group.owners')) { // If no administration permission,
	if(!empty($replier)) {	// If replier exists, (member of the blog system)
		if(!Acl::check('group.owners')) { // If not blog owner,
			if(!doesHaveMembership() || $replier != getUserId()) {
				echo "<script> alert('"._t('권한이 없습니다.')."'); window.close(); </script>";
				exit;	
			}
		}
	}
}
if (false) {
	fetchConfigVal();
}
list($replier) = getCommentAttributes($blogid, $suri['id'], 'replier');
if (!empty($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'delete':
			if (!list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry'))
				respondErrorPage(_text('댓글이 존재하지 않습니다.'));
			$result = false;
			if (doesHaveOwnership()) {
				$result = trashComment($blogid, $suri['id'], $entryId, isset($_POST['password']) ? $_POST['password'] : '');
			} else {
				$result = deleteComment($blogid, $suri['id'], $entryId, isset($_POST['password']) ? $_POST['password'] : '');
			}			
			if ($result == true) {
				$skin = new Skin($skinSetting['skin']);
				printHtmlHeader();
				$tempComments = revertTempTags(removeAllTags(getCommentView($entryId, $skin)));
				$tempRecentComments = revertTempTags(getRecentCommentsView(getRecentComments($blogid), $skin->recentComments));
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('댓글이 삭제되었습니다.');?>");
		var obj = opener.document.getElementById("entry<?php echo $entryId;?>Comment");
		obj.innerHTML = "<?php echo str_innerHTML($tempComments);?>";
		obj = opener.document.getElementById("recentComments");
		if(obj)
			obj.innerHTML = "<?php echo str_innerHTML($tempRecentComments);?>";
<?php
$commentCount = getCommentCount($blogid, $entryId);
$commentCount = ($commentCount > 0) ? $commentCount : '';
list($tempTag, $commentView) = getCommentCountPart($commentCount, $skin);
?>
		try {
			obj = opener.document.getElementById("commentCount<?php echo $entryId;?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML($commentView);?>";
		} catch(e) { }		
		try {
			obj = opener.document.getElementById("commentCountOnRecentEntries<?php echo $entryId;?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML(($commentCount > 0) ? '(' . $commentCount . ')' : '');?>";
		} catch(e) { }		
		window.close();
	//]]>
</script>
<?php
				printHtmlFooter();
				exit;
			} else {
				respondErrorPage(_text('패스워드가 일치하지 않습니다.'));
				exit;
			}
			
		case 'edit':
			$comment = getComment($blogid, $suri['id'], isset($_POST['password']) ? $_POST['password'] : '');
			if ($comment === false)
				respondErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			$pageTitle = _text('댓글을 수정합니다');
			require ROOT . '/lib/view/replyEditorView.php';
			exit;
		case 'commit':
			$comment = getComment($blogid, $suri['id'], isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
			if ($comment === false)
				respondErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment'])) {
				if (!doesHaveOwnership()) {
					$comment['name'] = $_POST['name'];
					$comment['password'] = $_POST['password'];
					$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : $_POST['homepage'];
					$comment['ip'] = $_SERVER['REMOTE_ADDR'];
				}
				//$comment['email'] = empty($_POST['email']) || ($_POST['email'] == '') ? '' : $_POST['email'];
				$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
				$comment['comment'] = $_POST['comment'];
				$result = updateComment($blogid, $comment, isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
				if ($result === 'blocked') {
					printHtmlHeader();
?>
<script type="text/javascript">
	alert("<?php echo _text('귀하는 차단되었으므로 사용하실 수 없습니다.');?>");
	window.close();
</script>
<?php
					printHtmlFooter();
					exit;
				} else if ($result !== false) {
					$skin = new Skin($skinSetting['skin']);
					printHtmlHeader();
					$tempComments = revertTempTags(removeAllTags(getCommentView($comment['entry'], $skin)));
					$tempRecentComments = revertTempTags(getRecentCommentsView(getRecentComments($blogid), $skin->recentComments));
?>
<script type="text/javascript">
	//<![CDATA[		
		alert("<?php echo _text('댓글이 수정되었습니다.');?>");
		
		try {
			var obj = opener.document.getElementById("entry<?php echo $comment['entry'];?>Comment");
			obj.innerHTML = "<?php echo str_innerHTML($tempComments);?>";
			var recentComment = opener.document.getElementById("recentComments");
			if(recentComment)
				recentComment.innerHTML = "<?php echo str_innerHTML($tempRecentComments);?>";
			window.close();
			opener.openWindow = '';
		} catch(e) {
			// alert(e.message);
		}
	//]]>
</script>
<?php
					printHtmlFooter();
					exit;
				} else {
					respondErrorPage(_text('수정이 실패하였습니다.'));
				}
			}
	}
	respondErrorPage();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo _text('댓글 삭제') ;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/popup-comment.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path'];?>";
			var blogURL = "<?php echo $blogURL;?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/common2.js"></script>
</head>
<body>
	<form name="deleteComment" method="post" action="<?php echo $blogURL;?>/comment/delete/<?php echo $suri['id'];?>">
		<div id="comment-box">
			<img src="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/image/img_comment_popup_logo.gif" alt="<?php echo _text('텍스트큐브 로고');?>" />	
			
			<div id="command-box">
				<div class="edit-line">
					<input type="radio" id="edit" class="radio" name="mode" value="edit" checked="checked" /><label for="edit"><?php echo _text('댓글을 수정합니다.');?></label>
				</div>
				<div class="delete-line">			
					<input type="radio" id="delete" class="radio" name="mode" value="delete" /><label for="delete"><?php echo _text('댓글을 삭제합니다.');?></label>
				</div>
				<div class="password-line">
<?php
if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId()))) {
?>				  
					<label for="password"><?php echo _text('비밀번호');?><span class="divider"> | </span></label><input type="password" id="password" class="input-text" name="password" />
<?php
}
?>
					<input type="button" class="input-button" name="Submit" value="<?php echo _text('다음');?>" onclick="document.deleteComment.submit()" />				
				</div>
			</div>
		</div>
	</form>
</body>
</html>
