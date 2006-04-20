<?
if (empty($comment['name']) && isset($_COOKIE['guestName']))
	$comment['name'] = $_COOKIE['guestName'];
if ((empty($comment['homepage']) || $comment['homepage'] == 'http://') && isset($_COOKIE['guestHomepage']) && $_COOKIE['guestHomepage'] != 'http://')
	$comment['homepage'] = $_COOKIE['guestHomepage'];
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title><?=$pageTitle?></title>
<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
<script type="text/javascript">
	function submitComment() {
		var oForm = document.commentToComment;
		trimAll(oForm);
<?
if (!doesHaveMembership()) {
?>
		if (!checkValue(oForm.name, '<?=_t('이름을 입력해 주세요')?>')) return false;
<?
}
?>
		if (!checkValue(oForm.comment, '<?=_t('댓글을 입력해 주세요')?>')) return false;
		oForm.submit();
	}
</script>
</head>
<?
if (!doesHaveMembership()) {
?>
<body onLoad="document.commentToComment.name.focus()" style="margin:0; padding:0" bgcolor="#ffffff">
<?
} else {
?>
<body onload="document.commentToComment.comment.focus()" style="margin:0; padding:0" bgcolor="#ffffff">
<?
}
?>
<form name="commentToComment" method="post" action="<?=$suri['url']?>">
  <input type="hidden" name="mode" value="commit">
  <input type="hidden" name="oldPassword" value="<?=isset($_POST['password']) ? $_POST['password'] : ''?>">
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
		
		<img src="<?=$service['path']?>/image/icon_PopupTitle.gif" width="15" height="15" hspace="3" vspace="3" align="absmiddle" />		        <?=$pageTitle?></td>
        <td align="right" style="padding-bottom:3px;">
		
<?
?>		
		<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" />

		
<?
?>					
		</td>
      </tr>
    </table>
	
	
	
	
      <table width="100%" height="306" border="0" cellspacing="1" bgcolor="#9DCAFB">
        <tr>
          <td bgcolor="#E0EFFF" style="padding:10px;">
		  
<?
if (!doesHaveOwnership()) {
	if (!doesHaveMembership()) {
?>		  
		  <table border="0" cellspacing="0" cellpadding="1">
            <tr>
              <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?=_t('이름')?> : </td>
              <td style="padding:1px;"><input value="<?=htmlspecialchars($comment['name'])?>" tabindex="1" type="text" name="name" style="border: 1px solid #9DCAFB; height:18px; width:160px;" /></td>
            </tr>
          </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?=_t('비밀번호')?> : </td>
                <td style="padding:1px;"><input value="<?=isset($_POST['password']) ? $_POST['password'] : ''?>" tabindex="2" type="password" name="password" style="border: 1px solid #9DCAFB; height:18px; width:160px;" /></td>
              </tr>
            </table>
<?
	}
?>

			
			
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding:1px;"><?=htmlspecialchars(_t('홈페이지'))?> : </td>
                <td style="padding:1px;"><input value="<?=(empty($comment['homepage']) ? 'http://' : htmlspecialchars($comment['homepage']))?>" tabindex="3" type="text" name="homepage" style="border: 1px solid #9DCAFB; height:18px; width:330px;" /></td>
              </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; padding:1px;">&nbsp;</td>
                <td style="color:#5490D1; font-family:Dotum; font-size:11px; letter-spacing:-1px; padding:1px;"><input type="checkbox" name="secret" id="secret"<?=($comment['secret'] ? ' checked' : false)?> tabindex="4" />
                  <label for="secret"><?=_t('비밀글로 등록')?></label> </td>
              </tr>
            </table> 
<?
}
?>			
			
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td valign="top" style="width:65px; text-align:right; color:#2A64A3; font-size:12px; padding-top:5px; padding:1px;"><?=_t('내용')?> : </td>
                <td style="padding:1px;">
        				<textarea name="comment" style="border: 1px solid #9DCAFB; height:<?=(!doesHaveOwnership() && !doesHaveOwnership()) ? 170 : 230?>px; width:350px;"><?=htmlspecialchars($comment['comment'])?></textarea>
				</td>
              </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td style="width:65px; text-align:right; color:#2A64A3; padding:1px;">&nbsp;</td>
                <td style="padding:1px;"><input onClick="javascript:submitComment()" type="button" name="Submit" value="<?=_t('완료')?>"  style="	border: 1px solid #6297D1; background-color:#83AFE0; color:#fff; width:180px; height:20px; font-size:11px; font-family:tahoma; font-weight:bold;" /></td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>
 </form>
</body>
</html>
