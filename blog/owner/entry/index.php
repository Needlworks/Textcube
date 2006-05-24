<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
publishEntries();
if (isset($_POST['categoryAtHome']))
	$_POST['category'] = $_POST['categoryAtHome'];
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = getPersonalization($owner, 'rowsPerPage');
if (empty($_POST['perPage'])) {
} else if (!empty($_POST['perPage']) && $perPage != $_POST['perPage']) {
	setPersonalization($owner, 'rowsPerPage', $_POST['perPage']);
	$perPage = $_POST['perPage'];
} else if (!empty($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
}
list($entries, $paging) = getEntriesWithPagingForOwner($owner, $categoryId, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu00.php';
?>
								<input type="hidden" name="withSearch" value="" />
								
								<script type="text/javascript">
									//<![CDATA[
<?
if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION)) {
?>
										window.onload = function () {
											if (confirm("<?=_t('태터툴즈 시스템 점검이 필요합니다. 지금 점검하시겠습니까?')?>"))
												window.location.href = "<?=$blogURL?>/checkup";
										}
<?
}
?>
										function setEntryVisibility(entry, visibility) {
											if ((visibility < 0) || (visibility > 3))
												return false;
											
											if (visibility == 3 && document.getElementById("syndicatedIcon_" + entry).style.backgroundPosition == 'left bottom') {
												visibility = 2;
											}
											
											var request = new HTTPRequest("<?=$blogURL?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
											
											switch (visibility) {
												case 0:
													document.getElementById("protectedSettingIcon_" +  + entry).style.display = 'none';
													document.getElementById("privateIcon_" + entry).innerHTML = '<span><?=_t('비공개')?></span>';
													document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a>';
													document.getElementById("publicIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a>';
													document.getElementById("privateIcon_" + entry).setAttribute('title', '<?=_t('현재 비공개 상태입니다.')?>');
													document.getElementById("protectedIcon_" + entry).removeAttribute('title');
													document.getElementById("publicIcon_" + entry).removeAttribute('title');
													
													document.getElementById("entry" + entry + "Password").setAttribute('disabled', 'disabled');
													
													document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
													document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 3)" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다')?>"><span><?=_t('비발행')?></span></a>';
													
													break;
												case 1:
													document.getElementById("protectedSettingIcon_" +  + entry).style.display = 'block';
													document.getElementById("privateIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a>';
													document.getElementById("protectedIcon_" + entry).innerHTML = '<span><?=_t('보호')?></span>';
													document.getElementById("publicIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a>';
													document.getElementById("privateIcon_" + entry).removeAttribute('title');
													document.getElementById("protectedIcon_" + entry).setAttribute('title', '<?=_t('현재 보호 상태입니다.')?>');
													document.getElementById("publicIcon_" + entry).removeAttribute('title');
													
													document.getElementById("entry" + entry + "Password").removeAttribute('disabled');
													
													document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
													document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 3)" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다')?>"><span><?=_t('비발행')?></span></a>';
													
													break;
												case 2:
													document.getElementById("protectedSettingIcon_" +  + entry).style.display = 'none';
													document.getElementById("privateIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a>';
													document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a>';
													document.getElementById("publicIcon_" + entry).innerHTML = '<span><?=_t('공개')?></span>';
													document.getElementById("privateIcon_" + entry).removeAttribute('title');
													document.getElementById("protectedIcon_" + entry).removeAttribute('title');
													document.getElementById("publicIcon_" + entry).setAttribute('title', '<?=_t('현재 공개 상태입니다.')?>');
													
													document.getElementById("entry" + entry + "Password").setAttribute('disabled', 'disabled');
													
													document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
													document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 3)" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다')?>"><span><?=_t('비발행')?></span></a>';
													
													break;
												case 3:
													document.getElementById("protectedSettingIcon_" +  + entry).style.display = 'none';
													document.getElementById("privateIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a>';
													document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a>';
													document.getElementById("publicIcon_" + entry).innerHTML = '<span><?=_t('공개')?></span>';
													document.getElementById("privateIcon_" + entry).removeAttribute('title');
													document.getElementById("protectedIcon_" + entry).removeAttribute('title');
													document.getElementById("publicIcon_" + entry).setAttribute('title', '<?=_t('현재 공개 상태입니다.')?>');
													
													document.getElementById("entry" + entry + "Password").setAttribute('disabled', 'disabled');
													
													document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-on-icon';
													document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 2)" title="<?=_t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.')?>"><span><?=_t('발행')?></span></a>';
													
													break;
											}
											request.send();
										}
										
										function deleteEntry(id) {
											if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?')?>"))
												return;
											var request = new HTTPRequest("<?=$blogURL?>/owner/entry/delete/" + id);
											request.onSuccess = function () {
												document.forms[0].submit();
											}
											request.send();
										}
										
										function protectEntry(id) {
											var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/protect/" + id);
											request.onSuccess = function () {
												hideLayer("entry" + id + "Protection");
											}
											request.onError = function () {
												alert("<?=_t('보호글의 비밀번호를 변경하지 못 했습니다.')?>");
											}
											request.send("password=" + encodeURIComponent(document.getElementById("entry" + id + "Password").value));
										}
										
										function checkAll(checked) {
											for (i = 0; document.forms[0].elements[i]; i ++)
												if (document.forms[0].elements[i].name == "entry")
													document.forms[0].elements[i].checked = checked;
										}
										
										function processBatch(mode) {
											var entries = '';
											switch (mode) {
												case 'classify':
													for (var i = 0; i < document.forms[0].elements.length; i++) {
														var oElement = document.forms[0].elements[i];
														if ((oElement.name == "entry") && oElement.checked)
															setEntryVisibility(oElement.value, 0);
													}
													break;
												case 'publish':
													for (var i = 0; i < document.forms[0].elements.length; i++) {
														var oElement = document.forms[0].elements[i];
														if ((oElement.name == "entry") && oElement.checked)
															setEntryVisibility(oElement.value, 2);
													}
													break;
												case 'delete':
													if (!confirm("<?=_t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?')?>"))
														return false;
													var targets = "";
													for (var i = 0; i < document.forms[0].elements.length; i++) {
														var oElement = document.forms[0].elements[i];
														if ((oElement.name == "entry") && oElement.checked)
															targets += oElement.value +'~*_)';
													}
													var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/delete/");
													request.onSuccess = function () {
														document.forms[0].submit();
													}
													request.send("targets="+targets);
													break;
											}
										}
										
										function removeTrackbackLog(id,entry) {
											if (confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?')?>")) {
												var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/remove/" + id);
												request.onSuccess = function () {
													printTrackbackLog(entry);
												}
												request.onError = function () {
													alert("<?=_t('트랙백을 삭제하는데 실패하였습니다.')?>");
												}
												request.send();
											}
										}
										
										function printTrackbackLog(id) {
											var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/" + id);
											request.onVerify = function () {
												var resultRow = this.getText("/response/result").split('*');
												if (resultRow.length == 1)
													var str ='';
												else {
													var str='<table cellspacing="0" cellpadding="0" border="0">';
													for (var i=0; i<resultRow.length-1 ; i++) {
														field = resultRow[i].split(',');
														str += '<tr id="trackbackLog_'+field[0]+'">\n';
														str += '	<td class="address">'+field[1]+'</td>\n'
														str += '	<td class="date">'+field[2]+'</td>\n'
														str += '	<td class="remove"><a class="remove-button button" href="#void" onclick="removeTrackbackLog('+field[0]+','+id+');" title="<?=_t('이 트랙백을 삭제합니다.')?>"><span><?=_t('삭제')?></span></a></td>\n'
														str += '</tr>\n';
													}
													str += "</table>";
												}
												document.getElementById("logs_"+id).innerHTML = str;
												return true;
											}
											request.send();
										}
										
										function showProtectSetter(id) {
											if (document.getElementById("entry" + id + "Protection").style.display == "") {
												document.getElementById("entry" + id + "Protection").style.display = "none";
												document.getElementById("protectedSettingIcon_" + id).className = "protect-off-button button";
											} else {
												document.getElementById("entry" + id + "Protection").style.display = "";
												document.getElementById("entry"+ id + "Password").select();
												document.getElementById("protectedSettingIcon_" + id).className = "protect-on-button button";
											}
											return;
										}
										
										function showTrackbackSender(id) {
											if (document.getElementById("trackbackSender_" + id).style.display == "") {
												document.getElementById("trackbackSender_" + id).style.display = "none";
												document.getElementById("trackbackIcon_" + id).className = "trackback-off-button button";
											} else {
												document.getElementById("trackbackSender_" + id).style.display = "";
												document.getElementById("trackbackForm_" + id).select();
												document.getElementById("trackbackIcon_" + id).className = "trackback-on-button button";
												printTrackbackLog(id);
											}
											return;
										}
										
										function sendTrackback(id) {
											var trackbackField = document.getElementById('trackbackForm_'+id);
											var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
											request.onSuccess = function () {
												document.getElementById('trackbackForm_'+id).value = "http://";
												printTrackbackLog(id);
											}
											request.onError = function () {
												alert("<?=_t('트랙백 전송에 실패하였습니다.')?>");
											}
											request.send();
										}
									//]]>
								</script>
								
								<div id="part-post-list" class="part">
									<h2 class="caption">
										<span class="category">
											<label for="category"><span><?php echo _t('분류')?></span><span class="divider"> | </span></label>
											<select id="category" name="category" onchange="document.forms[0].page.value=1; document.forms[0].submit()">
												<option value="0"><?php echo _t('전체')?></option>
<?php
foreach (getCategories($owner) as $category) {
?>
												<option value="<?php echo $category['id']?>"<?php echo ($category['id'] == $categoryId ? ' selected="selected"' : '')?>><?php echo htmlspecialchars($category['name'])?></option>
<?php
	foreach ($category['children'] as $child) {
?>
												<option value="<?php echo $child['id']?>"<?php echo ($child['id'] == $categoryId ? ' selected="selected"' : '')?>>&nbsp;► <?php echo htmlspecialchars($child['name'])?></option>
<?php
	}
}
?>
											</select>
										</span>
										<span class="interword"><?php echo _t('카테고리에')?></span>
										<span class="main-text"><?php echo _t('등록된 글 목록입니다')?></span>
										
										<span class="clear"></span>
									</h2>
									
									<table class="data-inbox" cellspacing="0" cellpadding="0" border="0">
										<thead>
											<tr>
												<td class="selection"><input type="checkbox" class="checkbox" onclick="checkAll(this.checked);" /></td>
												<td class="date"><span><?=_t('등록일자')?></span></td>
												<td class="statue"><span><?=_t('상태')?></span></td>
												<td class="syndicate"><span><?=_t('발행')?></span></td>
												<td class="category"><span><?=_t('분류')?></span></td>
												<td class="title"><span><?=_t('제목')?></span></td>
												<td class="protect"><span><?=_t('보호설정')?></span></td>
												<td class="trackback"><span><?=_t('트랙백')?></span></td>
												<td class="delete"><span><?=_t('삭제')?></span></td>
											</tr>
										</thead>
										<tbody>
<?
for ($i=0; $i<sizeof($entries); $i++) {
	$entry = $entries[$i];
	
	if ($i == sizeof($entries) - 1) {
?>
											<tr class="tr-last-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.forms[0].allChecked.checked=false" /></td>
												<td class="date"><?=Timestamp::formatDate($entry['published'])?></td>
												<td class="statue">
<?
		if ($entry['visibility'] == 0) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon" title="<?=_t('현재 비공개 상태입니다.')?>"><span><?=_t('비공개')?></span></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a></span>
<?
		} else if ($entry['visibility'] == 1) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon" title="<?=_t('현재 보호 상태입니다.')?>"><span><?=_t('보호')?></span></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a></span>
<?
		} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon" title="<?=_t('현재 공개 상태입니다.')?>"><span><?=_t('공개')?></span></span>
<?
		}
?>
												</td>
												<td class="syndicate">
<?
		if ($entry['visibility'] == 3) {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 3)" title="<?=_t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.')?>"><span><?=_t('발행')?></span></a></span>
<?
		} else {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-off-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 3)" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span><?=_t('비발행')?></span></a></span>
<?
		}
?>
												</td>
												<td class="category">
													<a href="#void" onclick="document.forms[0].category.value='<?=$entry['category']?>'; document.forms[0].submit();"><?=htmlspecialchars($entry['categoryLabel'])?></a>
												</td>
												<td class="title">
													<?=($entry['draft'] ? ('<span class="temp-icon bullet" title="' . _t('임시 저장본이 있습니다.') . '"><span>' . _t('[임시]') . '</span></span> ') : '')?><a href="#void" onclick="document.forms[0].action='<?=$blogURL?>/owner/entry/edit/<?=$entry['id']?>'<?=($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')") : '')?>; document.forms[0].submit();"><?=htmlspecialchars($entry['title'])?></a>
												</td>
												<td class="protect">
													<a id="protectedSettingIcon_<?=$entry['id']?>" class="protect-off-button button" href="#void" style="display: <?=(abs($entry['visibility']) == 1 ? 'block' : 'none')?>;" onclick="showProtectSetter('<?=$entry['id']?>')" title="<?=_t('보호 패스워드를 설정합니다.')?>"><span><?=_t('보호')?></span></a>
												</td>
												<td class="trackback">
													<a id="trackbackIcon_<?=$entry['id']?>" class="trackback-off-button button" href="#void" onclick="showTrackbackSender(<?=$entry['id']?>,event)" title="<?=_t('관련글에 트랙백을 보냅니다.')?>"><span><?=_t('트랙백')?></span></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="#void" onclick="deleteEntry(<?=$entry['id']?>)" title="<?=_t('이 포스트를 삭제합니다.')?>"><span><?=_t('삭제')?></span></a>
												</td>
											</tr>
											<tr id="entry<?=$entry['id']?>Protection" class="hidden-last-layer" style="display: none;">
												<td colspan="9">
													<div class="layer-section">
														<div class="password-box">
															<label for="entry<?=$entry['id']?>Password"><span><?=_t('비밀번호')?></span></label><span class="divider"> | </span><input type="text" id="entry<?=$entry['id']?>Password" class="text-input" value="<?=$entry['password']?>" maxlength="16" onkeydown="if (event.keyCode == 13) protectEntry(<?=$entry['id']?>)"<?=$entry['visibility'] == 1 ? '' : ' disabled="disabled"'?> />
														</div>
														<div class="button-box">
															<a class="edit-button button" href="#void" onclick="protectEntry(<?=$entry['id']?>)"><span><?=_t('수정')?></span></a>
															<!--span class="divider"> | </span>
															<a class="close-button button" href="#void" onclick="collapseAll()"><span><?=_t('닫기')?></span></a-->
														</div>
														
														<div class="clear"></div>
													</div>
												</td>
											</tr>
											<tr id="trackbackSender_<?=$entry['id']?>" class="hidden-last-layer" style="display: none;">
												<td colspan="9">
													<div class="layer-section">
														<div class="trackback-box">
															<label for="trackbackForm_<?=$entry['id']?>"><span><?=_t('트랙백 주소')?></span></label><span class="divider"> | </span><input type="text" id="trackbackForm_<?=$entry['id']?>" class="text-input" name="trackbackURL" value="http://" onkeydown="if (event.keyCode == 13) sendTrackback(<?=$entry['id']?>)" />
														</div>
														<div id="logs_<?=$entry['id']?>" class="trackback-log-box"></div>
														<div class="button-box">
															<a class="send-button button" href="#void" onclick="sendTrackback(<?=$entry['id']?>)"><span><?=_t('전송')?></span></a>
															<!--span class="divider"> | </span>
															<a class="close-button button" href="#void" onclick="collapseAll()"><span><?=_t('닫기')?></span></a-->
														</div>
															
															<div class="clear"></div>
		 											</div>
												</td>
											</tr>
<?
	} else {
?>
											<tr class="tr-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.forms[0].allChecked.checked=false" /></td>
												<td class="date"><?=Timestamp::formatDate($entry['published'])?></td>
												<td class="statue">
<?
		if ($entry['visibility'] == 0) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon" title="<?=_t('현재 비공개 상태입니다.')?>"><span><?=_t('비공개')?></span></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a></span>
<?
		} else if ($entry['visibility'] == 1) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon" title="<?=_t('현재 보호 상태입니다.')?>"><span><?=_t('보호')?></span></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span><?=_t('공개')?></span></a></span>
<?
		} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 1)" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon" title="<?=_t('현재 공개 상태입니다.')?>"><span><?=_t('공개')?></span></span>
<?
		}
?>
												</td>
												<td class="syndicate">
<?
		if ($entry['visibility'] == 3) {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 3)" title="<?=_t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.')?>"><span><?=_t('발행')?></span></a></span>
<?
		} else {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-off-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 3)" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span><?=_t('비발행')?></span></a></span>
<?
		}
?>
												</td>
												<td class="category">
													<a href="#void" onclick="document.forms[0].category.value='<?=$entry['category']?>'; document.forms[0].submit();"><?=htmlspecialchars($entry['categoryLabel'])?></a>
												</td>
												<td class="title">
													<?=($entry['draft'] ? ('<span class="temp-icon bullet" title="' . _t('임시 저장본이 있습니다.') . '"><span>' . _t('[임시]') . '</span></span> ') : '')?><a href="#void" onclick="document.forms[0].action='<?=$blogURL?>/owner/entry/edit/<?=$entry['id']?>'<?=($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')") : '')?>; document.forms[0].submit();"><?=htmlspecialchars($entry['title'])?></a>
												</td>
												<td class="protect">
													<a id="protectedSettingIcon_<?=$entry['id']?>" class="protect-off-button button" href="#void" style="display: <?=(abs($entry['visibility']) == 1 ? 'block' : 'none')?>;" onclick="showProtectSetter('<?=$entry['id']?>')" title="<?=_t('보호 패스워드를 설정합니다.')?>"><span><?=_t('보호')?></span></a>
												</td>
												<td class="trackback">
													<a id="trackbackIcon_<?=$entry['id']?>" class="trackback-off-button button" href="#void" onclick="showTrackbackSender(<?=$entry['id']?>,event)" title="<?=_t('관련글에 트랙백을 보냅니다.')?>"><span><?=_t('트랙백')?></span></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="#void" onclick="deleteEntry(<?=$entry['id']?>)" title="<?=_t('이 포스트를 삭제합니다.')?>"><span><?=_t('삭제')?></span></a>
												</td>
											</tr>
											<tr id="entry<?=$entry['id']?>Protection" class="hidden-layer" style="display: none;">
												<td colspan="9">
													<div class="layer-section">
														<div class="password-box">
															<label for="entry<?=$entry['id']?>Password"><span><?=_t('비밀번호')?></span></label><span class="divider"> | </span><input type="text" id="entry<?=$entry['id']?>Password" class="text-input" value="<?=$entry['password']?>" maxlength="16" onkeydown="if (event.keyCode == 13) protectEntry(<?=$entry['id']?>)"<?=$entry['visibility'] == 1 ? '' : ' disabled="disabled"'?> />
														</div>
														<div class="button-box">
															<a class="edit-button button" href="#void" onclick="protectEntry(<?=$entry['id']?>)"><span><?=_t('수정')?></span></a>
															<!--span class="divider"> | </span>
															<a class="close-button button" href="#void" onclick="collapseAll()"><span><?=_t('닫기')?></span></a-->
														</div>
														
														<div class="clear"></div>
													</div>
												</td>
											</tr>
											<tr id="trackbackSender_<?=$entry['id']?>" class="hidden-layer" style="display: none;">
												<td colspan="9">
													<div class="layer-section">
														<div class="trackback-box">
															<label for="trackbackForm_<?=$entry['id']?>"><span><?=_t('트랙백 주소')?></span></label><span class="divider"> | </span><input type="text" id="trackbackForm_<?=$entry['id']?>" class="text-input" name="trackbackURL" value="http://" onkeydown="if (event.keyCode == 13) sendTrackback(<?=$entry['id']?>)" />
														</div>
														<div class="button-box">
															<a class="send-button button" href="#void" onclick="sendTrackback(<?=$entry['id']?>)"><span><?=_t('전송')?></span></a>
															<!--span class="divider"> | </span>
															<a class="close-button button" href="#void" onclick="collapseAll()"><span><?=_t('닫기')?></span></a-->
														</div>
														<div id="logs_<?=$entry['id']?>"></div>
	 													
	 													<div class="clear"></div>
		 											</div>
	 											</td>
	 										</tr>
<?
	}
}
?>
										</tbody>
									</table>
									
									<hr class="hidden" />
									
									<div class="data-subbox">
										<div id="change-section" class="section">
											<span class="label"><span class="text"><?=_t('선택한 글을')?></span></span>
											<select onchange="processBatch(this.value); this.selectedIndex=0">
												<option>-------------------------</option>
												<option value="publish"><?=_t('공개로 변경합니다.')?></option>
												<option value="classify"><?=_t('비공개로 변경합니다.')?></option>
												<option value="delete"><?=_t('삭제합니다.')?></option>
											</select>
											
											<div class="clear"></div>
										</div>
										
										<div id="page-section" class="section">
											<div id="page-navigation">
												<span id="total-count"><?=_t('총')?> <?=$paging['total']?><?=_t('건')?><span class="hidden">, </span></span>
												<span id="page-list">
<?
$paging['url'] = 'javascript: document.forms[0].page.value=';
$paging['prefix'] = '';
$paging['postfix'] = '; document.forms[0].submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
												</span>
											</div>
											<div class="page-count">
												<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?>
												<select name="perPage" onchange="document.forms[0].page.value=1; document.forms[0].submit()">
<?php
for ($i = 10; $i <= 30; $i += 5) {
	if ($i == $perPage) {
?>
													<option value="<?php echo $i?>" selected="selected"><?php echo $i?></option>
<?php
	} else {
?>
													<option value="<?php echo $i?>"><?php echo $i?></option>
<?php
	}
}
?>
												</select>
												<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1)?>
											</div>
												
											<div class="clear"></div>
										</div>
										
										<hr class="hidden" />
										
										<div id="search-section" class="section">
											<!--label for="search"><span><?=_t('제목')?>, <?=_t('내용')?></span></label><span class="divider"> |</span-->
											<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
											<a class="search-button button" href="#void" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();"><span><?=_t('검색')?></span></a>
											
											<div class="clear"></div>
										</div>
										
										<div class="clear"></div>
									</div>
								</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>