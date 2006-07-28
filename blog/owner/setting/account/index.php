<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'password' => array('any' ,'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu51.php';
?>
<script>
//<![CDATA[
	function checkMail(str)
	{
		try {
			var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if (filter.test(str)) return true;
			else return false;
		} catch(e) {
			return false;
		}
	}
	
function Trim(sInString) {
  sInString = sInString.replace( /^\s+/g, "" );// strip leading
  return sInString.replace( /\s+$/g, "" );// strip trailing
}

<?
?>	
	function save() {
		try {
			var email = document.getElementById('email');	
			var nickname = document.getElementById('nickname');	
			
			if(!checkMail(email.value)) {
				alert("<?=_t('이메일이 바르지 않습니다')?>");
				email.select();
				return false;
			}
			if(nickname.value == '') {
				alert("<?=_t('닉네임을 입력해 주십시오')?>");
				nick.select();
				return false;
			}
			var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/profile/");
			request.onSuccess = function() {
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('저장하지 못했습니다')?>");
			}
			request.send("&email=" + encodeURIComponent(email.value) + "&nickname=" + encodeURIComponent(nickname.value));
		} catch(e) {
			
		}
	}	
<?
?>
	function savePwd() {
		var prevPwd = document.getElementById('prevPwd');	
		var pwd = document.getElementById('pwd');	
		var pwd2 = document.getElementById('pwd2');	
		if(pwd.value != '' || prevPwd.value != '') {
			if(confirm("<?=_t('비밀번호를 변경합니까?\t')?>")) {
				if(pwd.value.length<6 || pwd2.value.length<6) {
					alert("<?=_t('비밀번호는 6자리 이상입니다')?>");
					return false;
				}
				if(pwd.value != pwd2.value) {
					alert("<?=_t('입력된 비밀번호가 서로 다릅니다')?>");
					pwd.select();
					return false;
				}
			} else {
				return false;
			}
		} else {
			alert("<?=_t('비밀번호를 입력해주세요')?>");
			return false;
		}
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/password/");
		request.onSuccess = function() {
			alert("<?=_t('변경했습니다')?>");
			prevPwd.value = '';
			pwd.value = '';
			pwd2.value = '';
		}
		request.onError = function() {
			alert("<?=_t('변경하지 못했습니다')?>");
		}
		request.send("email=&nickname=&prevPwd=" + encodeURIComponent(prevPwd.value) + "&pwd=" + encodeURIComponent(pwd.value));
	}
	
<?
if ($service['type'] != 'single') {
?>
	
	function refreshReceiver(event) {
		if (event.keyCode == 188) {
			var receivers = createReceiver();
			createBlogIdentify(receivers);
		}
	}
	var receiverCount = 0;
	var errorStr;
	function createReceiver(target) {
		var receiver = document.getElementById(target);
		
		receiversTemp = receiver.value.split(',');
		receivers = new Array();
		
		for (var i=0; i<receiversTemp.length; i++) {
			var name, email;
			
			pos1 = receiversTemp[i].indexOf('<');
			pos2 = receiversTemp[i].indexOf('>');
			if(pos1 != -1 && pos2 != -1) {
				name = receiversTemp[i].substring(0,pos1);
				email = Trim(receiversTemp[i].substring(pos1+1,pos2));
			} else {
				name = '';
				email = Trim(receiversTemp[i]);
			}
			if(!checkMail(email)) {
				errorStr += "\"" + email + "\"<?=_t('은 올바른 이메일이 아닙니다')?>\n\n";
				continue;
			}
			
			identy = '('+( (name == undefined || name == '')  ? email : name)+')';
			
			
			receivers[i] = new Array(name, email);
		}
		
		
		return receivers;
	}

	function sendInvitation() {
		var receiver = document.getElementById('invitation_receiver');
		var identify = document.getElementById('invitation_identify');
		var comment = document.getElementById('invitation_comment');
		var sender = document.getElementById('invitation_sender');
		
		/*
		receiver.style.backgroundColor='';
		identify.style.backgroundColor='';
		comment.style.backgroundColor='';
		sender.style.backgroundColor='';
		*/
		errorStr ='';

		if(receiver.value == '') {
			errorStr = '<?=_t('초대받을 사람의 이름<이메일>을 적어주세요.\n이메일만 적어도 됩니다')?>\n \n';
			//receiver.style.backgroundColor='#FFFF00';
		}
		
		if(identify.value == '') {
			errorStr = '<?=_t('초대받을 사람이 사용할 블로그 식별자를 적어주세요')?>\n \n';
			//identify.style.backgroundColor='#FFFF00';
		}
		
		inviteList = createReceiver("invitation_receiver");
		sender = createReceiver("invitation_sender");
		
		if(errorStr != '') {
			alert(errorStr);
			return false;
		}
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/invite/");
		request.onVerify = function() {
			return this.getText("/response/error") == 15;
		}
		request.onSuccess = function() {
			PM.showMessage("<?=_t('초대장을 발송했습니다')?>", "center", "bottom");
			window.location.href='<?=$blogURL?>/owner/setting/account/';
		}
		request.onError = function() {
			alert(Number(this.getText("/response/error")));
			switch(Number(this.getText("/response/error"))) {
				case 2:
					alert('<?=_t('이메일이 바르지 않습니다')?>');
					//receiver.style.backgroundColor='#FFFF00';
					break;
				case 4:
					alert('<?=_t('블로그 식별자는 영문으로 입력하셔야 합니다')?>');
					//identify.style.backgroundColor='#FFFF00';
					break;
				case 5:
					//receiver.style.backgroundColor='#FFFF00';			
					alert('<?=_t('이미 존재하는 이메일입니다')?>');
					break;
				case 60:
					//identify.style.backgroundColor='#FFFF00';
					alert('<?=_t('이미 존재하는 블로그 식별자입니다')?>');
					break;
				case 61:
					//identify.style.backgroundColor='#FFFF00';
					alert('<?=_t('이미 존재하는 블로그 식별자입니다')?>');
					break;
				case 62:
					alert('<?=_t('실패 했습니다')?>');
					break;
				case 11:
					alert('<?=_t('실패 했습니다')?>');
					break;
				case 12:
					alert('<?=_t('실패 했습니다')?>');
					break;
				case 13:
					alert('<?=_t('실패 했습니다')?>');
					break;
				case 14:				
					//receiver.style.backgroundColor='#FFFF00';
					alert('<?=_t('실패 했습니다')?>');
					break;
				default:
					alert('<?=_t('실패 했습니다')?>');
			}
		}
		request.send("&senderName="+encodeURIComponent(sender[0][0])+"&senderEmail="+encodeURIComponent(sender[0][1])+"&email="+inviteList[0][1]+"&name="+encodeURIComponent(inviteList[0][0])+"&identify="+identify.value+"&comment="+encodeURIComponent(comment.value));
	} 
	
	function createBlogIdentify(receivers) {
		var blogList = document.getElementById('blogList');
		
		for (var name in receivers) {
			target = document.getElementById(name);
			if (target != null) continue;
				blogList.innerHTML += receivers[name][2];
		}
	}
	
	function cancelInvite(userid, caller) {
		if(!confirm('<?=_t('삭제할까요?')?>')) return false;
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/cancelInvite/");
		request.onSuccess = function() {
			//caller.parentNode.parentNode.removeNode();
			window.location.href="<?=$blogURL?>/owner/setting/account";
		}
		request.onError = function() {
			alert('<?=_t('실패 했습니다')?>');
		}
		request.send("userid=" + userid);
	} 
<?
}
?>
//]]>
</script>
<table cellspacing="0" width="100%">
    <tr>
        <td><table cellspacing="0" style="width:100%; height:28px">
                <tr>
                    <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                    <td style="padding:3px 0px 0px 4px"><?=_t('회원정보를 관리합니다')?></td>
                </tr>
            </table></td>
    </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
    <tr >
        <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
<?
?>
            <table cellspacing="0">
                <tr>
                    <td class="entryEditTableLeftCell"><?=_t('필명')?> |</td>
                    <td><input type="text" id="nickname" style="width: 200px" value="<?=$user['name']?>" onkeydown="if(event.keyCode == 13) save();"/>
                    </td>
                </tr>
            </table>
			<table cellspacing="0">
                <tr>
                    <td class="entryEditTableLeftCell">E-mail |</td>
                    <td style="color:#FF0000">
						<input type="text" id="email" style="width: 200px" value="<?=htmlspecialchars(User::getEmail())?>" />
						&nbsp; <?=_t('(로그인시 ID로 사용됩니다)')?>
                    </td>
                </tr>
            </table>
			<div style="padding-left:132px">
				<table class="buttonTop" cellspacing="0" onclick="save()">
					<tr>
						<td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
						<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('변경')?></td>
						<td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
					</tr>
				</table>
			</div>	
			<table cellspacing="0">
                <tr>
                    <td></td>
                    <td>&nbsp;</td>
                </tr>
            </table>
            <table cellspacing="0">
                <tr>
                    <td class="entryEditTableLeftCell"><?=_t('현재 비밀번호')?> |</td>
                    <td><input type="password" id="prevPwd" style="width: 200px" value="<?=(empty($_GET['password']) ? '' : $_GET['password'])?>" />
                    </td>
                </tr>
                <tr>
                    <td class="entryEditTableLeftCell"><?=_t('새로운 비밀번호')?>  |</td>
                    <td><input type="password" id="pwd" style="width: 200px" value="" />
                    </td>
                </tr>
                <tr>
                    <td class="entryEditTableLeftCell"><?=_t('비밀번호 확인')?>  |</td>
                    <td><input type="password" id="pwd2" style="width: 200px" value="" onkeydown="if(event.keyCode == 13) savePwd();" />
                    </td>
                </tr>
            </table>
            <div style="padding-left:132px">
                <table class="buttonTop" cellspacing="0" onclick="savePwd()" >
                    <tr>
                        <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('변경')?></td>
                        <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                    </tr>
                </table>
			  </div>		
<?
?>			

        </td>
    </tr>
</table>

<?
if (($service['type'] != 'single') && (getUserId() == 1)) {
	$urlRule = getBlogURLRule();
?>
<table cellspacing="0" width="100%"> 
    <tr>
        <td><table cellspacing="0" style="width:100%; height:28px">
                <tr>
                    <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                    <td style="padding:3px 0px 0px 4px"><?=_t('친구를 초대합니다')?></td>
                </tr>
            </table></td>
    </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED" border="0">

    <tr >
      <td style="background-color:#EBF2F8; padding-top:10px">			
			<table border="0" cellspacing="5" cellpadding="3">
				<tr>
					<td class="entryEditTableLeftCell" valign="top"><?=_t('초대장')?> |</td>
					<td>
						<table border="0" cellspacing="0" cellpadding="0" width="550" bgcolor="#BCD2E5">
							<tr>
								<td height="30" background="<?=$service['path']?>/image/owner/Invite_top.gif" style="padding:40px 40px 0px 40px; " > <?=_t('받는 사람')?> : 
									<input type="text" id="invitation_receiver" name="text" style="  background-image:url(<?=$service['path']?>/image/owner/invitationBg.gif); width:300px; "onclick="if(!this.selected) this.select();this.selected=true;"  onblur="this.selected=false;"  onkeydown="refreshReceiver(event)" value="<?=_t('이름&lt;이메일&gt; 혹은 이메일')?>" /></td>
							</tr>
							<tr>
								<td background="<?=$service['path']?>/image/owner/Invite_bg.gif" style="padding:0px 40px 20px 40px;" id="'+email+'"><?=_t('블로그 주소')?> : 
									<?=$urlRule[0]?>&nbsp;<input id="invitation_identify" name="text" type="text" style=" background-image:url(<?=$service['path']?>/image/owner/invitationBg.gif); overflow:visible;"/><?=$urlRule[1]?></td>
							</tr>
							<tr>
								<td align="center" background="<?=$service['path']?>/image/owner/Invite_bg.gif"><textarea id="invitation_comment" name="textarea" style="border:0px; line-height:17px; overflow:visible; width:460px; height:300px; background-image:url(<?=$service['path']?>/image/owner/invitationBg.gif)" ><?=_t("블로그를 준비해 두었습니다.\n지금 바로 입주하실 수 있습니다.")?></textarea></td>
							</tr>

							<tr>
								<td align="right" background="<?=$service['path']?>/image/owner/Invite_bg.gif" style="padding: 20px 45px 20px 0px;"><?=_t('보내는 사람')?>  :
									<input id="invitation_sender" name="text2" type="text" style="background-image:url(<?=$service['path']?>/image/owner/invitationBg.gif); overflow:visible; width:200px"  value="<?=htmlspecialchars($user['name'] . '<' . User::getEmail() . '>')?>"/></td>
							</tr>
							<tr>
								<td><img src="<?=$service['path']?>/image/owner/Invite_bottom.gif" width="547" height="17" /></td>
							</tr>
							<tr>
							  <td height="30" align="center" valign="top"><input type="button" name="Submit2" style="border:1px solid #666666; background-color:#ffffff; font-size:11px; color:#000; height:22px;  padding:3px 0px 0px 0px" value="<?=_t('초대장 발송')?>" onclick="sendInvitation()" /></td>
						  </tr>
						</table>

				</tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td class="entryEditTableLeftCell" valign="top" style="padding-top:10px;"><?=_t('초대명단')?> |</td>
					<td >
<?
	$invitedList = getInvited($owner);
?>
						<table border="0" cellspacing="0" cellpadding="5">
							<tr>
							  <td colspan="6" bgcolor="#BCD2E5"></td>
						  </tr>
							<tr>
							  <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"><?=_t('이름')?>(<?=_t('E-mail')?>)</td>
						      <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"><?=_t('주소')?></td>
						      <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"><?=_t('초대일')?></td>
						      <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"><?=_t('경과')?></td>
							   <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"><?=_t('비밀번호')?></td>
							    <td align="center" nowrap="nowrap" bgcolor="#D0E5F1" style="padding:5px; font-family:Verdana; font-size:11px; color:#2E5B74; font-weight:bold;"></td>
						  </tr>
							<tr>
							  <td colspan="6" bgcolor="#BCD2E5"></td>
						  </tr>		
							<tr>
							  <td colspan="6" height="5px;"></td>
						  </tr>								  	
						
<?
	foreach ($invitedList as $value) {
?>						
							<tr>
								<td style="padding:2px 10px 2px 5px; font-family:Verdana; font-size:11px; color:#333;" nowrap="nowrap"><?=htmlspecialchars($value['name'])?>(<?=htmlspecialchars($value['loginid'])?>)</td>
								<td style="padding:2px 10px 2px 5px; font-family:Verdana; font-size:11px; color:#333;" nowrap="nowrap"><a href="<?=getBlogURL($value['blogName'])?>" target="_blank"><?=getBlogURL($value['blogName'])?></a></td>								
								<td style="padding:2px 10px 2px 5px; font-family:Verdana; font-size:11px; color:#333;" nowrap="nowrap"><?=Timestamp::format5($value['created'])?></td>
<?
		if ($value['lastLogin'] == 0) {
?>								<td style="padding:2px 10px 2px 5px; font-family:Verdana; font-size:11px; color:#333; text-align: right;" nowrap="nowrap"><?=timeInterval($value['created'], time()) . ' ' . _t('전')?></td>
							

								<td style="padding:2px 10px 2px 5px; font-family:Verdana; font-size:11px; color:#333; text-align: right;" nowrap="nowrap"><?=fetchQueryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = {$value['userid']} AND host = $owner AND lastLogin = 0")?></td>
							
								<td style="padding:2px 10px 2px 5px;"><input type="button" name="Submit" style="border:1px solid #3D6283; background-color:#6699CC; font-size:11px; color:#fff; height:16px; padding:0px 0px 1px 0px" value="<?=_t('초대취소')?>" onclick="cancelInvite(<?=$value['userid']?>,this);" title="<?=_t('초대에 응하지 않은 사용자의 계정을 삭제합니다')?>"/></td>
<?
		}
?>
							</tr>
							<tr>
							  <td colspan="6" bgcolor="#BCD2E5"></td>
						  </tr>							
<?
	}
?>
						</table>
					</td>
				</tr>
			</table>
		    <p>&nbsp;</p></td>
    </tr>
</table>
<?
}
?>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>
