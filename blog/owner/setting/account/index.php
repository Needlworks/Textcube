<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu51.php';
?>
										<script type="text/javascript">
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

												function save() {
													try {
														var email = document.getElementById('email');
														var nickname = document.getElementById('nickname');

														if(!checkMail(email.value)) {
															alert("<?=_t('이메일이 바르지 않습니다.')?>");
															email.select();
															return false;
														}
														if(nickname.value == '') {
															alert("<?=_t('닉네임을 입력해 주십시오.')?>");
															nick.select();
															return false;
														}
														var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/profile/");
														request.onSuccess = function() {
															PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														}
														request.onError = function() {
															alert("<?=_t('저장하지 못했습니다.')?>");
														}
														request.send("&email=" + email.value + "&nickname=" + nickname.value);
													} catch(e) {

													}
												}
												
												function savePwd() {
													var prevPwd = document.getElementById('prevPwd');
													var pwd = document.getElementById('pwd');
													var pwd2 = document.getElementById('pwd2');
													if(pwd.value != '' || prevPwd.value != '') {
														if(confirm("<?=_t('비밀번호를 변경합니까?')?>")) {
															if(pwd.value.length<6 || pwd2.value.length<6) {
																alert("<?=_t('비밀번호는 6자리 이상입니다')?>");
																return false;
															}
															if(pwd.value != pwd2.value) {
																alert("<?=_t('입력된 비밀번호가 서로 다릅니다.')?>");
																select.pwd();
																return false;
															}
														} else {
															return false;
														}
													} else {
														alert("<?=_t('비밀번호를 입력해주세요.')?>");
														return false;
													}
													var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/account/password/");
													request.onSuccess = function() {
														alert("<?=_t('변경했습니다.')?>");
														prevPwd.value = '';
														pwd.value = '';
														pwd2.value = '';
													}
													request.onError = function() {
														alert("<?=_t('변경하지 못했습니다.')?>");
													}
													request.send("email=&nickname=&prevPwd=" + prevPwd.value + "&pwd=" + pwd.value);
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
															errorStr += "\"" + email + "\"<?=_t('은 올바른 이메일이 아닙니다.')?>\n\n";
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
														errorStr = '<?=_t('초대받을 사람이 사용할 블로그 식별자를 적어주세요.')?>\n \n';
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
														PM.showMessage("<?=_t('초대장을 발송했습니다.')?>", "center", "bottom");
														window.location.href='<?=$blogURL?>/owner/setting/account/';
													}
													request.onError = function() {
														switch(Number(this.getText("/response/error"))) {
															case 2:
																alert('<?=_t('이메일이 바르지 않습니다.')?>');
																//receiver.style.backgroundColor='#FFFF00';
																break;
															case 4:
																alert('<?=_t('블로그 식별자는 영문으로 입력하셔야 합니다.')?>');
																//identify.style.backgroundColor='#FFFF00';
																break;
															case 5:
																//receiver.style.backgroundColor='#FFFF00';
																alert('<?=_t('이미 존재하는 이메일입니다.')?>');
																break;
															case 60:
																//identify.style.backgroundColor='#FFFF00';
																alert('<?=_t('이미 존재하는 블로그 식별자입니다.')?>');
																break;
															case 61:
																//identify.style.backgroundColor='#FFFF00';
																alert('<?=_t('이미 존재하는 블로그 식별자입니다.')?>');
																break;
															case 62:
																alert('<?=_t('실패 했습니다.')?>');
																break;
															case 11:
																alert('<?=_t('실패 했습니다.')?>');
																break;
															case 12:
																alert('<?=_t('실패 했습니다.')?>');
																break;
															case 13:
																alert('<?=_t('실패 했습니다.')?>');
																break;
															case 14:
																//receiver.style.backgroundColor='#FFFF00';
																alert('<?=_t('실패 했습니다.')?>');
																break;
															default:
																alert('<?=_t('실패 했습니다.')?>');
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
														alert('<?=_t('실패 했습니다.')?>');
													}
													request.send("userid=" + userid);
												}
<?
}
?>
											//]]>
										</script>
										
										<div id="part-setting-account" class="part">
											<h2 class="caption"><span class="main-text"><?=_t('회원정보를 관리합니다')?></span></h2>
											
											<div class="data-inbox">
												<div id="info-section" class="section">
													<dl class="line">
														<dt><label for="nickname"><span class="text"><?=_t('필명')?></span></label><span class="divider"> | </span></dt>
														<dd><input type="text" id="nickname" class="text-input" value="<?=$user['name']?>" onkeydown="if(event.keyCode == 13) save();" /></dd>
														<dd class="clear"></dd>
													</dl>
													<dl class="line">
														<dt><label for="email"><span class="text">e-mail</span></label><span class="divider"> | </span></dt>
														<dd>
															<input type="text" id="email" class="text-input" value="<?=htmlspecialchars($user['loginid'])?>" />
															<em><?=_t('(로그인시 ID로 사용됩니다)')?></em>
														</dd>
														<dd class="clear"></dd>
													</dl>
													<div class="button-box">
														<a class="save-button button" href="#void" onclick="save()"><span class="text"><?=_t('변경')?></span></a>
													</div>
													<div class="clear"></div>
												</div>
												
												<hr class="hidden" />
												
												<div id="account-section" class="section">
													<dl class="line">
														<dt><label for="prevPwd"><span class="text"><?=_t('현재 비밀번호')?></span></label><span class="divider"> | </span></dt>
														<dd><input type="password" id="prevPwd" class="text-input" value="<?=(empty($_GET['password']) ? '' : $_GET['password'])?>" /></dd>
														<dd class="clear"></dd>
													</dl>
													<dl class="line">
														<dt><label for="pwd"><span class="text"><?=_t('새로운 비밀번호')?></span></label><span class="divider"> | </span></dt>
														<dd><input type="password" id="pwd" class="text-input" value="" /></dd>
														<dd class="clear"></dd>
													</dl>
													<dl class="line">
														<dt><label for="pwd2"><span class="text"><?=_t('비밀번호 확인')?></span></label><span class="divider"> | </span></dt>
														<dd><input type="password" id="pwd2" class="text-input" value="" onkeydown="if(event.keyCode == 13) savePwd();" /></dd>
														<dd class="clear"></dd>
													</dl>
													<div class="button-box">
														<a class="save-button button" href="#void" onclick="savePwd()"><span class="text"><?=_t('변경')?></span></a>
													</div>
													<div class="clear"></div>
												</div>
											</div>
										</div>
									
<?
if (($service['type'] != 'single') && (getUserId() == 1)) {
	$urlRule = getBlogURLRule();
?>
										<div id="part-setting-invite" class="part">
											<h2 class="caption"><span class="main-text"><?=_t('친구를 초대합니다')?></span></h2>
											
											<div class="data-inbox">
												<dl id="letter-section" class="section">
													<dt class="title"><span class="text"><?=_t('초대장')?></span><span class="divider"> | </span></dt>
													<dd id="letter">
														<div class="letter-head">
															<dl class="line">
																<dt><label for="text"><span class="text"><?=_t('받는 사람')?></span></label><span class="divider"> : </span></dt>
																<dd><input type="text" id="invitation_receiver" class="text-input" name="text" value="<?=_t('이름&lt;이메일&gt; 혹은 이메일')?>" onclick="if(!this.selected) this.select();this.selected=true;" onblur="this.selected=false;" onkeydown="refreshReceiver(event)" /></dd>
																<dd class="clear"></dd>
															</dl>
															<dl class="line">
																<dt><label for="invitation_sender"><span class="text"><?=_t('보내는 사람')?></span></label><span class="divider"> : </span></dt>
																<dd><input type="text" id="invitation_sender" class="text-input" name="text2" value="<?=htmlspecialchars($user['name'] . '<' . $user['loginid'] . '>')?>" /></dd>
																<dd class="clear"></dd>
															</dl>
															<dl class="line">
																<dt><label for="invitation_identify"><span class="text"><?=_t('블로그 주소')?></span></label><span class="divider"> : </span></dt>
																<dd><?=$urlRule[0]?><input type="text" id="invitation_identify" class="text-input" name="text" /><?=$urlRule[1]?></dd>
																<dd class="clear"></dd>
															</dl>
															
															<div class="clear"></div>
														</div>
														
														<div class="letter-body">
															<textarea id="invitation_comment" cols="60" rows="30" name="textarea"><?=_t("블로그를 준비해 두었습니다.\n지금 바로 입주하실 수 있습니다.")?></textarea>
														</div>
														
														<div class="letter-foot">
															<div class="clear"></div>
														</div>
													</dd>
												</dl>
												
												<div class="clear"></div>
												
												<div class="button-box">
													<a class="invite-button button" href="#void" onclick="sendInvitation()"><span class="text"><?=_t('초대장 발송')?></span></a>
												</div>
												
												<dl id="list-section" class="section">
													<dt class="title"><span class="text"><?=_t('초대명단')?></span><span class="divider"> | </span></dt>
													<dd>
<?
	$invitedList = getInvited($owner);
?>
														<table cellspacing="0" cellpadding="0">
															<thead>
																<tr>
																	<td class="email"><span class="text"><?=_t('이름')?>(<?=_t('E-mail')?>)</span></td>
																	<td class="address"><span class="text"><?=_t('주소')?></span></td>
																	<td class="date"><span class="text"><?=_t('초대일')?></span></td>
																	<td class="status"><span class="text"><?=_t('경과')?></span></td>
																	<td class="password"><span class="text"><?=_t('비밀번호')?></span></td>
																	<td class="cancel"><span class="text"><?=_t('초대취소')?></span></td>
																</tr>
															</thead>
															<tbody>
<?
	foreach ($invitedList as $value) {
?>
																<tr class="tr-body">
																	<td class="email"><?=htmlspecialchars($value['name'])?>(<?=htmlspecialchars($value['loginid'])?>)</td>
																	<td class="address"><a href="<?=getBlogURL($value['blogName'])?>" onclick="window.open(this.href); return false;"><?=getBlogURL($value['blogName'])?></a></td>
																	<td class="date"><?=Timestamp::format5($value['created'])?></td>
<?
		if ($value['lastLogin'] == 0) {
?>
																	<td class="status"><?=timeInterval($value['created'], time()) . ' ' . _t('전')?></td>
																	<td class="password"><?=fetchQueryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = {$value['userid']}")?></td>
																	<td class="cancel"><a class="cancel-button button" href="#void" onclick="cancelInvite(<?=$value['userid']?>,this);" title="<?=_t('초대에 응하지 않은 사용자의 계정을 삭제합니다.')?>"><span class="text"><?=_t('초대취소')?></span></a></td>
<?
		}
?>
																</tr>
<?
	}
?>
															</tbody>
														</table>
													</dd>
												</dl>
												
												<div class="clear"></div>
											</div>
										</div>
<?
}

require ROOT . '/lib/piece/owner/footer0.php';
?>
