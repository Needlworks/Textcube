<?
define('ROOT', '../../..');
require ROOT . '/lib/include.php';
list($replier) = getCommentAttributes($owner, $suri['id'], 'replier');
if (!empty($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'delete':
			if (!list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry'))
				respondErrorPage(_t('댓글이 존재하지 않습니다.'));
			if (deleteComment($owner, $suri['id'], $entryId, isset($_POST['password']) ? $_POST['password'] : '')) {
				$skin = new Skin($skinSetting['skin']);
				printHtmlHeader();
?>
<script type="text/javascript">
//<![CDATA[
	alert("<?=_t('댓글이 삭제되었습니다.')?>");
	var obj = opener.document.getElementById("entry<?=$entryId?>Comment");
	obj.innerHTML = "<?=str_innerHTML(removeAllTags(getCommentView($entryId, $skin)))?>";
	obj = opener.document.getElementById("recentComments");
	if(obj)
		obj.innerHTML = "<?=str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments))?>";
	<?
		$commentCount = getCommentCount($owner, $entryId);
		$commentCount = ($commentCount > 0) ? "($commentCount)" : '';
	?>
	try {
		obj = opener.document.getElementById("commentCount<?=$entryId?>");
		obj.innerHTML = "<?=$commentCount?>";
	} catch(e) { }		
	try {
		obj = opener.document.getElementById("commentCountOnRecentEntries<?=$entryId?>");
		obj.innerHTML = "<?=$commentCount?>";
	} catch(e) { }		
	window.close();
//]]>
</script>
<?
				printHtmlFooter();
				exit;
			}
			respondErrorPage(_t('패스워드가 틀렸습니다.'));
		case 'edit':
			$comment = getComment($owner, $suri['id'], isset($_POST['password']) ? $_POST['password'] : '');
			if ($comment === false)
				respondErrorPage(_t('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			$pageTitle = _t('댓글을 수정합니다');
			require ROOT . '/lib/view/replyEditorView.php';
			exit;
		case 'commit':
			$comment = getComment($owner, $suri['id'], isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
			if ($comment === false)
				respondErrorPage(_t('댓글이 존재하지 않거나 패스워드가 일치하지 않습니다.'));
			if ((doesHaveMembership() || !empty($_POST['name'])) && !empty($_POST['comment'])) {
				$comment['name'] = empty($_POST['name']) ? '' : $_POST['name'];
				$comment['password'] = empty($_POST['password']) ? '' : $_POST['password'];
				$comment['homepage'] = empty($_POST['homepage']) || ($_POST['homepage'] == 'http://') ? '' : $_POST['homepage'];
				$comment['secret'] = empty($_POST['secret']) ? 0 : 1;
				$comment['comment'] = $_POST['comment'];
				$comment['ip'] = $_SERVER['REMOTE_ADDR'];
				$result = updateComment($owner, $comment, isset($_POST['oldPassword']) ? $_POST['oldPassword'] : '');
				if ($result === 'blocked') {
					printHtmlHeader();
?>
<script type="text/javascript">
	alert("<?=_t('귀하는 차단되었으므로 사용할 수 없습니다')?>");
	window.close();
</script>
<?
					printHtmlFooter();
					exit;
				} else if ($result !== false) {
					$skin = new Skin($skinSetting['skin']);
					printHtmlHeader();
?>
<script type="text/javascript">
//<![CDATA[		
	alert("<?=_t('댓글이 수정되었습니다.')?>");
	
	try {
		var obj = opener.document.getElementById("entry<?=$comment['entry']?>Comment");
		obj.innerHTML = "<?=str_innerHTML(removeAllTags(getCommentView($comment['entry'], $skin)))?>";
		var recentComment = opener.document.getElementById("recentComments");
		if(recentComment)
			recentComment.innerHTML = "<?=str_innerHTML(getRecentCommentsView(getRecentComments($owner), $skin->recentComments))?>";
		window.close();
	} catch(e) {
//		alert(e.message);
	}
//]]>
</script>
<?
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
	<title><?php echo _t('댓글 삭제') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=$service['path']?>/style/owner.css" />
</head>
<body>
	<form name="deleteComment" method="post" action="<?=$blogURL?>/comment/delete/<?=$suri['id']?>">
		<div id="comment-box">
			<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" alt="태터툴즈 로고" />	
			
			<div id="command-box">
				<div class="edit-line">
					<input type="radio" id="edit" class="radio" name="mode" value="edit" checked="checked" /> <label for="edit"><span class="text"><?=_t('댓글을 수정합니다.')?></span></label>
				</div>
				<div class="delete-line">			
					<input type="radio" id="delete" class="radio" name="mode" value="delete" />  <label for="delete"><span class="text"><?=_t('댓글을 삭제합니다.')?></span></label>
				</div>
				<div class="password-line">
<?
if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId()))) {
?>				  
					<label for="password"><span class="text"><?=_t('비밀번호')?></span><span class="divider"> | </span></label><input type="password" id="password" class="text-input" name="password" />
<?
}
?>
					<input type="button" class="button-input" name="Submit" value="<?=_t('다음')?>" onclick="document.deleteComment.submit()" />				
				</div>
			</div>
		</div>
	</form>
</body>
</html>
