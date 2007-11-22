<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$service['admin_script']='control.js';
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
require ROOT . '/lib/piece/owner/libForControl.php';
global $database;
$blogid=$suri['id'];
$blogsetting=getBlogSettings($blogid);
$blogsetting['owner']= DBQuery::queryCell("SELECT userid FROM `{$database['prefix']}teamblog` WHERE acl & ".BITWISE_OWNER." != 0 AND blogid = " . $blogid);
?>
<script type="text/javascript"> // <![CDATA[

function deleteBlog(bid) {
	if (!confirm(_t('되돌릴 수 없습니다.\t\n\n계속 진행하시겠습니까?'))) return false;
	var request = new HTTPRequest(blogURL + "/owner/control/action/blog/delete/"+bid);
	request.onSuccess = function() {
		PM.removeRequest(this);
		window.location.href = './';
	}
	request.onError = function() {
		alert(_t('블로그 삭제에 실패하였습니다.'));
	}
	PM.addRequest(request, _t("블로그 삭제중"));
	request.send();
}


function changeOwner(owner) {
	var request = new HTTPRequest("<?php echo $blogURL;?>/owner/control/action/changeowner/?owner="+owner+"&blogid="+<?php echo $blogid?>);
	request.onSuccess = function() {
		alert("<?php echo _t('소유자가 변경되었습니다. \r\n기존의 소유자는 참가자로 변경되었습니다.');?>");
		window.location.reload();
	}
	request.onError = function() {
		msg = this.getText("/response/result");
		alert("<?php echo _t('소유자를 변경하지 못했습니다.');?>Error : \r\n" + msg);
	}
	request.send();
}
// ]]> </script>
						<div id="part-center-about" class="part">
<a href="<?php echo $blogURL;//TODO TEMPCODE?>/owner/control/blog">&lt;&lt;돌아가기</a>
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 정보');?></span></h2>
						
							<h3>Brand yourself! : <?php echo $blogsetting['title'];?></h3>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo $blogsetting['description'];?></h3>
								</p>
								<div id="copyright">
                                    <?php echo _f('이 블로그에는 총 %1개의 글이 있습니다.', DBQuery::queryCell("SELECT Count(*) FROM {$database['prefix']}Entries WHERE blogid = ".$blogid));?><br/>
                                    <?php echo _f('이 블로그에는 총 %1개의 트랙백이 있습니다.', DBQuery::queryCell("SELECT Count(*) FROM {$database['prefix']}Trackbacks WHERE blogid = ".$blogid));?><br/>
                                    <?php echo _f('이 블로그에는 총 %1개의 코멘트가 있습니다.', DBQuery::queryCell("SELECT Count(*) FROM {$database['prefix']}Comments WHERE blogid = ".$blogid));?><br/>
                                    <?php echo _f('이 블로그가 사용중인 첨부파일의 총 용량은 %1 입니다.', getSizeHumanReadable(DBQuery::queryCell(" SELECT sum( size ) FROM `{$database['prefix']}Attachments` WHERE blogid = ".$blogid)));?>
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
												<td class="name"><a href="<?php echo getDefaultUrl($blogid);?>"><?php echo _t("블로그 보기");?></a></td>
											</tr>
											<tr>
											<tr>
												<td class="name"><a href="#void" onClick="deleteBlog(<?php echo $blogid?>);return false;"><?php echo _t("블로그 삭제");?></a></td>
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
												<th class="role"><?php echo _t('권한 그룹');?></th>
											</tr>
										</thead>
										<tbody><?php
$teamblog = DBQuery::queryAll("SELECT * FROM `{$database['prefix']}teamblog` WHERE blogid = " . $blogid);
	foreach ($teamblog as $row){
		echo "<tr>";
		echo "<td class=\"name\"><a href=\"{$blogURL}/owner/control/user/{$row['userid']}\">".getUserName($row['userid'])."(".getUserEmail($row['userid']).")</a></td>";

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
								<h3><span class="text"><?php echo _t('Plugin');?></span></h3>
								<div id="tester-container" class="container">
									<h4><?php echo _t('소유자 변경');?></h4>
									<div>
									<form onsubmit="return false;">
<span class="label"><?php echo _t('소유자'); ?> : </span>
<span id="sgtOwner"></span>
<input type=submit value="<?php echo _t("소유자 변경");?>" onclick="changeOwner(ctlUserSuggestObj.getValue());return false;">
</form>
<script type="text/javascript">
//<![CDATA[
	try {
		var ctlUserSuggestObj = new ctlUserSuggest(document.getElementById("sgtOwner"),  false);
		ctlUserSuggestObj.setInputClassName("bi-owner-loginid");
		ctlUserSuggestObj.setValue("<?php echo getUserEmail($blogsetting['owner']);?>");
	} catch (e) {
		document.getElementById("sgtOwner").innerHTML = '<input type="text" class="bi-owner-loginid" name="location" value="" />';
	}
//]]>
</script> 
<?php //echo "<div id=debug></div>";?>
</div>
								</div>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
