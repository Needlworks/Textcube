<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$service['admin_script']='control.js';

require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/control/header.php';

requirePrivilege('group.creators');
global $database;
$bid=$suri['id'];
$blogsetting = getBlogSettingGlobals($bid);
?>
						<script type="text/javascript"> 
						//<![CDATA[

						function setDefaultBlog(bid) {
							var request = new HTTPRequest("<?php echo $blogURL;?>/control/action/blog/setDefault/?blogid="+bid);
							request.onSuccess = function() {
								alert("<?php echo _t('대표 블로그가 설정되었습니다.');?>");
								window.location.reload();
							}
							request.onError = function() {
								msg = this.getText("/response/result");
								alert("<?php echo _t('대표 블로그가 설정되지 못했습니다.');?>\r\nError : " + msg);
							}
							request.send();
						}

						function deleteUser(userid, atype) {
							if(atype == 1) { // If there are posts from user.
								if(!confirm("<?php echo _t('선택된 사용자를 정말 삭제하시겠습니까?');?>\n\n<?php echo _t('삭제되는 기존 사용자의 글은 전부 관리자의 글로 변환됩니다.');?>\n(<?php echo _t('글이 전부 삭제되지는 않고 팀블로그의 로그인 데이터만 삭제됩니다');?>)\n<?php echo _t('삭제 이후에는 복원이 불가능합니다.');?> <?php echo _t('정말 삭제 하시겠습니까?');?>")) return false;
							} else { // No post from user.
								if(!confirm('<?php echo _t('삭제 하시겠습니까?');?>')) 
									return false;
							}
							var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/control/action/blog/deleteUser/" + "?userid=" + userid + "&blogid=" + <?php echo $bid?>);
							request.onSuccess = function() {
								window.location.href="<?php echo $blogURL;?>/control/blog/detail/<?php echo $bid?>";
							}
							request.onError = function() {
								alert("<?php echo _t('실패했습니다.');?>");
							}
							request.send();
						}
						
						
						function deleteBlog(bid) {
							if (!confirm("<?php echo _t('되돌릴 수 없습니다.');?>\t\n\n<?php echo _t('계속 진행 하시겠습니까?');?>")) return false;
							var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/control/action/blog/delete/?item="+bid);
							request.onSuccess = function() {
								PM.removeRequest(this);
								window.location.href = '../';
							}
							request.onError = function() {
								PM.removeRequest(this);
								msg = this.getText("/response/result");
								alert("<?php echo _t('블로그 삭제에 실패하였습니다.');?>\r\nError : " + msg);
							}
							PM.addRequest(request, _t("블로그 삭제중"));
							request.send();
						}
						
						function changeOwner(owner) {
							var request = new HTTPRequest("<?php echo $blogURL;?>/control/action/blog/changeOwner/?owner="+owner+"&blogid="+<?php echo $bid?>);
							request.onSuccess = function() {
								alert("<?php echo _t('소유자가 변경되었습니다. \r\n기존의 소유자는 관리자로 변경되었습니다.');?>");
								window.location.reload();
							}
							request.onError = function() {
								msg = this.getText("/response/result");
								alert("<?php echo _t('소유자를 변경하지 못했습니다.');?>\r\nError : " + msg);
							}
							request.send();
						}
						
						function changeACL(acltype, userid, checked) {
							var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/control/action/blog/changeACL/?blogid=" + <?php echo $bid?> + "&acltype=" + acltype + "&userid=" + userid + "&switch=" + checked);
							request.onSuccess = function() {
								PM.showMessage("<?php echo _t('설정을 변경했습니다.');?>", "center", "bottom");
								window.location.reload();
							}
							request.onError = function() {
								PM.showErrorMessage("<?php echo _t('실패했습니다.');?>", "center", "bottom");
							}
							request.send();
						}
						
						function addUser(user) {
							var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/control/action/blog/addUser/?user="+user+"&blogid="+<?php echo $bid?>);
							request.onSuccess = function() {
								alert("<?php echo _t('팀블로그 참여자를 추가하였습니다.');?>\r\n<?php echo _t('초대 메일은 발송되지 않으며, 새로 추가된 참여자는 기본 권한만을 가지게 됩니다.');?>");
								window.location.reload();
							}
							request.onError = function() {
								msg = this.getText("/response/message");
								PM.showErrorMessage("<?php echo _t('참여자를 추가하지 못하였습니다.');?>\r\nMessage : \r\n" + msg,"center","bottom");
							}
							request.send();
						}
						//]]> 
						</script>

						<div id="part-blog-about" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 정보');?></span></h2>
							
							<div id="team-blog-about" class="container">
								<h3><?php echo empty($blogsetting['title']) ? '<em>'._t('비어 있는 타이틀').'</em>' : '<a href="'.getDefaultUrl($bid).'">'.$blogsetting['title'].'</a>';?></h3>
								
								<div class="main-explain-box">
									<p class="explain"><?php echo empty($blogsetting['description']) ? '<em>'._t('비어 있는 블로그 설명').'</em>' : $blogsetting['description'];?></p>
								</div>
								
								<ul>
									<?php if ($bid == getServiceSetting("defaultBlogId",1)) { ?><li><em><?php echo _t('이 블로그는 대표 블로그입니다.');?></em></li><?php } ?>
									<li><?php echo _f('이 블로그에는 총 %1개의 글이 있습니다.', POD::queryCell("SELECT count(*) FROM {$database['prefix']}Entries WHERE blogid = ".$bid));?></li>
                                    <li><?php echo _f('이 블로그에는 총 %1개의 걸린글(트랙백)이 있습니다.', POD::queryCell("SELECT count(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = ".$bid." AND type = 'trackback'"));?></li>
                                    <li><?php echo _f('이 블로그에는 총 %1개의 댓글이 있습니다.', POD::queryCell("SELECT count(*) FROM {$database['prefix']}Comments WHERE blogid = ".$bid));?></li>
                                    <li><?php 
		$attachmentSum = POD::queryCell("SELECT sum(size) FROM `{$database['prefix']}Attachments` WHERE blogid = ".$bid);
		if(empty($attachmentSum)) echo _t('이 블로그에는 첨부파일이 없습니다.');
		else echo _f('이 블로그가 사용중인 첨부파일의 총 용량은 %1입니다.', Misc::getSizeHumanReadable($attachmentSum));?></li>
                                </ul>
							</div>
								
							<div id="team-member-list" class="container">
								<h4><span class="text"><?php echo _t('팀블로그 멤버 목록');?></span></h4>
								
								<table class="data-inbox">
									<thead>
										<tr>
											<th class="name"><?php echo _t('사용자');?></th>
											<th class="role"><?php echo _t('관리자');?></th>
											<th class="role"><?php echo _t('글관리');?></th>
											<th class="action" colspan=2 ><?php echo _t('명령');?></th>
										</tr>
									</thead>
									<tbody>
<?php
	$teamblog = POD::queryAll("SELECT * FROM `{$database['prefix']}Privileges` WHERE blogid = " . $bid);
	foreach ($teamblog as $row){
		echo "<tr>".CRLF;
		echo "<td class=\"name\"><a href=\"{$blogURL}/control/user/detail/{$row['userid']}\">".User::getName($row['userid'])."(".User::getEmail($row['userid']).")</a></td>".CRLF;

		if ($row['acl'] & BITWISE_OWNER) {
			echo '<td class="role" colspan="4">'._t('이 사용자는 블로그의 소유자입니다.').'</td>'.CRLF;
		}
		else {
		echo "<td class=\"role\"><a href=\"".$blogURL."/control/action/blog/changeACL/?blogid=" . $bid . "&acltype=admin&userid=" .$row['userid']."&switch=".(($row['acl'] & BITWISE_ADMINISTRATOR)?0:1)."\" onclick=\"changeACL('admin',".$row['userid'].",".(($row['acl'] & BITWISE_ADMINISTRATOR)?0:1).");return false;\">".(($row['acl'] & BITWISE_ADMINISTRATOR)?_t('ON'):_t('OFF'))."</a></td>".CRLF;
		echo "<td class=\"role\"><a href=\"".$blogURL."/control/action/blog/changeACL/?blogid=" . $bid . "&acltype=editor&userid=" .$row['userid']."&switch=".(($row['acl'] & BITWISE_EDITOR)?0:1)."\" onclick=\"changeACL('editor',".$row['userid'].",".(($row['acl'] & BITWISE_EDITOR)?0:1).");return false;\">".(($row['acl'] & BITWISE_EDITOR)?_t('ON'):_t('OFF'))."</a></td>".CRLF;
		echo "<td class=\"role\"><a href=\"".$blogURL."/control/action/blog/deleteUser/?blogid=" . $bid . "&userid=".$row['userid']."\" onclick =  \"deleteUser(".$row['userid'].",1);return false;\">" . _t('팀원 제외') . "</a></td>".CRLF;
		echo "<td class=\"role\"><a href=\"".$blogURL."/control/action/blog/changeOwner/?blogid=" . $bid . "&owner=".$row['userid']."\" onclick =  \"changeOwner(".$row['userid'].");return false;\">" . _t('소유자 변경') . "</a></td>".CRLF;
		echo "</tr>".CRLF;
		}
	}
?>
									</tbody>
								</table>
							</div>
							
							<div id="team-new-member" class="container">
								<h4><?php echo _t('팀원 추가');?></h4>
								
								<form action="<?php echo $blogURL?>/control/action/blog/addUser/">
									<dl>
										<dt><label for=""><?php echo _t('사용자'); ?></label></dt>
										<dd>
											<span id="suggestContainer"><input type="text" id="bi-owner-loginid" name="user" value="" /></span>
											<input type="hidden" name="blogid" value="<?php echo $bid?>" />
											<input type="submit" class="input-button" value="<?php echo _t("팀원 추가");?>" onclick="addUser(ctlUserSuggestObj.getValue());return false;" />
										</dd>
									</dl>
								</form>
								
								<script type="text/javascript">
									//<![CDATA[
										try {
											document.getElementById("suggestContainer").innerHTML = '';
											var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("suggestContainer"), false);
											ctlUserSuggestObj.setValue("<?php echo User::getEmail(1);?>");
										} catch (e) {
											document.getElementById("suggestContainer").innerHTML = '<input type="text" id="bi-owner-loginid" name="user" value="" />';
										}
									//]]>
								</script>
							</div>
							
							<div class="button-box">
								<a class="button" href="#void" onclick="deleteBlog(<?php echo $bid;?>); return false;"><?php echo _t("블로그 삭제");?></a>
								<?php if ($bid != getServiceSetting("defaultBlogId",1)) { ?><a class="button" href="<?php echo $blogURL;?>/control/action/blog/setDefault/?blogid=<?php echo $bid;?>" onclick="setDefaultBlog('<?php echo $bid;?>'); return false;"><?php echo _t('대표 블로그 설정');?></a><?php } ?>
								<a class="button" href="<?php echo $blogURL;?>/control/blog"><?php echo _t("돌아가기");?></a>
							</div>
						</div>
<?php
require ROOT . '/interface/common/control/footer.php';
?>
