<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$service['admin_script']='control.js';

require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
requireComponent('Textcube.Function.misc');

global $database;
$bid=$suri['id'];
$blogsetting = getBlogSettings($bid);
?>
<script type="text/javascript"> // <![CDATA[
//from interface/owner/setting/teamblog
function deleteUser(userid, atype) {
	if(atype == 1) { // If there are posts from user.
		if(!confirm("<?php echo _t('선택된 사용자를 정말 삭제하시겠습니까?');?>\n\n<?php echo _t('삭제되는 기존 사용자의 글은 전부 관리자의 글로 변환됩니다.');?>\n(<?php echo _t('글이 전부 삭제되지는 않고 팀블로그의 로그인 데이터만 삭제됩니다');?>)\n<?php echo _t('삭제 이후에는 복원이 불가능합니다.');?> <?php echo _t('정말 삭제 하시겠습니까?');?>")) return false;
	} else { // No post from user.
		if(!confirm('<?php echo _t('삭제 하시겠습니까?');?>')) 
			return false;
	}
	var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/control/action/blog/deleteUser/" + "?userid=" + userid + "&blogid=" + <?php echo $bid?>);
	request.onSuccess = function() {
		window.location.href="<?php echo $blogURL;?>/owner/control/blog/detail/<?php echo $bid?>";
	}
	request.onError = function() {
		alert("<?php echo _t('실패했습니다.');?>");
	}
	request.send();
}


function deleteBlog(bid) {
	if (!confirm(_t('되돌릴 수 없습니다.\t\n\n계속 진행하시겠습니까?'))) return false;
	var request = new HTTPRequest(blogURL + "/owner/control/action/blog/delete/?item="+bid);
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
	var request = new HTTPRequest("<?php echo $blogURL;?>/owner/control/action/blog/changeOwner/?owner="+owner+"&blogid="+<?php echo $bid?>);
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

//from interface/owner/setting/teamblog
function changeACL(acltype, userid, checked) {
	var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/control/action/blog/changeACL/" + "?blogid=" + <?php echo $bid?> + "&acltype=" + acltype + "&userid=" + userid + "&switch=" + checked);
	request.onSuccess = function() {
		alert("<?php echo _t('설정을 변경했습니다.');?>", "center", "bottom");
		window.location.reload();
	}
	request.onError = function() {
		alert("<?php echo _t('실패했습니다.');?>");
	}
	request.send();
}

function addUser(user) {
	var request = new HTTPRequest("<?php echo $blogURL;?>/owner/control/action/blog/addUser/?user="+user+"&blogid="+<?php echo $bid?>);
	request.onSuccess = function() {
		alert("<?php echo _t('팀블로그 참여자가 추가 되었습니다.\r\n초대 메일은 발송되지 않으며, 새로 추가된 참여자는 기본 권한만을 가지게 됩니다.');?>");
		window.location.reload();
	}
	request.onError = function() {
		msg = this.getText("/response/message");
		alert("<?php echo _t('참여자를 추가하지 못하였습니다.');?>\r\nMessage : \r\n" + msg);//TODO//PM으로 change
	}
	request.send();
}
// ]]> </script>
						<div id="part-center-about" class="part">
<a href="<?php echo $blogURL;//TODO TEMPCODE?>/owner/control/blog">&lt;&lt;돌아가기</a>
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 정보');?></span></h2>
						
							<h3><?php echo $blogsetting['title'];?></h3>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo $blogsetting['description'];?></h3>
								</p>
								<div id="copyright"><?php if ($bid == getServiceSetting("defaultBlogId",1)) { ?>
									<?php echo _t('이 블로그는 대표 블로그 입니다.');?><br/><?php 
								}
?>
                                    <?php echo _f('이 블로그에는 총 %1개의 글이 있습니다.', POD::queryCell("SELECT Count(*) FROM {$database['prefix']}Entries WHERE blogid = ".$bid));?><br/>
                                    <?php echo _f('이 블로그에는 총 %1개의 트랙백이 있습니다.', POD::queryCell("SELECT Count(*) FROM {$database['prefix']}Trackbacks WHERE blogid = ".$bid));?><br/>
                                    <?php echo _f('이 블로그에는 총 %1개의 코멘트가 있습니다.', POD::queryCell("SELECT Count(*) FROM {$database['prefix']}Comments WHERE blogid = ".$bid));?><br/>
                                    <?php echo _f('이 블로그가 사용중인 첨부파일의 총 용량은 %1 입니다.', misc::getSizeHumanReadable(POD::queryCell(" SELECT sum( size ) FROM `{$database['prefix']}Attachments` WHERE blogid = ".$bid)));?>
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
										<?php 
										if ( $service['type'] != "single" ) {
											?>
											<tr>
												<td class="name"><a href="<?php echo getDefaultUrl($bid);?>"><?php echo _t("블로그 보기");?></a></td>
											</tr>
											<?php 
										} 
?>
											<tr>
	<?php echo "<td class=\"name\"><a href=\"".$blogURL."/owner/control/action/blog/setDefault/?blogid=" . $bid . "\" onclick =  \"setDefaultBlog(".$bid.");return false;\">대표 블로그 설정</a></td>";
	?>
											</tr>
											<tr>
												<td class="name"><a href="#void" onClick="deleteBlog(<?php echo $bid?>);return false;"><?php echo _t("블로그 삭제");?></a></td>
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
												<th class="name"><?php echo _t('사용자');?></th>
												<th class="role" ><?php echo _t('관리자');?></th>
												<th class="role" ><?php echo _t('글관리');?></th>
												<th class="action" colspan=2 ><?php echo _t('Actions');?></th>
											</tr>
										</thead>
										<tbody><?php
$teamblog = POD::queryAll("SELECT * FROM `{$database['prefix']}Teamblog` WHERE blogid = " . $bid);
	foreach ($teamblog as $row){
		echo "<tr>";
		echo "<td class=\"name\"><a href=\"{$blogURL}/owner/control/user/detail/{$row['userid']}\">".User::getName($row['userid'])."(".User::getEmail($row['userid']).")</a></td>";

		if ($row['acl'] & BITWISE_OWNER) {
			echo "<td class=\"role\" colspan = 4>"._t('이 사용자는 소유자 입니다.')."</td>";
		}
		else {
		echo "<td class=\"role\"><a href=\"".$blogURL."/owner/control/action/blog/changeACL/?blogid=" . $bid . "&acltype=admin&userid=" .$row['userid']."&switch=".(($row['acl'] & BITWISE_ADMINISTRATOR)?0:1)."\" onclick =  \"changeACL('admin',".$row['userid'].",".(($row['acl'] & BITWISE_ADMINISTRATOR)?0:1).");return false;\">".(($row['acl'] & BITWISE_ADMINISTRATOR)?_t('ON'):_t('OFF'))."</a></td>";
		echo "<td class=\"role\"><a href=\"".$blogURL."/owner/control/action/blog/changeACL/?blogid=" . $bid . "&acltype=editor&userid=" .$row['userid']."&switch=".(($row['acl'] & BITWISE_EDITOR)?0:1)."\" onclick =  \"changeACL('editor',".$row['userid'].",".(($row['acl'] & BITWISE_EDITOR)?0:1).");return false;\">".(($row['acl'] & BITWISE_EDITOR)?_t('ON'):_t('OFF'))."</a></td>";
		echo "<td class=\"role\"><a href=\"".$blogURL."/owner/control/action/blog/deleteUser/?blogid=" . $bid . "&userid=".$row['userid']."\" onclick =  \"deleteUser(".$row['userid'].",1);return false;\">팀원 제외</a></td>";
		echo "<td class=\"role\"><a href=\"".$blogURL."/owner/control/action/blog/changeOwner/?blogid=" . $bid . "&owner=".$row['userid']."\" onclick =  \"changeOwner(".$row['userid'].");return false;\">소유자 변경</a></td>";
		echo "</tr>";
		}
	}
?>
										</tbody>
									</table>
								<div id="tester-container" class="container">
									<h4><?php echo _t('팀원 추가');?></h4>
									<div>
<form action ="<?php echo $blogURL?>/owner/control/action/blog/addUser/" onsubmit="return false;">
<span class="label"><?php echo _t('사용자'); ?> : </span>
<span id="sgtOwner"><input type="text" class="bi-owner-loginid" name="user" value="" /></span>
<input type=hidden name = "blogid" value="<?php echo $bid?>">
<input type=submit value="<?php echo _t("팀원 추가");?>" onclick="addUser(ctlUserSuggestObj.getValue());return false;">
</form>
<script type="text/javascript">
//<![CDATA[
	try {
		document.getElementById("sgtOwner").innerHTML = '';
		var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("sgtOwner"),  false);
		ctlUserSuggestObj.setInputClassName("bi-owner-loginid");
		ctlUserSuggestObj.setValue("<?php echo User::getEmail(1);?>");
	} catch (e) {
		document.getElementById("sgtOwner").innerHTML = '<input type="text" class="bi-owner-loginid" name="location" value="" />';
	}
//]]>
</script> 
</div>
								</div>
							</div>
						</div>
								</div>
								
							<div id="supporter-description" class="section">
								<h3><span class="text"><?php echo _t('Plugin');?></span></h3>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
