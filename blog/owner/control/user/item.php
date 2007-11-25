<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
require ROOT . '/lib/piece/owner/libForControl.php';
global $database;
$userid=$suri['id'];
$usersetting= DBQuery::queryRow("SELECT * FROM `{$database['prefix']}Users` WHERE userid = " . $userid);
$usersetting['owner']= DBQuery::queryCell("SELECT userid FROM `{$database['prefix']}teamblog` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $blogid);
?>
						<div id="part-center-about" class="part">
<a href="<?php echo $blogURL;?>/owner/control/user">&lt;&lt;돌아가기</a>
							<h2 class="caption"><span class="main-text"><?php echo _t('사용자 정보');?></span></h2>
						
							<h3>Brand yourself! : <?php echo $usersetting['name'];?></h3>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo $usersetting['loginid'];?></h3>
								</p>
								<div id="copyright">
                                    <?php echo _f('이 계정은 %1에 생성되었습니다.', date("D M j G:i:s T Y",$usersetting['created']));?><br/>
                                </div>
							</div>
							
							<div id="developer-description" class="section">
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
												<td class="name"><?php echo _t("로그인 아이디 변경");?></td>
											</tr>
											<tr>
												<td class="name"><?php echo _t("계정 삭제");?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="developer-container" class="container">
									<h4><span class="text"><?php echo _t('Team Blog');?></span></h4>
									<table>
										<colgroup>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('블로그');?></th>
												<th class="role"><?php echo _t('권한 그룹');?></th>
											</tr>
										</thead>
										<tbody><?php
$teamblog = DBQuery::queryAll("SELECT * FROM `{$database['prefix']}teamblog` WHERE userid = " . $userid);
	foreach ($teamblog as $row){
		echo "<tr>";
		echo "<td class=\"name\"><a href=\"{$blogURL}/owner/control/blog/{$row['blogid']}\">".DBQuery::queryCell("SELECT value FROM `{$database['prefix']}BlogSettings` WHERE name = 'name' AND blogid = " . $row['blogid'])."</a></td>";

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
								
							<div id="supporter-description" class="section">
								<h3><span class="text"><?php echo _t('OpenID');?></span></h3>
								<div id="tester-container" class="container">
								</div>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
