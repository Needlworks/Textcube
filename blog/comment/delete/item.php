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


<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
</head>
<body style="margin:0; padding:0">
<form name="deleteComment" method="post" action="<?=$blogURL?>/comment/delete/<?=$suri['id']?>">


<table width="450" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td bgcolor="#6989AC"></td>
  </tr>
  <tr>
    <td bgcolor="#9DCAFB" height="3"></td>
  </tr>
  <tr>
    <td style="padding:10px;"><table width="100%" height="80" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" valign="bottom" style="color:#5681B0; font-weight:bold; font-size:13px; padding-bottom:10px;">
		
<?
?>		
		<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" />

		
<?
?>	

		</td>
        </tr>
    </table>
      <table width="100%" height="180" border="0" cellpadding="20" cellspacing="1" bgcolor="#9DCAFB">
        <tr>
          <td align="center" bgcolor="#E0EFFF"><table height="40" border="0" cellpadding="1" cellspacing="0">
            <tr>
			
<?
?>			
              <td style="text-align:right; color:#2A64A3; font-size:12px; padding:15px;">
                <input type="radio" name="mode" id="edit" value="edit" checked />
                <label for="edit"><?=_t('댓글을 수정합니다')?></label> </td>
				
<?
?>				
              <td style="padding:15px;"><span style="text-align:right; color:#2A64A3; font-size:12px;">
                <input type="radio" name="mode" id="delete" value="delete"> 
                <label for="delete"><?=_t('댓글을 삭제합니다')?></label>
              </span></td>
			  
			  
			  
            </tr>
          </table>
            <br />
            <table>
              <tr>
			  
			  
<?
if (!doesHaveOwnership() && (!doesHaveMembership() || ($replier != getUserId()))) {
?>				  
                <td id="password_td" style="text-align:right; color:#2A64A3; font-size:12px; padding:3px;">
				<label for="password"><?=_t('비밀번호')?>:</label>
                <input type="password" name="password" id="password" style="border: 1px solid #9DCAFB; height:22px; width:150px;" /></td>
                <?
}
?>				
				
				
                <td style="padding:3px;">
				<input onClick="document.deleteComment.submit()" type="button" name="Submit" value="<?=_t('다음')?>" style="border: 1px solid #6297D1; background-color:#83AFE0; color:#fff; width:70px; height:20px; font-size:11px; font-family:tahoma; font-weight:bold;" /></td>
              </tr>
            </table>
          </td>
        </tr>
      </table></td>
  </tr>
</table>
</form>
</body>
</html>
