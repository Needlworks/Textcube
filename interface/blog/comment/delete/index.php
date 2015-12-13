<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_ADMINPANEL__',true);

$IV = array(
	'POST' => array(
		'mode' => array( array('delete','edit','commit'), 'default' => null ),
		'password' => array( 'string' , 'default' => ''),
		'oldPassword' => array('string' , 'default' => ''),
		'name' => array('string' , 'default' => ''),
		'comment' => array('string' , 'default' => ''),
		'homepage' => array('string', 'default' => ''),
		'openidedit' => array('string', 'default' => ''),
		'secret' => array(array('on'), 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';

$context = Model_Context::getInstance();
$blogid = getBlogId();

list($replier) = getCommentAttributes($blogid,$context->getProperty('suri.id'),'replier');

$pool = DBModel::getInstance();
$pool->init("Comments");
$pool->setQualifier("blogid","eq",$blogid);
$pool->setQualifier("id","eq",$context->getProperty('suri.id'));
$comment = $pool->getRow();

$openid_identity = Acl::getIdentity('openid');

if(!Acl::check('group.administrators') && !Acl::check('group.owners')) { // If no administration permission,
	if(!empty($replier)) {	// If replier exists, (member of the blog system)
		if(!Acl::check('group.owners')) { // If not blog owner,
			if(!Acl::check('group.editors') && $replier != getUserId()) {
				echo "<script type=\"text/javascript\">//<![CDATA[".CRLF
					."alert('"._t('권한이 없습니다.')."'); window.close(); //]]></script>";
			}
		}
	}
}
list($replier) = getCommentAttributes($blogid, $context->getProperty('suri.id'), 'replier');
if (!empty($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'delete':
			if (!list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry'))
				Respond::ErrorPage(_text('댓글이 존재하지 않습니다.'));
			$result = false;
			if (doesHaveOwnership()) {
				$result = trashComment($blogid, $context->getProperty('suri.id'), $entryId, isset($_POST['password']) ? $_POST['password'] : '');
			} else {
				$result = deleteComment($blogid, $context->getProperty('suri.id'), $entryId, isset($_POST['password']) ? $_POST['password'] : '');
			}			
			if ($result == true) {
				$skin = new Skin($context->getProperty('skin.skin'));
				$entry = array();
				$entry['id'] = $entryId;
				$entry['slogan'] = getSloganById($blogid, $entry['id']);
				printHtmlHeader();
				$tempComments = revertTempTags(removeAllTags(getCommentView($entry, $skin)));
				$tempRecentComments = revertTempTags(getRecentCommentsView(getRecentComments($blogid), $skin->recentComment, $skin->recentCommentItem));
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('댓글이 삭제되었습니다.');?>");
		if (opener == null) {
			loader = parent;
		} else {
			loader = opener;
		}
		var obj = loader.document.getElementById("entry<?php echo $entryId;?>Comment");
		obj.innerHTML = "<?php echo str_innerHTML($tempComments);?>";
		obj = loader.document.getElementById("recentComments");
		if(obj)
			obj.innerHTML = "<?php echo str_innerHTML($tempRecentComments);?>";
<?php
$commentCount = getCommentCount($blogid, $entryId);
$commentCount = ($commentCount > 0) ? $commentCount : '';
list($tempTag, $commentView) = getCommentCountPart($commentCount, $skin);
?>
		try {
			obj = loader.document.getElementById("commentCount<?php echo $entryId;?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML($commentView);?>";
		} catch(e) { }
		try {
			obj = loader.document.getElementById("commentCountOnRecentEntries<?php echo $entryId;?>");
			if (obj != null) obj.innerHTML = "<?php echo str_innerHTML(($commentCount > 0) ? '(' . $commentCount . ')' : '');?>";
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
			} else {
				Respond::ErrorPage(_text('패스워드가 일치하지 않습니다.'));
				exit;
			}

		case 'edit':
			if( !empty( $_POST['openidedit'] ) ) {
				//$comment is feched top of this script;
				if( $openid_identity != $comment['openid'] ) {
					$comment = false;
				}
			} else {
				$comment = getComment($blogid, $context->getProperty('suri.id'), isset($_POST['password']) ? $_POST['password'] : '');
			}
			if ($comment === false)
				Respond::ErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'),null,null);
			$pageTitle = _text('댓글을 수정합니다');
			$viewMode = 'edit';
			require ROOT . '/library/view/replyEditorView.php';
			exit;
		case 'commit':
			if( !empty( $_POST['openidedit'] ) ) {
				//$comment is feched top of this script;
				if( $openid_identity != $comment['openid'] ) {
					$comment = false;
				}
			} else {
				$comment = getComment($blogid, $context->getProperty('suri.id'), isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
			}
			if ($comment === false)
				Respond::ErrorPage(_text('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'),null,null);
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
	//<![CDATA[
		alert("<?php echo _text('귀하는 차단되었으므로 사용하실 수 없습니다.');?>");
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
				} else if ($result !== false) {
					$skin = new Skin($context->getProperty('skin.skin'));
					$suri['page'] = getGuestbookPageById($blogid, $context->getProperty('suri.id'));
					$entry = array();
					$entry['id'] = $comment['entry'];
					$entry['slogan'] = getSloganById($blogid, $entry['id']);
					if( Acl::getIdentity( 'openid' ) ) {
						OpenIDConsumer::updateUserInfo( $comment['name'], $comment['homepage'] );
					}
					printHtmlHeader();
					$tempComments = revertTempTags(removeAllTags(getCommentView($entry, $skin)));
					$tempRecentComments = revertTempTags(getRecentCommentsView(getRecentComments($blogid), null, $skin->recentCommentItem));
?>
<script type="text/javascript">
	//<![CDATA[
		alert("<?php echo _text('댓글이 수정되었습니다.');?>");
		if (opener == null) {
			loader = parent;
		} else {
			loader = opener;
		}
		try {
			var obj = loader.document.getElementById("entry<?php echo $entry['id'];?>Comment");
			if (obj != null) {
				obj.innerHTML = "<?php echo str_innerHTML($tempComments);?>";
				var recentComment = loader.document.getElementById("recentComments");
				if(recentComment)
					recentComment.innerHTML = "<?php echo str_innerHTML($tempRecentComments);?>";
			} else {
				var listObj = loader.document.getElementById('list-form');
				if(listObj != null) loader.document.getElementById('list-form').submit();
			}
			if (opener == null) {
				parent.tcDialog.close();
			} else {
				window.close();
				opener.openWindow = '';
			}
		} catch(e) {
			// alert(e.message);
		}
	//]]>
</script>
<?php
					printHtmlFooter();
					exit;
				} else {
					Respond::ErrorPage(_text('수정이 실패하였습니다.'));
				}
			}
	}
	Respond::ErrorPage();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo _text('댓글 삭제') ;?></title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path') . $context->getProperty('panel.skin');?>/popup-comment.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo i$context->getProperty('service.path');?>";
			var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
			var adminSkin = "<?php echo $context->getProperty('panel.skin');?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/common3.min.js"></script>
</head>
<body>
	<form name="deleteComment" method="post" action="<?php echo $context->getProperty('uri.blog');?>/comment/delete/<?php echo $context->getProperty('suri.id');?>">
		<div id="comment-box">
			<img src="<?php echo $context->getProperty('service.path') . $context->getProperty('admin.skin');?>/image/img_comment_popup_logo.gif" alt="<?php echo _text('텍스트큐브 로고');?>" />	
			<a onclick="closeDialog();" href="#" class="close-button"><span>X</span></a>
			<div id="command-box">
<?php
	$render = true;
	if( !empty($comment['openid']) && !Acl::check('group.administrators') ) { ?>
<?php		if( !$openid_identity ) { $render = false;?>
				<div class="edit-line">
					<label><?php echo _text('권한이 없습니다.') ?></label>
				</div>
				<div class="password-line">
					<input type="button" class="input-button" name="Submit" value="<?php echo _text('닫기');?>" onclick="window.close()" />
				</div>
<?php		} else if( $openid_identity != $comment['openid']) { $render = false;?>
				<div class="edit-line">
					<label><?php echo _text('로그인된 오픈아이디의 권한으로는 수정/삭제가 불가능합니다.') ?></label>
				</div>
				<div class="password-line">
					<input type="button" class="input-button" name="Submit" value="<?php echo _text('닫기');?>" onclick="window.close()" />
				</div>
<?php 		} ?>
<?php }
	  if ($render) { ?>
				<div class="edit-line">
					<input type="radio" id="edit" class="radio" name="mode" value="edit" checked="checked" /><label for="edit"><?php echo _text('댓글을 수정합니다.');?></label>
				</div>
				<div class="delete-line">
					<input type="radio" id="delete" class="radio" name="mode" value="delete" /><label for="delete"><?php echo _text('댓글을 삭제합니다.');?></label>
				</div>
				<div class="password-line">
<?php 		if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId())) ) {
				if( !$openid_identity || $openid_identity != $comment['openid'] ) { ?>
					<label for="password"><?php echo _text('비밀번호');?><span class="divider"> | </span></label><input type="password" id="password" class="input-text" name="password" />
<?php 			} else { ?>
					<input name="openidedit" type="hidden" value="1" />
<?php 			} ?>
<?php		} ?>
					<input type="button" class="input-button" name="Submit" value="<?php echo _text('다음');?>" onclick="document.deleteComment.submit()" />
				</div>
<?php	} ?>
			</div>
		</div>
	</form>
</body>
</html>
