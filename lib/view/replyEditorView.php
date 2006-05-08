<?php 
if (empty($comment['name']) && isset($_COOKIE['guestName']))
	$comment['name'] = $_COOKIE['guestName'];
if ((empty($comment['homepage']) || $comment['homepage'] == 'http://') && isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://')
	$comment['homepage'] = $_COOKIE['guestHomepage'];
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title><?php echo $pageTitle?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $service['path']?>/style/owner.css" />
<script type="text/javascript">
var servicePath = "<?php echo $service['path']?>"; var blogURL = "<?php echo $blogURL?>";
</script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
<script type="text/javascript">
	function submitComment() {
		var oForm = document.commentToComment;
		trimAll(oForm);
<?php 
if (!doesHaveMembership()) {
?>
		if (!checkValue(oForm.name, '<?php echo _t('이름을 입력해 주세요')?>')) return false;
<?php 
}
?>
		if (!checkValue(oForm.comment, '<?php echo _t('댓글을 입력해 주세요')?>')) return false;
		oForm.submit();
	}
</script>
</head>
<?php 
if (!doesHaveMembership()) {
?>
<body onLoad="document.commentToComment.name.focus()" style="margin:0; padding:0" bgcolor="#ffffff">
<?php 
} else {
?>
<body onload="document.commentToComment.comment.focus()" style="margin:0; padding:0" bgcolor="#ffffff">
<?php 
}
?>
<form name="commentToComment" method="post" action="<?php echo $suri['url']?>">
  <input type="hidden" name="mode" value="commit">
  <input type="hidden" name="oldPassword" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''?>">
<table width="450" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
  <tr>
    <td bgcolor="#6989AC"></td>
  </tr>
  <tr>
    <td bgcolor="#9DCAFB" height="3"></td>
  </tr>
  <tr>
    <td style="padding:10px;">
	
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td style="color:#5681B0; font-weight:bold; font-size:13px;">
		
		<img src="<?php echo $service['path']?>/image/icon_PopupTitle.gif" width="15" height="15" hspace="3" vspace="3" align="absmiddle" />		        <?php echo $pageTitle?></td>
        <td align="right" style="padding-bottom:3px;">
		
<?php 
?>		
		<img src="<?php echo $service['path']?>/image/logo_CommentPopup.gif" />

		
<?php 
?>					
		</td>
      </tr>
    </table>
	
	
	
	
      <table width="100%" height="306" border="0" cellspacing="1" bgcolor="#9DCAFB">
        <tr>
          <td bgcolor="#E0EFFF" style="padding:10px;">
		  
<?php 
if (!doesHaveOwnership()) {
	if (!doesHaveMembership()) {
?>		  
		  <table border="0" cellspacing="0" cellpadding="1">
            <tr>
              <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?php echo _t('이름')?> : </td>
              <td style="padding:1px;"><input value="<?php echo htmlspecialchars($comment['name'])?>" tabindex="1" type="text" name="name" style="border: 1px solid #9DCAFB; height:18px; width:160px;" /></td>
            </tr>
          </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?php echo _t('비밀번호')?> : </td>
                <td style="padding:1px;"><input value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''?>" tabindex="2" type="password" name="password" style="border: 1px solid #9DCAFB; height:18px; width:160px;" /></td>
              </tr>
            </table>
<?php 
	}
?>

			
			
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?php echo htmlspecialchars(_t('홈페이지'))?> : </td>
                <td style="padding:1px;"><input value="<?php echo (empty($comment['homepage']) ? 'http://' : htmlspecialchars($comment['homepage']))?>" tabindex="3" type="text" name="homepage" style="border: 1px solid #9DCAFB; height:18px; width:330px;" /></td>
              </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; padding:1px;">&nbsp;</td>
                <td style="color:#5490D1; font-family:Dotum; font-size:11px; letter-spacing:-1px; padding:1px;"><input type="checkbox" name="secret" id="secret"<?php echo ($comment['secret'] ? ' checked' : false)?> tabindex="4" />
                  <label for="secret"><?php echo _t('비밀글로 등록')?></label> </td>
              </tr>
            </table> 
<?php 
}
?>			
			
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td valign="top" style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding-top:5px; padding:1px;"><?php echo _t('내용')?> : </td>
                <td style="padding:1px;">
        				<textarea name="comment" style="border: 1px solid #9DCAFB; height:<?php echo (!doesHaveOwnership() && !doesHaveOwnership()) ? 170 : 230?>px; width:350px;"><?php echo htmlspecialchars($comment['comment'])?></textarea>
				</td>
              </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; padding:1px;">&nbsp;</td>
                <td style="padding:1px;"><input onClick="javascript:submitComment()" type="button" name="Submit" value="<?php echo _t('완료')?>"  style="	border: 1px solid #6297D1; background-color:#83AFE0; color:#fff; width:180px; height:20px; font-size:11px; font-family:tahoma; font-weight:bold;" /></td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>
 </form>
</body>
</html>
