<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'password' => array('any' ,'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';
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

<?php
if( Acl::check('group.owners')) {?>
								function refreshReceiver(event) {
									if (event.keyCode == 188) {
										var receivers = createReceiver();
										createBlogIdentify(receivers);
									}
								}
								
								var receiverCount = 0;
								var errorStr;
								
								function createReceiver(target) {
									receivers = new Array();
									if( target == undefined ) {
										return receivers;
									}
									var receiver = document.getElementById(target);
									
									receiversTemp = receiver.value.split(',');
									
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
											temp = "<?php	echo _t('%1은 올바른 이메일이 아닙니다.');?>\n\n";
											errorStr += temp.replace('%1', '"' + email + '"');
											continue;
										}
										identy = '('+( (name == undefined || name == '')  ? email : name)+')';
										receivers[i] = new Array(name, email);
									}
										return receivers;
								}
								
								function clearPASS() {
									var password = document.getElementById('invite_password');
									password.value = '';
								}
								
								function sendInvitation() {
									var receiver = document.getElementById('invitation_receiver');
									var comment = document.getElementById('invitation_comment');
									var sender = document.getElementById('invitation_sender');
									
									errorStr ='';
									
									if(receiver.value == '') {
										errorStr = '<?php	echo _t('초대받을 사람의 이름<이메일>을 적어 주십시오.\n이메일만 적어도 됩니다.');?>';
									}
									
									inviteList = createReceiver("invitation_receiver");
									sender = createReceiver("invitation_sender");

									if(errorStr != '') {
										alert(errorStr);
										return false;
									}
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/network/teamblog/invite/");
									request.onVerify = function() {
										return this.getText("/response/error") == 0;
									}
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('초대장을 발송했습니다.');?>", "center", "bottom");
										window.location.href='<?php	echo $blogURL;?>/owner/network/teamblog/';
									}
									request.onError = function() {
										switch(Number(this.getText("/response/error"))) {
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
												alert('<?php echo _t('실패했습니다.');?>');
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
											case 20:
												alert('<?php echo _t('팀원 추가에 실패했습니다.');?>');
												break;
											case 21:
												alert('<?php echo _t('이미 팀원으로 등록된 사용자입니다.');?>');
												break;
											default:
												alert('<?php echo _t('실패했습니다.');?>');
										}
										msg = this.getText("/response/message");
										if( msg ) {
											alert( msg );
										}
									}
									request.send("&senderName="+encodeURIComponent(sender[0][0])+"&senderEmail="+encodeURIComponent(sender[0][1])+"&email="+inviteList[0][1]+"&name="+encodeURIComponent(inviteList[0][0])+"&comment="+encodeURIComponent(comment.value));
								}
								
								function createBlogIdentify(receivers) {
									var blogList = document.getElementById('blogList');
									
									for (var name in receivers) {
										target = document.getElementById(name);
										if (target != null) continue;
											blogList.innerHTML += receivers[name][2];
									}
								}
								
								function deleteUser(userid, atype) {
									if(atype == 1) { // If there are posts from user.
										if(!confirm("<?php echo _t('선택된 사용자를 정말 삭제하시겠습니까?');?>\n\n<?php echo _t('삭제되는 기존 사용자의 글은 전부 관리자의 글로 변환됩니다.');?>\n(<?php echo _t('글이 전부 삭제되지는 않고 팀블로그의 로그인 데이터만 삭제됩니다');?>)\n<?php echo _t('삭제 이후에는 복원이 불가능합니다.');?> <?php echo _t('정말 삭제 하시겠습니까?');?>")) return false;
									} else { // No post from user.
										if(!confirm('<?php echo _t('삭제 하시겠습니까?');?>')) 
											return false;
									}
									var request = new HTTPRequest("POST", "<?php	echo $blogURL;?>/owner/network/teamblog/deleteUser/");
									request.onSuccess = function() {
										window.location.href="<?php 	echo $blogURL;?>/owner/network/teamblog";
									}
									request.onError = function() {
										alert("<?php echo _t('실패했습니다.');?>");
									}
									request.send("userid=" + userid);
								}

<?php
}
if( Acl::check('group.administrators')) {
?>
								function changeACL(acltype, userid, checked) {

									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/network/teamblog/changeACL/");
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('설정을 변경했습니다.');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('실패했습니다.');?>");
									}
									request.send("acltype=" + acltype + "&userid=" + userid + "&switch=" + checked);
								}
								
								var CHCrev=false;
								function Check_rev() {
									if(CHCrev == false) CHCrev = true;
									else CHCrev = false;

									for(var i = 0;;i++) {
										if(document.getElementById('check_'+ i)) {
											document.getElementById('check_'+ i).checked = CHCrev;
										}
										else{
											break;
										}
							
									}
								}
<?php
}
?>
						//]]>
						</script>

						<div id="part-setting-account" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('이 블로그에 등록된 필자들의 권한을 설정하고 관리합니다');?></span></h2>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('이 블로그에 다른 필자를 등록하고 권한을 줄 수 있습니다. 필자로 등록된 경우 기본적으로 글쓰기 권한이 주어집니다. 관리자 권한의 경우 블로그의 설정을 변경할 수 있습니다. 글관리 권한의 경우 다른 필자의 글을 교정하거나 공개 정도를 변경할 수 있습니다.');?></p>
							</div>
							<div id="list-section" class="section">
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="check"><input type="checkbox" name="Aclick" onclick="Check_rev()" /></th>
											<th class="name"><span class="text"><?php echo _t('이름');?></span></th>
											<th class="email"><span class="text"><?php echo _t('이메일');?></span></th>
											<th class="date"><span class="text"><?php echo _t('가입일');?></span></th>
											<th class="posting"><span class="text"><?php echo _t('글 수');?></span></th>
											<th class="status"><span class="text"><?php	echo _t('초대상태');?></span></th>
											<th class="privilege"><span class="text"><?php echo _t('권한');?></span></th>
											<th class="exclude"><span class="text"><?php	echo _t('팀블로그 제외');?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
	$blogid = getBlogId();
	$teamblog_user = POD::queryAll("SELECT t.*, u.loginid, u.password, u.name, u.created
		FROM {$database['prefix']}Privileges t, 
		 	{$database['prefix']}Users u 
		WHERE t.blogid = '$blogid' 
			AND u.userid = t.userid 
		ORDER BY u.created DESC"); 

	$count = 0;

	if(isset($teamblog_user)) {
		foreach($teamblog_user as $value) {
			$value['posting'] = POD::queryCell("SELECT count(*) 
					FROM {$database['prefix']}Entries 
					WHERE blogid = $blogid AND userid = {$value['userid']}");
			$className= ($count%2)==1 ? 'even-line' : 'odd-line';
			$className.=($count==sizeof($teamblog_user)-1) ? ' last-line':'';
?>
												<tr class="<?php echo $className;?> inactive-class">
													<td class="check">
														<input type="checkbox" id="check_<?php echo $count;?>" />
													</td>
													<td class="name"><?php echo $value['name'];?></td>
													<td class="email"><?php	echo  htmlspecialchars($value['loginid']);?></td>
													<td class="date"><?php echo Timestamp::format5($value['created']);?></td>
													<td class="posting"><?php echo $value['posting'];?></td>
													<td class="status">
<?php
			$authtoken = getAuthToken($value['userid']);
			if($value['lastlogin'] == 0) { 
?>
													<?php echo _t('미참여');?>
<?php
				if($value['acl'] & BITWISE_ADMINISTRATOR) {
					$invitationURL = getInvitationLink(getBlogURLById($blogid), htmlspecialchars($value['loginid']), $value['password'], $authtoken);
					echo '<a href="'.$invitationURL.'">'._t('초대 링크').'</a>';
				}
			} else { 
?>
													<?php echo _t('참여중');?>
<?php
				if(($value['acl'] & BITWISE_ADMINISTRATOR) && !empty($authtoken) && !is_null($authtoken)) {
					$invitationURL = getInvitationLink(getBlogURLById($blogid), htmlspecialchars($value['loginid']), $value['password'], $authtoken);
					echo '<a href="'.$invitationURL.'">'._t('재발급 링크').'</a>';
				}
			}
?></td>
													<td class="password">
<?php
			if($value['acl'] & BITWISE_OWNER) {
				echo _t('블로그 소유자');
			} else {
?>										
														<input type="checkbox" onclick="changeACL('admin',<?php echo $value['userid']; ?>,this.checked?'1':'0');" <?php echo( ($value['acl'] & BITWISE_ADMINISTRATOR) ? 'checked="checked"' : '');?> /><?php echo _t('관리자');?>
														<input type="checkbox" onclick="changeACL('editor',<?php echo $value['userid']; ?>,this.checked?'1':'0');" <?php echo( ($value['acl'] & BITWISE_EDITOR) ? 'checked="checked"' : '');?> /><?php echo _t('글관리');?>
<?php
			}
?>
													</td>
													<td class="exclude">
<?php
			if($value['acl'] & BITWISE_OWNER) {
?>													
													<span class="text"><?php echo _t('제외할 수 없습니다');?></span>
<?php
			} else {
?>
													<a class="cancel-button button" href="#void" onclick="deleteUser(<?php echo $value['userid'];?>,1);return false;" title="<?php echo _t('이 사용자를 팀블로그에서 제외합니다.');?>"><span class="text"><?php echo _t('사용자 제외');?></span></a>
<?php
			}
?>
													</td>
												</tr>
<?php
			$count++;
		}
	}

?>
											</tbody>
										</table>
							</div>
						</div>
<?php
if( Acl::check('group.owners')) {
	$urlRule=getBlogURLRule();
?>
						<div id="part-setting-invite" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('새로운 사람을 블로그의 필진으로 초대합니다');?></span></h2>
							
<?php if( !function_exists( 'mail' ) && !getServiceSetting( 'useCustomSMTP', 0 )  ) { ?>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('시스템에 자체에서 메일을 보낼 수가 없습니다. 외부 메일 서버를 지정해주세요.');?> <a href="<?php echo $blogURL ?>/owner/control/server"><?php echo _t('메일 서버 설정 바로가기')?></a></p>
							</div>
<?php } else { ?>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('새로운 사람을 블로그의 필자로 초대합니다. 초대장은 이메일을 통하여 발송됩니다. 메일 주소가 이미 블로그 서비스에 등록되어 있는 경우, 그 사용자에게 이 블로그의 필진 권한을 추가합니다. 사용자가 존재하지 않는 경우에는 새로운 사용자를 자동으로 등록하고 초대장을 발송합니다. 초대된 사람에게는 기본적으로 글쓰기 권한이 부여됩니다.');?></p>
							</div>
							<div class="data-inbox">
								<form id="letter-section" class="section" method="post" action="<?php	echo $blogURL;?>/owner/network/teamblog/invite">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('초대장');?></span></dt>
										<dd id="letter">
											<div id="letter-head">
												<div id="receiver-line" class="line">
													<label for="invitation_receiver"><?php echo _t('받는 사람'); ?></label>
													<input type="text" id="invitation_receiver" class="input-text" name="text" value="<?php	echo _t('이름&lt;이메일&gt; 혹은 이메일');?>" onclick="if(!this.selected) this.select();this.selected=true;" onblur="this.selected=false;" onkeydown="refreshReceiver(event)" />
												</div>
											</div>
														
											<div id="letter-body">
												<label for="invitation_comment"><?php echo _t('초대 메시지');?></label>
												<textarea id="invitation_comment" cols="60" rows="3" name="textarea"><?php echo _f("%1님께서 블로그의 팀원으로 초대합니다",htmlspecialchars($user['name']));?></textarea>
											</div>
											
											<div id="letter-foot">
												<div id="sender-line" class="line">
													<label for="invitation_sender"><?php echo _t('보내는 사람');?></label>
													<input type="text" id="invitation_sender" class="input-text" name="text2" value="<?php	echo htmlspecialchars(htmlspecialchars($user['name']).'<'.User::getEmail().'>');?>" />
												</div>
											</div>
										</dd>
									</dl>
									<div class="button-box">
										<input type="submit" class="input-button" value="<?php	echo _t('초대장 발송');?>" onclick="sendInvitation(); return false;" />
									</div>
								</form>
<?php } ?>
							</div>
						</div>

<?php
	}
require ROOT . '/interface/common/owner/footer.php';
?>
