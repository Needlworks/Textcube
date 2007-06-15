<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
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

<?php
if($owner == $_SESSION['admin']){?>
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
											temp = "<?php	echo _t('%1은 올바른 이메일이 아닙니다.');?>\n\n";
											errorStr += temp.replace('%1', '"' + email + '"');
											continue;
										}
										identy = '('+( (name == undefined || name == '')  ? email : name)+')';
										receivers[i] = new Array(name, email);
									}
										return receivers;
								}
								
								function clearPASS(){
									var password = document.getElementById('invite_password');
									password.value = '';
								}
								
								function sendInvitation() {
									var receiver = document.getElementById('invitation_receiver');
									var password = document.getElementById('invite_password');
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
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/invite/");
									request.onVerify = function() {
										return this.getText("/response/error") == 15;
									}
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('초대장을 발송했습니다.');?>", "center", "bottom");
										window.location.href='<?php	echo $blogURL;?>/owner/setting/teamblog/';
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
									}
									request.send("&senderName="+encodeURIComponent(sender[0][0])+"&senderEmail="+encodeURIComponent(sender[0][1])+"&email="+inviteList[0][1]+"&name="+encodeURIComponent(inviteList[0][0])+"&password="+password.value+"&comment="+encodeURIComponent(comment.value));
								}
								
								function createBlogIdentify(receivers) {
									var blogList = document.getElementById('blogList');
									
									for (var name in receivers) {
										target = document.getElementById(name);
										if (target != null) continue;
											blogList.innerHTML += receivers[name][2];
									}
								}
								
								function cancelInvite(userid) {
									if(!confirm('<?php	echo _t('삭제하시겠습니까?');?>')) return false;
									var request = new HTTPRequest("POST", "<?php	echo $blogURL;?>/owner/setting/teamblog/cancelInvite/");
									request.onSuccess = function() {
										window.location.href="<?php 	echo $blogURL;?>/owner/setting/teamblog";
									}
									request.onError = function() {
										alert('<?php 	echo _t('실패했습니다.');?>');
									}
									request.send("userid=" + userid);
								}
								
								function deleteUser(userid, atype) {
									if(atype == 1){
										if(!confirm('<?php	echo _t('선택된 사용자를 삭제합니다.\n삭제되는 사용자가 쓴 글은 전부 관리자의 글로 변환됩니다.\n개인블로그가 설정되어있으면 개인블로그가 폐쇄됩니다.\n(글이 전부 삭제되는것은 아니고 팀블로그의 로그인데이터만 삭제됩니다)\n\n\n잘못된 삭제는 복원이 어렵습니다. 정말 삭제하시겠습니까?');?>')) return false;
									} else {
										if(!confirm('<?php	echo _t('삭제 하시겠습니까?');?>')) 
											return false;
									}
									var request = new HTTPRequest("POST", "<?php	echo $blogURL;?>/owner/setting/teamblog/deleteUser/");
									request.onSuccess = function() {
										window.location.href="<?php 	echo $blogURL;?>/owner/setting/teamblog";
									}
									request.onError = function() {
										alert('<?php 	echo _t('실패했습니다.');?>');
									}
									request.send("userid=" + userid);
								}

								function teamblog_admin(stype, userid, checked) {

									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/isAdmin/");
									request.onSuccess = function() {
										PM.showMessage("<?php	echo _t('설정을 변경했습니다.');?>", "center", "bottom");
									}
									request.onError = function() {
										alert('<?php 	echo _t('실패했습니다.');?>');
									}
									request.send("stype=" + stype + "&userid=" + userid + "&switch=" + checked);
								}
								
								var CHCrev=false;
								function Check_rev(){
									if(CHCrev == false) CHCrev = true;
									else CHCrev = false;

									for(var chr=0;;chr++){
										if(document.getElementById('check_'+chr)){
											document.getElementById('check_'+chr).checked = CHCrev;
										}
										else{
											break;
										}
							
									}
								}
								function deleteSelectedUsers(auser){
									if(!confirm('<?php	echo _t('선택된 사용자들을 정말 삭제하시겠습니까?\n삭제되는 기존사용자의 글은 전부 관리자의 글로 변환됩니다.\n개인블로그가 설정되어있으면 개인블로그가 폐쇄됩니다.\n(글이 전부 삭제되는것은 아니고 팀블로그의 로그인데이터만 삭제됩니다)\n잘못된 삭제는 복원이 어렵습니다. 정말 삭제하시겠습니까?');?>')) return false;
									PM.showMessage("<?php	echo _t('삭제 중입니다. 잠시만 기다려주세요.');?>", "center", "middle");
									var mysend = 0;
									var mycheck = 0;
									for(var chr=0; chr< auser ;chr++){
										if(document.getElementById('check_'+chr).checked == true){
											mycheck++;
											var users = document.getElementById('chh'+chr).value
											var type = document.getElementById('cht'+chr).value
											if(type == 0){
												var request = new HTTPRequest("POST", "<?php	echo $blogURL;?>/owner/setting/teamblog/cancelInvite/");
												request.onSuccess = function() {
													mysend--;
													if(mysend == 0) window.location.href="<?php	echo $blogURL;?>/owner/setting/teamblog";
												}
												request.onError = function() {
													mysend--;
													if(mysend == 0) window.location.href="<?php	echo $blogURL;?>/owner/setting/teamblog";
												}
												request.send("userid=" + users);
												mysend++;
											} else {
												var request = new HTTPRequest("POST", "<?php	echo $blogURL;?>/owner/setting/teamblog/deleteUser/");
												request.onSuccess = function() {
													mysend--;
													if(mysend == 0) window.location.href="<?php	echo $blogURL;?>/owner/setting/teamblog";
												}
												request.onError = function() {
													mysend--;
													if(mysend == 0) window.location.href="<?php	echo $blogURL;?>/owner/setting/teamblog";
												}
												request.send("userid=" + users);
												mysend++;
											}
										}					
									}
									if(mycheck == 0) alert("선택된 사용자가 없습니다.");
								}

								function buttonchange(){
									if( document.getElementById('target-role').value == 'delete'){
										document.getElementById('apply-button').value = '<?php echo _t('탈퇴');?>';
									} else {
										document.getElementById('apply-button').value = '<?php echo _t('적용');?>';
									}
								}
<?php
}
?>
						//]]>
						</script>

						<div id="part-setting-account" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('팀블로그를 관리합니다');?></span></h2>
							<div id="list-section" class="section">
								<table cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="status"><input type="checkbox" name="Aclick" onclick="Check_rev()"></th>
											<th class="acl"><span class="text"><?php echo _t('권한 ');?></span></th>
											<th class="name"><span class="text"><?php echo _t('이름 ');?></span></th>
											<th class="blog"><span class="text"><?php echo _t('대표 블로그');?></span></th>											
											<th class="email"><span class="text"><?php echo _t('이메일');?></span></th>
											<th class="date"><span class="text"><?php echo _t('가입일');?></span></th>
											<th class="date"><span class="text"><?php echo _t('작성한 글 수');?></span></th>
											<th class="cancel"><span class="text"><?php	echo _t('초대취소');?></span></th>
											<th class="status"><span class="text"><?php echo _t('권한');?></span></th>
											<th class="status"><span class="text"><?php	echo _t('계정삭제');?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
	$teamblog_owner = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Teamblog 
			WHERE userid='".$owner."' 
				AND teams='".$owner."'");
	$teamblog_user = DBQuery::queryRow("SELECT a.*, b.name 
			FROM {$database['prefix']}Teamblog a, 
				{$database['prefix']}Users b  
			WHERE a.userid = '".$_SESSION['admin']."' 
				AND a.teams = '".$_SESSION['userid']."' 
				AND b.userid = a.userid");
	$invited_user = DBQuery::queryAll("SELECT t.*, u.* 
		FROM {$database['prefix']}Teamblog t, 
		 	{$database['prefix']}Users u 
		WHERE t.teams = '$owner' 
			AND u.userid = t.userid 
			AND t.userid != '$owner'
		ORDER BY u.created DESC"); 

	$count=0;

	if(isset($invited_user)) {
		foreach($invited_user as $value) {
			$value['posting'] = DBQuery::queryCell("SELECT count(*) FROM {$database['prefix']}TeamEntryRelations where team = {$_SESSION['admin']}");
			$value['admin'] = Acl::check('group.administrators');
			$value['editor'] = Acl::check('group.editors');
			$className= ($count%2)==1 ? 'even-line' : 'odd-line';
			$className.=($count==sizeof($invited_user)-1) ? ' last-line':'';
?>
												<tr class="<?php echo $className;?> inactive-class">
													<td class="status"><input type="checkbox" id="check_<?php echo $count; ?>"><input type="hidden" name="chh<?php echo $count; ?>" value="<?php echo $value['userid']; ?>"><input type="hidden" name="cht<?php echo $count; ?>" value="<?php if($value['last'] == '0' && $value['lastLogin'] =='0') echo "0"; else echo "1"; ?>"></td>
													<td class="acl">test</td>
													<td class="name"><?php echo $value['name'];?></td>
													<td class="blog">test</td>
													<td class="email"><?php		echo  htmlspecialchars($value['loginid']);?></td>
													<td class="date"><?php echo Timestamp::format5($value['created']);?></td>
													<td class="posting"><?php echo $value['posting'];?></td>
<?php
			if($value['lastLogin'] == 0) { 
?>
													<td class="cancel"><a class="cancel-button button" href="#void" onclick="cancelInvite(<?php	echo $value['userid'];?>);return false;" title="<?php echo _t('초대에 응하지 않은 사용자의 계정을 삭제합니다.');?>"><span class="text"><?php echo _t('초대취소');?></span></a></td>
<?php
			} else { 
?>
													<td class="status"></td>
<?php
			}
?>
													<td class="password">
														<input type="checkbox" onclick="teamblog_admin('admin',<?php echo $value['userid']; ?>,this.checked?'1':'0');" <?php echo(!empty($value['admin']) ? "checked" : "");?>><?php echo _t('관리자');?><br />
														<input type="checkbox" onclick="teamblog_admin('editor',<?php echo $value['userid']; ?>,this.checked?'1':'0');" <?php echo(!empty($value['editor']) ? "checked" : "");?> ><?php echo _t('글관리');?>
													</td>
													<td class="cancel">
														<a class="cancel-button button" href="#void" onclick="deleteUser(<?php	echo $value['userid'];?>,1);return false;" title="<?php echo _t('현재 사용자를 팀블로그에서 제외합니다.');?>"><span class="text"><?php echo _t('계정삭제');?></span></a>
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
							
							<div id="role-action" class="part" >
								<span class="text" >선택한 구성원을 </span>
								<select name="t-role" id="target-role" onchange="buttonchange();" >
									<option value=''>행동을 지정합니다</option>
									<optgroup label="다음 권한으로 변경합니다">
										<option value='manager'>관리자</option>
										<option value='editor'>편집자</option>
										<option value='writer'>필자</option>
									</optgroup>
									<optgroup label="탈퇴 처리 합니다">
										<option value='delete'>탈퇴</option>
									</optgroup>
								</select>
								<input type="submit" id="apply-button" class="apply-button input-button" value="적용"  />
							</div>
						</div>

						
						
						
						
<?php
if($owner == $_SESSION['admin'] && empty($enduser)) {
	$urlRule=getBlogURLRule();
?>
						<div id="part-setting-invite" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('친구를 팀원으로 초대합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="letter-section" class="section" method="post" action="<?php	echo $blogURL;?>/owner/setting/teamblog/Invite">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('초대장');?></span></dt>
										<dd id="letter">
											<div id="letter-head">
												<div id="receiver-line" class="line">
													<label for="invitation_receiver"><?php	echo _t('받는 사람'); ?> (<?php echo _t('이메일의 @ 앞부분이 블로그 식별자로 사용됩니다.');?>)</label>
													<input type="text" id="invitation_receiver" class="input-text" name="text" value="<?php	echo _t('이름&lt;이메일&gt; 혹은 이메일');?>" onclick="if(!this.selected) this.select();this.selected=true;" onblur="this.selected=false;" onkeydown="refreshReceiver(event)" />
												</div>
												<div id="blog-address-line" class="line">
													<label for="invite_password" onclick="toggleLayer('teamblog_pass');clearPASS();"><?php	echo _t('패스워드 지정');?></label>
													<div id="teamblog_pass" style="display:none;">
													<input type="password" id="invite_password" class="input-text" name="text" disable/>
													</div>
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
						</div>
				</div>
				</div>
			</div>
<?php
	}
require ROOT . '/lib/piece/owner/footer.php';
?>
