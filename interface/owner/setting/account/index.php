<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if( !defined( 'OPENID_REGISTERS' ) ) {
	define('OPENID_REGISTERS', 10);
}
$IV = array(
	'GET' => array(
		'password' => array('any' ,'mandatory' => false)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function checkMail(str) {
									try {
										var filter  = /^([-a-zA-Z0-9_\.])+\@(([-a-zA-Z0-9])+\.)+([a-zA-Z0-9]{2,4})+$/;
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
								
								function changeHomepage() {
									try {
										var type = document.getElementById('homepage-section').hptype[0].checked ? 0:1;
										var hpurl = document.getElementById('hpurl');
										var blogid = document.getElementById('blogid');
										if(type && (hpurl.value == 'http://' || hpurl.value == '')) {
											alert("<?php echo _t('홈페이지 주소를 입력해 주십시오.');?>");
											hpurl.select();
											return false;
										}
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/account/homepage/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('저장하지 못했습니다.');?>");
										}
										request.send("&type=" + encodeURIComponent(type) + "&hp=" + encodeURIComponent((type==0 ? blogid.value : hpurl.value)));
									} catch(e) {
									}
								}
								function save() {
									try {
										var email = document.getElementById('email');
										var nickname = document.getElementById('nickname');
										if(!checkMail(email.value)) {
											alert("<?php echo _t('이메일 형식이 올바르지 않습니다.');?>");
											email.select();
											return false;
										}
										if(nickname.value == '') {
											alert("<?php echo _t('별칭을 입력해 주십시오.');?>");
											nick.select();
											return false;
										}
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/account/profile/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('저장하지 못했습니다.');?>");
										}
										request.send("&email=" + encodeURIComponent(email.value) + "&nickname=" + encodeURIComponent(nickname.value));
									} catch(e) {
									}
								}
								
								function savePwd() {
									var prevPwd = document.getElementById('prevPwd');
									var pwd = document.getElementById('pwd');
									var pwd2 = document.getElementById('pwd2');
									if(pwd.value != '' || prevPwd.value != '') {
										if(confirm("<?php echo _t('비밀번호를 변경하시겠습니까?');?>")) {
											if(pwd.value.length<6 || pwd2.value.length<6) {
												alert("<?php echo _t('비밀번호는 6자리 이상입니다.');?>");
												return false;
											}
											if(pwd.value != pwd2.value) {
												alert("<?php echo _t('입력된 비밀번호가 서로 다릅니다.');?>");
												select.pwd();
												return false;
											}
										} else {
											return false;
										}
									} else {
										alert("<?php echo _t('비밀번호를 입력해 주십시오.');?>");
										return false;
									}
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/account/password/");
									request.onSuccess = function() {
										alert("<?php echo _t('변경했습니다.');?>");
										prevPwd.value = '';
										pwd.value = '';
										pwd2.value = '';
									}
									request.onError = function() {
										alert("<?php echo _t('변경하지 못했습니다.');?>");
									}
									request.send("email=&nickname=&prevPwd=" + encodeURIComponent(prevPwd.value) + "&pwd=" + encodeURIComponent(pwd.value));
								}

<?php
if ($service['type'] != 'single' &&  Acl::check("group.creators")) {
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
											temp = "<?php echo _t('%1은 올바른 이메일이 아닙니다.');?>\n\n";
											errorStr += temp.replace('%1', '"' + email + '"');
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
									
									errorStr ='';
									
									if(receiver.value == '') {
										errorStr = '<?php echo _t('초대받을 사람의 이름<이메일>을 적어 주십시오.\n이메일만 적어도 됩니다.');?>';
									}
									
									if(identify.value == '') {
										errorStr = '<?php echo _t('초대받을 사람이 사용할 블로그 식별자를 적어 주십시오.');?>';
									}
									
									inviteList = createReceiver("invitation_receiver");
									sender = createReceiver("invitation_sender");
									
									if(errorStr != '') {
										alert(errorStr);
										return false;
									}
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/account/invite/");
									request.onVerify = function() {
										return this.getText("/response/error") == 0;
									}
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('초대장을 발송했습니다.');?>", "center", "bottom");
										window.location.href='<?php echo $blogURL;?>/owner/setting/account/';
									}
									request.onError = function() {
										switch(Number(this.getText("/response/error"))) {
											case 2:
												alert('<?php echo _t('이메일이 바르지 않습니다.');?>');
												break;
											case 4:
												alert('<?php echo _t('블로그 식별자는 영문으로 입력하셔야 합니다.');?>');
												break;
											case 5:
												alert('<?php echo _t('이미 존재하는 이메일입니다.');?>');
												break;
											case 60:
												alert('<?php echo _t('이미 존재하는 블로그 식별자입니다.');?>');
												break;
											case 61:
												alert('<?php echo _t('이미 존재하는 블로그 식별자입니다.');?>');
												break;
											case 62:
												alert('<?php echo _t('리더를 만드는 과정에서 오류가 발생하였습니다.');?>');
												break;
											case 65:
												alert('<?php echo _t('블로그 권한 설정 과정에서 오류가 발생하였습니다.');?>');
												break;												
											case 11:
												alert('<?php echo _t('실패했습니다.');?>');
												break;
											case 12:
												alert('<?php echo _t('실패했습니다.');?>');
												break;
											case 13:
												alert('<?php echo _t('실패했습니다.');?>');
												break;
											case 14:
												alert('<?php echo _t('메일 전송에 실패하였습니다.');?>');
												break;
											default:
												alert('<?php echo _t('실패했습니다.');?>');
										}
										msg = this.getText("/response/message");
										if( msg ) {
											alert( msg );
										}
										//window.location.href='<?php echo $blogURL;?>/owner/setting/account/';
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
									if(!confirm('<?php echo _t('삭제하시겠습니까?');?>')) return false;
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/account/cancelInvite/");
									request.onSuccess = function() {
										//caller.parentNode.parentNode.removeNode();
										window.location.href="<?php echo $blogURL;?>/owner/setting/account";
									}
									request.onError = function() {
										alert('<?php echo _t('실패했습니다.');?>');
									}
									request.send("userid=" + userid);
								}
<?php
}
?>							//]]>
						</script>


<?php
// Teamblog :: Get username.
 $teamblog_user = POD::queryRow("SELECT name, loginid 
	 FROM {$database['prefix']}Users 
	 WHERE userid='".getUserId()."'");
// End TeamBlog
?>


						<div id="part-setting-account" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('계정 정보');?></span></h2>
							
							<div class="data-inbox">
								<form id="info-section" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/account">
									<fieldset class="container">
										<legend><?php echo _t('계정 정보');?></legend>
										
										<dl id="blogger-name-line" class="line">
											<dt><label for="nickname"><?php echo _t('필명');?></label></dt>
											<dd><input type="text" id="nickname" class="input-text" value="<?php echo htmlspecialchars($teamblog_user['name']);?>" onkeydown="if(event.keyCode == 13) save();" /></dd>
										</dl>
										<dl id="blogger-email-line" class="line">
											<dt><label for="email"><?php echo _t('e-mail');?></label></dt>
											<dd>
												<input type="text" id="email" class="input-text" value="<?php echo htmlspecialchars($teamblog_user['loginid']);?>" />
												<em><?php echo _t('(로그인시 ID로 사용됩니다)');?></em>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="save(); return false;" />
									</div>
								</form>
								
								<hr class="hidden" />
								
								<form id="account-section" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/account">
									<fieldset class="container">
										<legend><?php echo _t('비밀번호 변경');?></legend>
										
										<dl id="current-password-line" class="line">
											<dt><label for="prevPwd"><?php echo _t('현재 비밀번호');?></label></dt>
											<dd><input type="password" id="prevPwd" class="input-text" value="<?php echo (empty($_GET['password']) ? '' : $_GET['password']);?>" /></dd>
										</dl>
										<dl id="new-password1-line" class="line">
											<dt><label for="pwd"><?php echo _t('새로운 비밀번호');?></label></dt>
											<dd><input type="password" id="pwd" class="input-text" /></dd>
										</dl>
										<dl id="new-password2-line" class="line">
											<dt><label for="pwd2"><?php echo _t('비밀번호 확인');?></label></dt>
											<dd><input type="password" id="pwd2" class="input-text" onkeydown="if(event.keyCode == 13) savePwd();" /></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="savePwd(); return false;" />
									</div>
								</form>
							</div>
							<div id="part-setting-account" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('대표 주소');?></span></h2>
								<div class="main-explain-box">
									<p class="explain"><?php echo _t("댓글 및 필자 정보에 사용되는 대표 홈페이지 주소를 설정합니다.")?></p>
								</div>
<?php
$blogs = POD::queryColumn("SELECT blogid FROM {$database['prefix']}TeamBlog WHERE userid = ".getUserId()." ORDER BY acl DESC");
$hptype = (empty($blogs) || is_string(getUserSetting("homepage"))? "url" : "blogid");
?>
								<form id="homepage-section" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/account/homepage">
									<fieldset class="container">
										<legend><?php echo _t('대표 주소');?></legend>
										<dl id="blogger-name-line" class="line">
											<dt><?php echo _t('대표 주소');?></dt>
											<dd><div><input id="hptype" type="radio" name="hptype" value="id" <?php echo ($hptype == "blogid" ? "checked=\"checked\"" : "");?> > <?php echo _t('참여중인 블로그');?>
<?php
if(!empty($blogs)) {
?>
												<select id="blogid" name="blogid">
<?php
	foreach ($blogs as $blog) {
?>
													<option value="<?php echo $blog;?>"><?php echo getBlogName($blog);?></option>
<?php
}
?>
												</select>
<?php
}
?>
												</div>
												<div>
												<input id="hptype" type="radio" name="hptype" value="hp" <?php echo ($hptype == "url" ? "checked=\"checked\"":"");?> > <?php echo _t('외부 주소');?> <input type="text" name="hpurl" id="hpurl" class="input-text" value="<?php echo getUserSetting("homepage","http://");?>">
												</div>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="changeHomepage(); return false;" />
									</div>
								</form>
							</div>
						</div>

<!-- OPENID -->
						<div id="part-setting-openid" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('오픈아이디 연결');?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="site"><span class="text"><?php echo _t('오픈아이디')?></span></th>
										<th class="site"><span class="text"><?php echo _t('삭제');?></span></th>
									</tr>
								</thead>
								<tbody>
<?php
$currentOpenID = fireEvent("OpenIDGetCurrent", null);
$openid_list = array();
for( $i=0; $i<OPENID_REGISTERS; $i++ )
{
	$openid = getUserSetting( "openid." . $i );
	if( !empty($openid) ) {
		array_push( $openid_list, $openid );
	}
}
for ($i=0; $i<count($openid_list); $i++) {
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == count($openid_list) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td><?php echo $openid_list[$i] ?></td>
										<td><a href="<?php echo $blogURL?>/owner/setting/account/openid?mode=del&amp;openid_identifier=<?php echo urlencode($openid_list[$i])?>"><?php echo _t('삭제') ?></a></td>
									</tr>
<?php
}
?>
								</tbody>
							</table>
<?php
if( $i > 0 ) { /* 출력된것이 하나라도 있다면*/
?>
							<div class="data-inbox openid-account-help">
								<?php echo _t('삭제: 본 계정과의 관계를 끊습니다.'); ?>
							</div> 
<?php
}
?>
							<div class="data-inbox">
<?php
$openidPluginCheck = fireEvent("OpenIDGetCurrent","*NONE*");
if( $openidPluginCheck != "*NONE*" ) {
?>
								<form id="openid-section" class="section" method="get" action="<?php echo $blogURL;?>/owner/setting/account/openid">
									<fieldset class="container">
										<legend><?php echo _t('오픈아이디 연결');?></legend>
										
										<dl id="blogger-openid-line" class="line">
											<dt><label for="openid_identifier"><?php echo _t('오픈아이디');?></label></dt>
											<dd><input type="text" id="openid_identifier" name="openid_identifier" class="input-text" value="<?php echo $currentOpenID ?>" />
												<input type="submit" class="save-button input-button" value="<?php echo _t('연결하기');?>" />
											</dd>
										</dl>
										<div class="openid-account-help">
											<?php echo _t('연결하기: 로그인하면 본 계정의 권한을 갖습니다.'); ?>
										</div> 
<?php 
	if( Acl::check( 'group.owners' ) ) { 
?>
										<div class="openid-account-help">
												<b><a href="<?php echo $blogURL?>/owner/plugin/adminMenu?name=CL_OpenID/openid_manage"><?php echo _t('오픈아이디 플러그인 설정 바로가기')?></b></a>
										</div> 
<?php
	} 
?>
									</fieldset>
									<input type="hidden" name="mode" value="add" />
								</form>
<?php
} else if( Acl::check('group.administrators') ) { 
?>
								<dl id="blogger-openid-activate-line" class="line">
									<dt><label for="nickname"><?php echo _t('오픈아이디');?></label></dt>
									<dd><em><?php echo _t('오픈아이디 인증 플러그인을 활성화하십시오'); ?></em>
									<a class="button" href="<?php echo $blogURL?>/owner/plugin">(<?php echo _t('플러그인 설정 바로가기'); ?></a>)
									</dd>
								</dl>
<?php
} else {
?>
								<dl id="blogger-name-line" class="line">
									<dt><label for="nickname"><?php echo _t('오픈아이디');?></label></dt>
									<dd><em><?php echo _t('오픈아이디 플러그인을 사용하고 있지 않으므로, 오픈아이디는 설정할 수 없습니다. 관리자에게 문의 하십시오'); ?></em>
									</dd>
								</dl>
<?php
}
?>
							</div>
						</div>
<!-- OPENID END -->
<?php
if ($service['type'] != 'single' && Acl::check("group.creators")) {
	$urlRule = getBlogURLRule();
?>
						<div id="part-setting-invite" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('친구를 초대합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="letter-section" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/account">
									<dl>
										<dt class="title"><span class="label"><?php echo _t('초대장');?></span></dt>
										<dd id="letter">
											<div id="letter-head">
												<div id="receiver-line" class="line">
													<label for="invitation_receiver"><?php echo _t('받는 사람');?></label>
													<input type="text" id="invitation_receiver" class="input-text" name="text" value="<?php echo _t('이름&lt;이메일&gt; 혹은 이메일');?>" onclick="if(!this.selected) this.select();this.selected=true;" onblur="this.selected=false;" onkeydown="refreshReceiver(event)" />
												</div>
												<div id="blog-address-line" class="line">
													<label for="invitation_identify"><?php echo _t('블로그 주소');?></label>
													<span class="inter-word"><?php echo link_cut($urlRule[0]);?></span><input type="text" id="invitation_identify" class="input-text" name="text" />
			
<?php 
	if (!empty($urlRule[1])) {
?>
													<span class="inter-word"><?php echo $urlRule[1];?></span>
<?php 
	}
?>
												</div>
											</div>
														
											<div id="letter-body">
												<textarea id="invitation_comment" cols="60" rows="3" name="textarea"><?php echo _t("블로그를 준비해 두었습니다.\n지금 바로 입주하실 수 있습니다.");?></textarea>
											</div>
											
											<div id="letter-foot">
												<div id="sender-line" class="line">
													<label for="invitation_sender"><?php echo _t('보내는 사람');?></label>
													<input type="text" id="invitation_sender" class="input-text" name="text2" value="<?php echo htmlspecialchars(htmlspecialchars($user['name']) . '<' . User::getEmail() . '>');?>" />
												</div>
											</div>
										</dd>
									</dl>
									<div class="button-box">
										<input type="submit" class="input-button" value="<?php echo _t('초대장 발송');?>" onclick="sendInvitation(); return false;" />
									</div>
								</form>
								
								<div id="list-section" class="section">
									<dl>
										<dt class="title"><span class="label"><?php echo _t('초대명단');?></span></dt>
										<dd>
<?php
	$invitedList = getInvited(getUserId());
?>
											<table cellspacing="0" cellpadding="0">
												<thead>
													<tr>
														<th class="email"><span class="text"><?php echo _t('이름(e-mail)');?></span></th>
														<th class="date"><span class="text"><?php echo _t('초대일');?></span></th>
														<th class="status"><span class="text"><?php echo _t('경과');?></span></th>
														<th class="password"><span class="text"><?php echo _t('비밀번호');?></span></th>
														<th class="cancel"><span class="text"><?php echo _t('초대취소');?></span></th>
													</tr>
												</thead>
												<tbody>
<?php
	$count = 0;
	foreach ($invitedList as $value) {
		$className = ($count % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($count == sizeof($invitedList) - 1) ? ' last-line' : '';
?>
													<tr class="<?php echo $className;?> inactive-class">
														<td class="email"><?php echo htmlspecialchars($value['name']);?>(<?php echo htmlspecialchars($value['loginid']);?>)</td>
														<td class="date"><?php echo Timestamp::format5($value['created']);?></td>
<?php
		if ($value['lastLogin'] == 0) {
?>
														<td class="status"><?php echo _f('%1 전', timeInterval($value['created'], time()));?></td>
														<td class="password"><?php echo POD::queryCell("SELECT value FROM {$database['prefix']}UserSettings WHERE userid = {$value['userid']} AND name = 'AuthToken'");?></td>
														<td class="cancel"><a class="cancel-button button" href="#void" onclick="cancelInvite(<?php echo $value['userid'];?>,this);return false;" title="<?php echo _t('초대에 응하지 않은 사용자의 계정을 삭제합니다.');?>"><span class="text"><?php echo _t('초대취소');?></span></a></td>
<?php
		} else {
?>
														<td class="status"><?php echo _t('가입');?></td>
														<td></td>
														<td></td>
<?php
		}
?>
													</tr>
<?php
		$count++;
	}
?>
												</tbody>
											</table>
										</dd>
									</dl>
								</div>
							</div>
						</div>
<?php
}

require ROOT . '/lib/piece/owner/footer.php';
?>
