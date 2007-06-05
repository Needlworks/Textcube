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
								
								function Trim(sInString) {
									sInString = sInString.replace( /^\s+/g, "" );// strip leading
									return sInString.replace( /\s+$/g, "" );// strip trailing
								}
								
								function saveName() {

									try{
										var Astyle = document.getElementById('admin_style');
										var Apos = document.getElementById('stylePos');
										var Ais_style = document.getElementById('is_style');
										
										var Sbold = document.getElementById('font_bold');
										var Sitalic = document.getElementById('font_i');
										var Ssize = document.getElementById('font_size');
										var Scolor = document.getElementById('font_color');

										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/nameStyle/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('저장하지 못했습니다.');?>");
										}
										
										var SendValue;
										SendValue = "&bold=" + encodeURIComponent(Sbold.checked);
										SendValue += "&italic=" + encodeURIComponent(Sitalic.checked);
										SendValue += "&size=" + encodeURIComponent(Ssize.value);
										SendValue += "&color=" + encodeURIComponent(Scolor.value);
										
										if(Astyle){
											SendValue += "&style=" + encodeURIComponent(Astyle.checked);
										}
										if(Apos){
											SendValue += "&pos=" + encodeURIComponent(Apos.value);
										}
										if(Ais_style){
											SendValue += "&is_style=" + encodeURIComponent(Ais_style.checked);
										}
										
										request.send(SendValue);
									}catch(e){
									}
								}
								
								function saveProfile() {
									var profile = document.getElementById('teamblogUserProfile');

									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/profileText/");
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('변경하지 못했습니다.');?>");
									}
									request.send("teamblogUserProfile=" + encodeURIComponent(profile.value));
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
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/Invite/");
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
										if(!confirm('<?php	echo _t('선택된 사용자를 삭제합니다.\n\n삭제되는 사용자가 쓴글은 전부 관리자의 글로 변환됩니다.\n\n개인블로가 설정되어있으면 개인블로그가 폐쇠됩니다.\n(글이 전부 삭제되는것은 아니고 팀블로그의 로그인데이터만 삭제됩니다)\n\n\n잘못된 삭제는 복원이 어렵습니다. 정말 삭제하시겠습니까?');?>')) return false;
									}
									else{
										if(!confirm('<?php	echo _t('삭제하시겠습니까?');?>')) return false;
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
								
								
								function teamblog_admin(stype, userid) {
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/teamblog/isAdmin/");
									request.onSuccess = function() {
										PM.showMessage("<?php	echo _t('설정을 변경했습니다.');?>", "center", "bottom");
									}
									request.onError = function() {
										alert('<?php 	echo _t('실패했습니다.');?>');
									}
									request.send("stype=" + stype + "&userid=" + userid);
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
									if(!confirm('<?php	echo _t('선택된 사용자들을 정말 삭제하시겠습니까?\n\n삭제되는 기존사용자의 글은 전부 관리자의 글로 변환됩니다.\n\n개인블로가 설정되어있으면 개인블로그가 폐쇠됩니다.\n(글이 전부 삭제되는것은 아니고 팀블로그의 로그인데이터만 삭제됩니다)\n\n\n잘못된 삭제는 복원이 어렵습니다. 정말 삭제하시겠습니까?');?>')) return false;
									PM.showMessage("<?php	echo _t('삭제중입니다. 잠시만 기다려주세요.');?>", "center", "middle");
									var mysend = 0;
									var mycheck = 0;
									for(var chr=0; chr<auser ;chr++){
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
											}
											else{
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
<?php
}?>							//]]>
						</script>


<?php
	$teamblog_owner = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Teamblog 
			WHERE userid='".$owner."' AND teams='".$owner."'");
	$teamblog_user = DBQuery::queryRow("SELECT a.*, b.name 
			FROM {$database['prefix']}Teamblog a, {$database['prefix']}Users b  
			WHERE a.userid = '".$_SESSION['admin']."' AND a.teams = '".$_SESSION['userid']."' AND b.userid = a.userid");
   
	// 비트 연산을 통한 변수 추출

	// 1번째 비트가 0인지 1인지, 0이면 이름 스타일 사용 1이면 사용하지 않고 블로그 스타일을 따라감
	$is_style = $teamblog_owner['font_style'] & 1;
   
	// 2번째 비트가 0인지 1인지, 0이면 사용자마음대로 이름 표시 1이면 관리자만 이름스타일 변경 가능
	$font_style = $teamblog_owner['font_style'] & 2;
   
	$font_bold = $teamblog_user['font_bold'] & 1;
	$font_i = $teamblog_user['font_bold'] & 2;
	$Fstyle = 'style="';
	if(!empty($font_bold)) $Fstyle .= 'font-Weight:Bold;';
	if(!empty($font_i)) $Fstyle .= 'font-Style:italic;';
	$Fstyle .= 'font-Size:'.$teamblog_user['font_size'].'pt;color:'.$teamblog_user['font_color'].';"';
   
	$NAmestyle1 = 1;
	$NAmestyle2 = 0;
	$NAmestyle3 = 0;
	$NAmestyle4 = 0;
   
	// 3번째 비트가 0인지 1인지, 0이면 이름스타일 표시, 1이면 팀원의 이름을 표시하지 않음
	$isname = $teamblog_owner['font_style'] & 4;
	if(empty($isname)) {
		$NAmestyle1 = 0;
		$name_pos = $teamblog_owner['font_style'] & 16;
		if(empty($name_pos)){
		// 4번째 비트가 0인지 1인지, 0 이면 시간옆에 이름 표시 1이면 제목옆에 이름표시
		$name_pos = $teamblog_owner['font_style'] & 8;
			if(!empty($name_pos)) $NAmestyle2 = 1;
			else $NAmestyle3 = 1;
		} else {
			$NAmestyle4 = 1;
		}
	}
   
   
	// Profile 설정

	$logo = $teamblog_user['logo'];
  
	if(empty($logo)){
	  	$logo = $service['path'] . '/image/spacer.gif';
	  	$profile_x = 92;
	  	$profile_y = 93;
	} else {
		$logo = $service['path'] . '/attach/1/teamProfileImages/' . $logo;
		$img = getimagesize('../../../../attach/1/teamProfileImages/' . $logo);
		$profile_x = $img[0];
		$profile_y = $img[1];
	  	if($profile_y > 93){
			$profile_x = intval($profile_x * 93 / $profile_y);
			$profile_y = 93;
		}
	}
  
	$enduser = $teamblog_owner['enduser'];
  
	$profile = $teamblog_user['profile'];
	$profile = str_replace("<br>", "\n", $profile);
?>


						<div id="part-setting-account" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('팀블로그를 관리합니다');?></span></h2>
			
			

<?php if((empty($is_style) && empty($font_style) && empty($isname)) || $owner == $_SESSION['admin']){ ?>

<script type="text/javascript">
<!--
	function style_Bold(){
		var myName = document.getElementById('nameStyle');
		var b_Name = document.getElementById('font_bold');
		if(myName.style.fontWeight){
			myName.style.fontWeight = '';
			b_Name.checked = false;
		}
		else{
			myName.style.fontWeight = 'bold';
			b_Name.checked = true;
		}
	}

	function style_Italic(){
		var myName = document.getElementById('nameStyle');
		var i_Name = document.getElementById('font_i');
		if(myName.style.fontStyle){
			myName.style.fontStyle = '';
			i_Name.checked = false;
		}
		else{
			myName.style.fontStyle = 'italic';
			i_Name.checked = true;
		}
	}
	
	function style_Size(){
		var myName = document.getElementById('nameStyle');
		var s_Name = document.getElementById('font_size');
		
		if(s_Name.value == 0 || !s_Name.value){
			myName.style.fontSize = '10pt';
		}
		else{
		  myName.style.fontSize = s_Name.value + 'pt';
		}
	}
	
	function style_Color(){
		var myName = document.getElementById('nameStyle');
		var s_Name = document.getElementById('font_color');
		var c_Name = document.getElementById('sfont_color');
		s_Name.value = c_Name.value;
		myName.style.color = c_Name.value;
	}
	
	function style_ColorZ(){
		var myName = document.getElementById('nameStyle');
		var c_Name = document.getElementById('font_color');
		myName.style.color = c_Name.value;
	}
	
-->
</script>


							<div class="data-inbox">
								<form class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/teamblog/">
									<fieldset class="container">
										<legend><?php echo _t('나의 이름 스타일');?></legend>
										
										<dl id="blogger-name-line" class="line">
											<dt><label for="nickname"><?php echo _t('나의 이름 스타일');?></label></dt>
											<dd>
											  <span id="nameStyle" <?php echo $Fstyle; ?>>by <?php echo _t("$teamblog_user[name]"); ?></span>
											  <?php if($owner == $_SESSION['admin']) { ?>
											  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select id="stylePos">
											  	<option value="1" <?php if(!empty($NAmestyle1)) echo "SELECTED"; ?>><?php echo _t('블로그에 이름을 표시하지 않습니다'); ?></option>
											  	<option value="2" <?php if(!empty($NAmestyle2)) echo "SELECTED"; ?>><?php echo _t('글 제목 옆에 이름을 표시합니다'); ?></option>
											  	<option value="3" <?php if(!empty($NAmestyle3)) echo "SELECTED"; ?>><?php echo _t('시간 옆에 이름을 표시합니다'); ?></option>
											  	<option value="4" <?php if(!empty($NAmestyle4)) echo "SELECTED"; ?>><?php echo _t('치환자를 이용하여 이름을 표시합니다.'); ?></option>
											  </select>
											<?php } ?>
											</dd>
										</dl>
										<dl id="blogger-email-line" class="line">
											<?php if($owner == $_SESSION['admin']) { ?>
											<dt><input type=checkbox id="is_style" <?php if(!empty($is_style)) echo 'checked'; ?> />사용안함</dt>
											<?php } ?>
											<dd>
												<span onclick="style_Bold();" style="cursor:pointer;"><input type="checkbox" id="font_bold" <?php if(!empty($font_bold)) echo 'checked'; ?> /> <b>굵게</b></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											  <span onclick="style_Italic();" style="cursor:pointer;"><input type="checkbox" id="font_i" <?php if(!empty($font_i)) echo 'checked'; ?> /> <i>기울임</i></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												글자 크기 <input type="text" id="font_size" size="3" value="<?php echo $teamblog_user['font_size']; ?>" onchange="style_Size();" onkeydown="if(event.keyCode == 13) style_Size();" /> pt&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												글자 색상 <input type="text" id="font_color" size="7" value="<?php echo $teamblog_user['font_color']; ?>"  onchange="style_ColorZ();" onkeydown="if(event.keyCode == 13) style_ColorZ();" />
												<select id="sfont_color" Onchange="style_Color()">
												<?php
                          $CH_Color = Array("#ff0000", "#ffff00", "#00ff00", "#00ffff", "#0000ff", "#ff00ff", "#808080", "#c0c0c0", "#ffc0c0", "#ffffc0", "#c0ffc0", "#c0ffff", "#ffc0ff", "#000000", "#FFFFFF");
                          $ci = count($CH_Color);
                          $is_sel = 0;
                          for($i=0; $i<$ci; $i++){
                            $teamblog_color_Style = "";
                            if($teamblog_user['font_color'] == $CH_Color[$i]){ $teamblog_color_Style = "SELECTED"; $is_sel = 1; }
                               $style_color = $CH_Color[$i]; $style_block = "■";
                               if($CH_Color[$i] == "#FFFFFF") { $style_color = "#000000"; $style_block = "　"; }
                                  echo "<option  value=\"$CH_Color[$i]\" style=\"color:$style_color;\" $teamblog_color_Style>$style_block $CH_Color[$i]</option>";
                          }
                          if(empty($is_sel)) echo '<option  value="'.$teamblog_user['font_color'].'" style="color:'.$teamblog_user[font_color].';" SELECTED>■ '.$teamblog_user['font_color'].'</option>';
                        ?>
                        </select>
                        <?php if($owner == $_SESSION['admin']) { ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" id="admin_style" <?php if(!empty($font_style)) echo 'CHECKED'; ?> /><?php echo _t('(팀원의 스타일 변경 금지)'); ?>
                        <?php } ?>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="saveName(); return false;" />
									</div>
								</form>
<?php } ?>

								
								<hr class="hidden" />
								
								<form id="account-section" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/account">
									<fieldset class="container">
										<legend><?php echo _t('프로필 변경');?></legend>
										
										<dl id="current-password-line" class="line">
											<dt><label for="prevPwd"><?php echo _t('나의 프로필');?></label></dt>
											<dd>
											  <img id="logo" width="<?php echo $profile_x; ?>" height="<?php echo $profile_y; ?>" style="border-style:solid; border-width:1px; border-color:#404040" src="<?php echo $logo; ?>" alt="" />
											  <iframe src="<?=$blogURL?>/owner/setting/teamblog/profileImage/index.php" style="margin:opx; padding:0px;display:block; border-color:#FFFFFF\" frameborder="0" scrolling="no" width="400" height="30"></iframe> <?php echo _t('(찾아보기를 이용해서 이미지 선택시 바로 이미지가 변경, 저장됩니다)'); ?>
											</dd>
										</dl>
										<dl id="new-password1-line" class="line">
											<dt><label></label></dt>
											<dd><textarea id="teamblogUserProfile" cols="80" rows="6"><?php echo $profile; ?></textarea></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('변경하기');?>" onclick="saveProfile(); return false;" />
									</div>
								</form>
							</div>
						</div>
						
<?php
if($owner == $_SESSION['admin'] && empty($enduser)){
	$urlRule=getBlogURLRule();?>
						<div id="part-setting-invite" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('친구를 팀원으로 초대합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="letter-section" class="section" method="post" action="<?php	echo $blogURL;?>/owner/setting/teamblog/Invite">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('초대장');?></span></dt>
										<dd id="letter">
											<div id="letter-head">
												<div id="receiver-line" class="line">
													<label for="invitation_receiver"><?php	echo _t('받는 사람');?></label>
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
												<textarea id="invitation_comment" cols="60" rows="30" name="textarea"><?php	echo htmlspecialchars(htmlspecialchars($user['name'])) . _t("님께서 블로그의 팀원으로 초대합니다");?></textarea>
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
								
								<div id="list-section" class="section">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('팀원 명단');?></span></dt>
										<dd>
<?php
 $invitedList = DBQuery::queryAll("SELECT a.*, b.* 
		FROM {$database['prefix']}Teamblog a, 
		 	{$database['prefix']}Users b 
		WHERE teams = '$owner' 
			AND b.userid = a.userid 
			AND a.userid != '$owner'
		ORDER BY b.created DESC"); 
?>
											<table cellspacing="0" cellpadding="0">
												<thead>
													<tr>
														<th class="status"><input type="checkbox" name="Aclick" onclick="Check_rev()"></th>
														<th class="email"><span class="text"><?php echo _t('이름 (e-mail)');?></span></th>
														<th class="date"><span class="text"><?php echo _t('초대일');?></span></th>
														<th class="status"><span class="text"><?php	echo _t('경과');?></span></th>
														<th class="password"><span class="text"><?php echo _t('비밀번호');?> / <?php echo _t('권한 관리');?></span></th>
														<th class="cancel"><span class="text"><?php	echo _t('초대취소');?></span></th>
													</tr>
												</thead>
												<tbody>
<?php
	$count=0;
	if(isset($invitedList)) {
		foreach($invitedList as $value) {
			$className=($count%2)==1?'even-line':'odd-line';
			$className.=($count==sizeof($invitedList)-1)?' last-line':'';
?>
													<tr class="<?php echo $className;?> inactive-class">
														<td class="status"><input type="checkbox" id="check_<?php echo $count; ?>"><input type="hidden" name="chh<?php echo $count; ?>" value="<?php echo $value['userid']; ?>"><input type="hidden" name="cht<?php echo $count; ?>" value="<?php if($value['last'] == '0' && $value['lastLogin'] =='0') echo "0"; else echo "1"; ?>"></td>
														<td class="email"><?php		echo htmlspecialchars($value['name']);?>(<?php echo htmlspecialchars($value['loginid']);?>)</td>
														<td class="date"><?php echo Timestamp::format5($value['create']);?></td>
<?php
			if($value['lastLogin'] == 0) {
?>
														<td class="status"><?php echo _f('%1 전',timeInterval($value['created'],time()));?></td>
														<td class="password"><?php echo DBQuery::queryCell("SELECT password FROM {$database['prefix']}Users WHERE userid = {$value['userid']} AND host = $owner AND lastLogin = 0");?></td>
														<?php if($value['lastLogin'] == 0){ ?><td class="cancel"><a class="cancel-button button" href="#void" onclick="cancelInvite(<?php	echo $value['userid'];?>);return false;" title="<?php echo _t('초대에 응하지 않은 사용자의 계정을 삭제합니다.');?>"><span class="text"><?php echo _t('초대취소');?></span></a></td>
														<?php } else{ ?><td class="cancel"><a class="cancel-button button" href="#void" onclick="deleteUser(<?php	echo $value['userid'];?>,0);return false;" title="<?php echo _t('초대에 응하지 않은 사용자의 계정을 삭제합니다.');?>"><span class="text"><?php echo _t('초대취소');?></span></a></td>
														<?php } ?>
<?php
			} else {
				$pblog = $value['enduser'] - $value['userid'];
				if($pblog == 1) $sblog = ($value['enduser']-1) & $value['userid'];
				else if($pblog == 0)	$sblog = $value['enduser'] & $value['userid'];
				else $sblog = 0;
		
				if($value['userid'] == 1){
					$pblog = 0;
					$sblog = 0;	
				}
?>
														<td class="status"></td>
														<td class="password">
															<input type="checkbox" onclick="teamblog_admin('1',<?php echo $value['userid'];?>);" <?php echo(!empty($value['admin']) ? "checked" : "");?>><?php echo _t('관리자');?>
															<input type="checkbox" onclick="teamblog_admin('2',<?php echo $value['userid'];?>);" <?php echo(!empty($value['posting']) ? "checked" : "");?> ><?php echo _t('글관리');?>
<?php 
				if(!empty($sblog) && ($service['type']!='single')) {
?>
															<input type="checkbox" onclick="teamblog_admin('3',<?php echo $value['userid'];?>);" <?php echo( $pblog==1 ? "checked" : "");?>><?php echo _t('개인블로그');?>
<?php
				}
?>
														</td>
														<td class="cancel"><a class="cancel-button button" href="#void" onclick="deleteUser(<?php	echo $value['userid'];?>,1);return false;" title="<?php echo _t('현재 사용자를 팀블로그에서 제외합니다.');?>"><span class="text"><?php echo _t('계정삭제');?></span></a></td>
<?php
			} 
?>
													</tr>
<?php
			$count++;
			}
		}
	}

?>
												</tbody>
												<?php if($count){ ?>
												<tr>
													<td colspan="6"><input type="button" value="<?php echo _t('선택된 사용자 삭제');?>" onclick="deleteSelectedUsers(<?php echo $count;?>)";></td>
												</tr>
												<?php } ?>
											</table>
										</dd>
									</dl>
								</div>
							</form>
						</div>
				</div>
				</div>
			</div>

<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
