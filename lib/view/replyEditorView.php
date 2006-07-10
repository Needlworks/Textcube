<?php 
if (empty($comment['name']) && isset($_COOKIE['guestName']))
	$comment['name'] = $_COOKIE['guestName'];
if ((empty($comment['homepage']) || $comment['homepage'] == 'http://') && isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://')
	$comment['homepage'] = $_COOKIE['guestHomepage'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo $pageTitle ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/owner.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?=$service['path']?>";
			var blogURL = "<?=$blogURL?>";
			var adminSkin = "<?=$service['adminSkin']?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
	<script type="text/javascript">
		//<![CDATA[
			function submitComment() {
				var oForm = document.commentToComment;
				trimAll(oForm);
<?php 
if (!doesHaveMembership()) {
?>
				if (!checkValue(oForm.name, '<?php echo _t('이름을 입력해 주십시오.')?>')) return false;
<?php 
}
?>
				if (!checkValue(oForm.comment, '<?php echo _t('댓글을 입력해 주십시오.')?>')) return false;
				oForm.submit();
			}
		//]]>
	</script>
</head>
<?php 
if (!doesHaveMembership()) {
?>
<body onLoad="document.commentToComment.name.focus()">
<?php 
} else {
?>
<body onload="document.commentToComment.comment.focus()">
<?php 
}
?>
	<form name="commentToComment" method="post" action="<?php echo $suri['url']?>">
		<input type="hidden" name="mode" value="commit" />
		<input type="hidden" name="oldPassword" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''?>" />
		
		<div id="comment-reply-box">
			<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" alt="<?=_t(TATTERTOOLS_NAME.' 로고')?>" />
			
			<div class="title"><span class="text"><?php echo $pageTitle ?></span></div>
	      	<div id="command-box">
<?php 
if (!doesHaveOwnership()) {
	if (!doesHaveMembership()) {
?>
				<dl class="name-line">
					<dt><label for="name"><?php echo _t('이름')?></label></dt>
					<dd><input type="text" id="name" class="text-input" name="name" value="<?php echo htmlspecialchars($comment['name'])?>" /></dd>
				</dl>
				<dl class="password-line">
					<dt><label for="password"><?php echo _t('비밀번호')?></label></dt>
					<dd><input type="password" class="text-input" id="password" name="password" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''?>" /></dd>
				</dl>
<?php 
	}
?>
				<dl class="homepage-line">
					<dt><label for="homepage"><?php echo _t('홈페이지')?></label></dt>
					<dd><input type="text" class="text-input" id="homepage" name="homepage" value="<?php echo (empty($comment['homepage']) ? 'http://' : htmlspecialchars($comment['homepage']))?>" /></dd>
				</dl>
				<dl class="secret-line">
					<dd>
						<input type="checkbox" class="checkbox" id="secret" name="secret"<?php echo ($comment['secret'] ? ' checked="checked"' : false)?> />
						<label for="secret"><?php echo _t('비밀글로 등록')?></label>
					</dd>
				</dl>
<?php 
}
?>			
				<dl class="content-line">
					<dt><label for="comment"><?php echo _t('내용')?></label></dt>
					<dd><textarea id="comment" name="comment" cols="45" rows="9" style="height: <?php echo (!doesHaveOwnership() && !doesHaveOwnership()) ? 150 : 242?>px;"><?php echo htmlspecialchars($comment['comment'])?></textarea></dd>
				</dl>
				
				<div class="button-box">
					<input type="button" class="button-input" name="Submit" value="<?php echo _t('완료')?>" onclick="submitComment()" />
				</div>
			</div>
		</div>
	</form>
</body>
</html>
