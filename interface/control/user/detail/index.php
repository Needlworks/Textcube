<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$service['admin_script']='control.js';

require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/control/header.php';

requirePrivilege('group.creators');
global $database;

$uid = $suri['id'];

$usersetting= POD::queryRow("SELECT * FROM {$database['prefix']}Users WHERE userid = " . $uid);
$usersetting['owner']= POD::queryCell("SELECT userid FROM {$database['prefix']}Privileges WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $blogid);
$AuthToken = Setting::getUserSettingGlobal('AuthToken',null,$uid);
?>
						<script type="text/javascript"> 
						//<![CDATA[

						function makeToken(uid) {
							var request = new HTTPRequest("<?php echo $blogURL;?>/control/action/user/makeToken/?userid="+uid);
							request.onSuccess = function() {
								alert("<?php echo _t('임시 암호가 설정되었습니다.');?>");
								window.location.reload();
							}
							request.onError = function() {
								msg = this.getText("/response/result");
								alert("<?php echo _t('임시 암호가 설정되지 못했습니다.');?>\r\nError : " + msg);
							}
							request.send();
						}
						</script>

						<div id="part-user-about" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('사용자 정보');?></span></h2>
							
							<div id="team-user-about" class="container">
								<h3><?php echo $usersetting['name'];?></h3>
								
								<div class="main-explain-box">
									<p class="explain"><?php echo $usersetting['loginid'];?></p>
								</div>
								
								<ul>
									<?php if(!is_null($AuthToken)) { ?>
										<li><em><?php echo _t("임시 암호가 설정되어 있습니다.");?></em></li>
										<li><em><?php echo $AuthToken;?></em></li>
									<?php }?>
									<li><?php echo _f('이 계정은 %1에 생성되었습니다.', date("D M j G:i:s T Y", $usersetting['created']));?></li>
                                </ul>
							</div>
							
							<!--div id="developer-description" class="section">
								<h3><span class="text"><?php echo _t('팀블로그');?></span></h3>
								
								<div id="maintainer-container" class="container">
									<h4><span class="text"><?php echo _t('Action');?></span></h4>
									
									<table>
										<colgroup>
											<col class="name"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('사용자');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
	<?php echo "<td class=\"name\"></td>";?>
											</tr>
												<td></td>
											<tr>
											</tr>
										</tbody>
									</table>
								</div>
							</div -->
							
							<div id="team-joined-list" class="container">
								<h4><span class="text"><?php echo _t('참여중인 블로그');?></span></h4>
								
								<table class="data-inbox">
									<thead>
										<tr>
											<th class="name"><?php echo _t('블로그');?></th>
											<th class="role"><?php echo _t('권한 그룹');?></th>
										</tr>
									</thead>
									<tbody><?php
$teamblog = POD::queryAll("SELECT * FROM `{$database['prefix']}Privileges` WHERE userid = " . $uid);
	foreach ($teamblog as $row){
		echo "<tr>";
		echo "<td class=\"name\"><a href=\"{$blogURL}/control/blog/detail/{$row['blogid']}\">".POD::queryCell("SELECT value FROM `{$database['prefix']}BlogSettings` WHERE name = 'name' AND blogid = " . $row['blogid'])."</a></td>";

		$tmpstr = '';
		if ($row['acl'] & BITWISE_ADMINISTRATOR) $tmpstr .= _t("관리자")." ";
		if ($row['acl'] & BITWISE_OWNER) $tmpstr .= _t("소유자")." ";
		if ($row['acl'] & BITWISE_EDITOR) $tmpstr .= _t("글관리")." ";
		$tmpstr = ($tmpstr?$tmpstr:_t("없음"));
		echo "<td class=\"role\">".$tmpstr."</td>";
		echo "</tr>";
	}
?>
									</tbody>
								</table>
							</div>
							
							<div class="button-box">
								<?php if (is_null($AuthToken)) { ?><a class="button" href="<?php echo $blogURL;?>/control/action/user/makeToken/?userid=<?php echo $uid;?>" onclick="makeToken('<?php echo $uid;?>'); return false;"><?php echo _t('임시 암호 설정');?></a><?php } ?>
								<a class="button" href="<?php echo $blogURL;?>/control/action/user/delete/?userid=<?php echo $uid;?>" onclick="cleanUser('<?php echo $uid;?>'); return false;"><?php echo _t('사용자 삭제');?></a>
								<a class="button" href="<?php echo $blogURL;?>/control/user"><?php echo _t('돌아가기');?></a>
							</div>
						</div>
<?php
require ROOT . '/interface/common/control/footer.php';
?>
