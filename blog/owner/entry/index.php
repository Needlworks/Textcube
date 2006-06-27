<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
publishEntries();
if (isset($_GET['category']))
	$_POST['category'] = $_GET['category'];
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
						<script type="text/javascript">
							//<![CDATA[
<?
if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION)) {
?>
								tt_init_funcs.push(function() { checkupDialog(); });
								function checkupDialog() {
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
									
									request.onSuccess = function () {
										switch (visibility) {
											case 0:
												document.getElementById("privateIcon_" + entry).innerHTML = '<span class="text"><?=_t('비공개')?></span>';
												document.getElementById("privateIcon_" + entry).className = 'private-on-icon';
												document.getElementById("privateIcon_" + entry).setAttribute('title', '<?=_t('현재 비공개 상태입니다.')?>');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span class="text"><?=_t('보호')?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=public" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a>';
												document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
												document.getElementById("publicIcon_" + entry).removeAttribute('title');
																									
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span class="text"><?=_t('비발행')?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].innerHTML = "";
												
												break;
											case 1:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<span class="text"><?=_t('보호')?></span>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-on-icon';
												document.getElementById("protectedIcon_" + entry).setAttribute('title', '<?=_t('현재 보호 상태입니다.')?>');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=public" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a>';
												document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
												document.getElementById("publicIcon_" + entry).removeAttribute('title');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span class="text"><?=_t('비발행')?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempLink = document.createElement("A");
												tempLink.id = "protectedSettingIcon_" + entry;
												tempLink.className = "protect-off-button button";
												tempLink.setAttribute("href", "<?=$blogURL?>/owner/entry/edit/" + entry + "#status-line");
												tempLink.setAttribute("onclick", "showProtectSetter(" + entry + "); return false;");
												tempLink.setAttribute("title", "<?php echo _t('보호 패스워드를 설정합니다.')?>");
												tempLink.innerHTML = '<span class="text"><?=_t('보호설정')?></span>';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].appendChild(tempLink);
												
												break;
											case 2:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span class="text"><?=_t('보호')?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?=_t('공개')?></span>';
												document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
												document.getElementById("publicIcon_" + entry).setAttribute('title', '<?=_t('현재 공개 상태입니다.')?>');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span class="text"><?=_t('비발행')?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].innerHTML = "";
																							
												break;
											case 3:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span class="text"><?=_t('보호')?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?=_t('공개')?></span>';
												document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
												document.getElementById("publicIcon_" + entry).setAttribute('title', '<?=_t('현재 공개 상태입니다.')?>');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=syndicate" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?=_t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.')?>"><span class="text"><?=_t('발행')?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-on-icon';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].innerHTML = "";
												
												break;
										}
									}
									request.onError = function () {
										switch (visibility) {
											case 0:
												window.location.href = "<?=$blogURL?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=private";
												break;
											case 1:
												window.location.href = "<?=$blogURL?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=protect";
												break;
											case 2:
												window.location.href = "<?=$blogURL?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=public";
												break;
											case 3:
												window.location.href = "<?=$blogURL?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=syndicate";
												break;
										}
									}
									request.send();
								}
								
								function deleteEntry(id) {
									if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
										return;
									var request = new HTTPRequest("<?=$blogURL?>/owner/entry/delete/" + id);
									request.onSuccess = function () {
										document.getElementById('listForm').submit();
									}
									request.onError = function () {
										window.location.href = "<?=$blogURL?>/owner/entry/delete/" + id + "?javascript=disabled&amp;command=delete";
									}
									request.send();
								}
								
								function deleteSelected() {
								
								}
								
								function protectEntry(id) {
									var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/protect/" + id);
									request.onSuccess = function () {
										objTable = getParentByTagName("TABLE", getObject("protectedSettingIcon_" + id));
										objTr = getParentByTagName("TR", getObject("protectedSettingIcon_" + id));
										objTable.deleteRow(objTr.rowIndex + 1);
										
										document.getElementById("protectedSettingIcon_" + id).className = "protect-off-button button";
									}
									request.onError = function () {
										alert("<?=_t('보호글의 비밀번호를 변경하지 못했습니다.')?>");
									}
									request.send("password=" + encodeURIComponent(document.getElementById("entry" + id + "Password").value));
								}
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('listForm').elements[i]; i ++)
										if (document.getElementById('listForm').elements[i].name == "entry")
											document.getElementById('listForm').elements[i].checked = checked;
								}
								
								function processBatch(obj) {	
									mode = obj.value;
									if (mode.match('^category_')) {
										mode = 'category';
									}
									
									var entries = '';
									var isSelected = false;
									for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
										var oElement = document.getElementById('listForm').elements[i];
										if ((oElement.name == "entry") && oElement.checked) {
											isSelected = true;
											break;
										}
									}
									if (!isSelected) {
										alert("<?=_t('적용할 글을 선택해 주십시오.')?>")
										return false;
									}
									switch (mode) {
										case 'classify':
											for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
												var oElement = document.getElementById('listForm').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													setEntryVisibility(oElement.value, 0);
											}
											break;
										case 'publish':
											for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
												var oElement = document.getElementById('listForm').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													setEntryVisibility(oElement.value, 2);
											}
											break;
										case 'delete':
											if (!confirm("<?=_t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
												return false;
											var targets = "";
											for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
												var oElement = document.getElementById('listForm').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													targets += oElement.value +'~*_)';
											}
											var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/delete/");
											request.onSuccess = function () {
												document.getElementById('listForm').submit();
											}
											request.send("targets="+targets);
											break;
										case 'category':
											var targets = "";
											var category = obj.options[obj.options.selectedIndex].value.replace('category_', '');
											var label = obj.options[obj.options.selectedIndex].getAttribute('label');
											var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/changeCategory/");
											for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
												var oElement = document.getElementById('listForm').elements[i];
												if ((oElement.name == "entry") && oElement.checked) {
													targets += oElement.value +',';
												}
											}
											targets = targets.substr(0,targets.length-1);
											
											if (targets == '') {
												return false;
											}
											
											request.onSuccess = function () {
												for (var i = 0; i < document.getElementById('listForm').elements.length; i++) {
													var oElement = document.getElementById('listForm').elements[i];
													if ((oElement.name == "entry") && oElement.checked) {
														document.getElementById("category_" + oElement.value).innerHTML = label;
														document.getElementById("category_" + oElement.value).name = category;
													}
												}
												//document.getElementById('listForm').submit();
											}
											request.send("category="+category+"&targets="+targets);
											break;
									}
									obj.selectedIndex = 0
								}
								
								function removeTrackbackLog(id,entry) {
									if (confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속 하시겠습니까?')?>")) {
										var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/remove/" + id);
										request.onSuccess = function () {
											document.getElementById("logs_"+entry).innerHTML = "";
											printTrackbackLog(entry);
										}
										request.onError = function () {
											alert("<?=_t('트랙백을 삭제하는데 실패했습니다.')?>");
										}
										request.send();
									}
								}
								
								function printTrackbackLog(id) {
									var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/" + id);
									request.onVerify = function () {
										var resultRow = this.getText("/response/result").split('*');
										if (resultRow.length == 1)
											tempTable ='';
										else {
											tempTable = '';
											for (var i=0; i<resultRow.length-1 ; i++) {
												if  (i == 0) {
													tempTable = document.createElement("TABLE");
													tempTable.setAttribute("cellpadding", 0);
													tempTable.setAttribute("cellspacing", 0);
												}
												field = resultRow[i].split(',');
												
												tempTr = document.createElement("TR");
												tempTr.id = "trackbackLog_" + field[0];
												
												tempTd_1 = document.createElement("TD");
												tempTd_1.className = "address";
												tempTd_1.innerHTML = field[1];
												tempTr.appendChild(tempTd_1);
												
												tempTd_2 = document.createElement("TD");
												tempTd_2.className = "date";
												tempTd_2.innerHTML = field[2];
												tempTr.appendChild(tempTd_2);
												
												tempTd_3 = document.createElement("TD");
												tempTd_3.className = "remove";
												
												tempA = document.createElement("A");
												tempA.className = "remove-button button";
												tempA.setAttribute("href", "#void");
												tempA.setAttribute("onclick", "removeTrackbackLog('" + field[0] + "','" + id + "');");
												tempA.setAttribute("title", "<?php echo _t('이 트랙백을 삭제합니다.')?>");
												
												tempSpan = document.createElement("SPAN");
												tempSpan.className = "text";
												tempSpan.innerHTML = "delete";
												
												tempA.appendChild(tempSpan);
												tempTd_3.appendChild(tempA);
												tempTr.appendChild(tempTd_3);
												
												tempTable.appendChild(tempTr);
											}
										}
										if (tempTable != '' ) {
											document.getElementById("logs_"+id).appendChild(tempTable);
											document.getElementById("logs_"+id).style.display = "block";
										}
										return true;
									}
									request.send();
								}
								
								function showProtectSetter(id) {
									if (document.getElementById("entry" + id + "Protection")) {
										objTable = getParentByTagName("TABLE", getObject("protectedSettingIcon_" + id));
										objTr = getParentByTagName("TR", getObject("protectedSettingIcon_" + id));
										objTable.deleteRow(objTr.rowIndex + 1);
										
										document.getElementById("protectedSettingIcon_" + id).className = "protect-off-button button";
									} else {
										var request = new HTTPRequest("<?=$blogURL?>/owner/entry/protect/getPassword/" + id);
										request.onSuccess = function () {
											objTable = getParentByTagName("TABLE", getObject("protectedSettingIcon_" + id));
											objTr = getParentByTagName("TR", getObject("protectedSettingIcon_" + id));
											
											newRow = objTable.insertRow(objTr.rowIndex + 1);
											newRow.id = "entry" + id + "Protection";
											newRow.className = "hidden-layer";
											
											newCell = newRow.insertCell(0);
											newCell.setAttribute("colspan", 9);
											newCell.setAttribute("align", "right");
											newSection = document.createElement("DIV");
											newSection.className = "layer-section";
											newSection.innerHTML = '<label for="entry' + id + 'Password"><?=_t('비밀번호')?></label><span class="divider"> | </span><input type="text" id="entry' + id + 'Password" class="password-input" value="' + this.getText("/response/password") + '" maxlength="16" onkeydown="if (event.keyCode == 13) protectEntry(' + id + ')" /> ';
											
											tempLink = document.createElement("A");
											tempLink.className = "edit-button button";
											tempLink.setAttribute("href", "#void");
											tempLink.setAttribute("onclick", "protectEntry(" + id + ")");
											tempLink.innerHTML = '<span class="text"><?=_t('수정')?></span>';
											
											newSection.appendChild(tempLink);
											
											newCell.appendChild(newSection);
											
											document.getElementById("protectedSettingIcon_" + id).className = "protect-on-button button";
										}
										request.onError = function () {
											alert("비밀번호를 가져오지 못했습니다.");
										}
										request.send();
									}
									return;
								}
								
								function showTrackbackSender(id) {
									if (document.getElementById("trackbackSender_" + id)) {
										objTable = getParentByTagName("TABLE", getObject("trackbackIcon_" + id));
										objTr = getParentByTagName("TR", getObject("trackbackIcon_" + id));
										objTable.deleteRow(objTr.rowIndex + 1);
										
										document.getElementById("trackbackIcon_" + id).className = "trackback-off-button button";
									} else {
										objTable = getParentByTagName("TABLE", getObject("trackbackIcon_" + id));
										objTr = getParentByTagName("TR", getObject("trackbackIcon_" + id));
										
										newRow = objTable.insertRow(objTr.rowIndex + 1);
										newRow.id = "trackbackSender_" + id;
										newRow.className = "hidden-layer";
										
										newCell = newRow.insertCell(0);
										newCell.setAttribute("colspan", "9");
										newCell.setAttribute("align", "right");
										
										newSection = document.createElement("DIV");
										newSection.className = "layer-section";
										newSection.innerHTML = '<label for="trackbackForm_' + id + '"><?=_t('트랙백 주소')?></label><span class="divider"> | </span><input type="text" id="trackbackForm_' + id + '" class="text-input" name="trackbackURL" value="http://" size="50" onkeydown="if (event.keyCode == 13) sendTrackback(' + id + ')" /> ';
										
										tempLink = document.createElement("A");
										tempLink.className = "send-button button";
										tempLink.setAttribute("href", "#void");
										tempLink.setAttribute("onclick", "sendTrackback(" + id + ")");
										tempLink.innerHTML = '<span class="text"><?=_t('전송')?></span>';
										
										newDiv = document.createElement("DIV");
										newDiv.id = "logs_" + id;
										newDiv.className = "trackback-log-box";
										newDiv.style.display = "none";
										
										newSection.appendChild(tempLink);
										newSection.appendChild(newDiv);
										newCell.appendChild(newSection);
										
										printTrackbackLog(id);
										document.getElementById("trackbackIcon_" + id).className = "trackback-on-button button";
									}
									return;
								}
								
								function sendTrackback(id) {
									var trackbackField = document.getElementById('trackbackForm_'+id);
									var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
									request.onSuccess = function () {
										document.getElementById('trackbackForm_'+id).value = "http://";
										document.getElementById("logs_"+id).innerHTML = "";
										printTrackbackLog(id);
									}
									request.onError = function () {
										alert("<?=_t('트랙백 전송에 실패하였습니다.')?>");
									}
									request.send();
								}
								
								function toggleDeleteButton(obj) {
									index = obj.selectedIndex;
									button = document.getElementById("apply-button");
									
									if (obj.options[index].value == "delete") {
										button.className = button.className.replace("apply-button", "delete-button");
										button.innerHTML = '<span class="text"><?php echo _t('삭제')?></span>';
									} else {
										button.className = button.className.replace("delete-button", "apply-button");
										button.innerHTML = '<span class="text"><?php echo _t('적용')?></span>';
									}
								}
								
								tt_init_funcs.push(function() { activateFormElement(); });
								function activateFormElement() {
									document.getElementById('allChecked').disabled = false;
									//document.getElementById('category-move-button').style.display = "none";
								}
								
								function toggleThisTr(obj) {
									objTR = getParentByTagName("TR", obj);
									
									if (objTR.className.match('inactive')) {
										objTR.className = objTR.className.replace('inactive', 'active');
									} else {
										objTR.className = objTR.className.replace('active', 'inactive');
									}
								}
							//]]>
						</script>
						
						<div id="part-post-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('등록된 글 목록입니다')?></span></h2>
							
							<form id="categoryForm" class="data-inbox" method="post" action="<?=$blogURL?>/owner/entry">
								<div class="groupig">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									<select id="category" class="normal-class" name="category" onchange="document.getElementById('categoryForm').page.value=1; document.getElementById('categoryForm').submit()">
										<option value="0"><?php echo _t('전체')?></option>
<?php
foreach (getCategories($owner) as $category) {
?>
										<option value="<?php echo $category['id']?>"<?php echo ($category['id'] == $categoryId ? ' selected="selected"' : '')?>><?php echo htmlspecialchars($category['name'])?></option>
<?php
	foreach ($category['children'] as $child) {
?>
										<option value="<?php echo $child['id']?>"<?php echo ($child['id'] == $categoryId ? ' selected="selected"' : '')?>>&nbsp;― <?php echo htmlspecialchars($child['name'])?></option>
<?php
	}
}
?>
									</select>
									<!--a id="category-move-button" class="move-button button" href="#void"><span class="text"><?=_t('이동')?></span></a-->
								</div>
							</form>
							
							<form id="listForm" method="post" action="<?=$blogURL?>/owner/entry">
								<div class="grouping">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									
									<table class="data-inbox" cellspacing="0" cellpadding="0">
										<thead>
											<tr>
												<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" /></th>
												<th class="date"><span class="text"><?=_t('등록일자')?></span></th>
												<th class="status"><span class="text"><?=_t('상태')?></span></th>
												<th class="syndicate"><span class="text"><?=_t('발행')?></span></th>
												<th class="category"><span class="text"><?=_t('분류')?></span></th>
												<th class="title"><span class="text"><?=_t('제목')?></span></th>
												<th class="protect"><span class="text"><?=_t('보호설정')?></span></th>
												<th class="trackback"><span class="text"><?=_t('트랙백')?></span></th>
												<th class="delete"><span class="text"><?=_t('삭제')?></span></th>
											</tr>
										</thead>
										<tbody>
<?
for ($i=0; $i<sizeof($entries); $i++) {
	$entry = $entries[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($entries) - 1) ? ' last-line' : '';
?>
											<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
												<td class="date"><?=Timestamp::formatDate($entry['published'])?></td>
												<td class="status">
<?
	if ($entry['visibility'] == 0) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-on-icon" title="<?=_t('현재 비공개 상태입니다.')?>"><span class="text"><?=_t('비공개')?></span></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=protect" onclick="setEntryVisibility(<?=$entry['id']?>, 1); return false;" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span class="text"><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=public" onclick="setEntryVisibility(<?=$entry['id']?>, 2); return false;" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a></span>
<?
	} else if ($entry['visibility'] == 1) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=private" onclick="setEntryVisibility(<?=$entry['id']?>, 0); return false;" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-on-icon" title="<?=_t('현재 보호 상태입니다.')?>"><span class="text"><?=_t('보호')?></span></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=public" onclick="setEntryVisibility(<?=$entry['id']?>, 2); return false;" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a></span>
<?
	} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
													<span id="privateIcon_<?=$entry['id']?>" class="private-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=private" onclick="setEntryVisibility(<?=$entry['id']?>, 0); return false;" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a></span>
													<span id="protectedIcon_<?=$entry['id']?>" class="protected-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=protect" onclick="setEntryVisibility(<?=$entry['id']?>, 1); return false;" title="<?=_t('현재 상태를 보호로 전환합니다.')?>"><span class="text"><?=_t('보호')?></span></a></span>
													<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon" title="<?=_t('현재 공개 상태입니다.')?>"><span class="text"><?=_t('공개')?></span></span>
<?
	}
?>
												</td>
												<td class="syndicate">
<?
	if ($entry['visibility'] == 3) {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-on-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=public" onclick="setEntryVisibility(<?=$entry['id']?>, 3); return false;" title="<?=_t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.')?>"><span class="text"><?=_t('발행')?></span></a></span>
<?
	} else {
?>
													<span id="syndicatedIcon_<?=$entry['id']?>" class="syndicated-off-icon"><a href="<?php echo $blogURL?>/owner/entry/visibility/<?=$entry['id']?>?javascript=disabled&amp;command=syndicate" onclick="setEntryVisibility(<?=$entry['id']?>, 3); return false;" title="<?=_t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.')?>"><span class="text"><?=_t('비발행')?></span></a></span>
<?
	}
?>
												</td>
												<td class="category">
<?
	if (!empty($entry['categoryLabel'])) {
?>
													<a id="category_<?=$entry['id']?>" class="categorized" href="<?php echo $blogURL?>/owner/entry?category=<?php echo $entry['category']?>"><?php echo htmlspecialchars($entry['categoryLabel'])?></a>
<?
	} else {
?>
													<span class="uncategorized"><?php echo _t('분류 없음')?></span>
<?
	}
?>
												</td>
												<td class="title">
													<?=($entry['draft'] ? ('<span class="temp-icon bullet" title="' . _t('임시 저장본이 있습니다.') . '"><span>' . _t('[임시]') . '</span></span> ') : '')?><a href="<?php echo $blogURL?>/owner/entry/edit/<?=$entry['id']?>" onclick="document.getElementById('listForm').action='<?=$blogURL?>/owner/entry/edit/<?=$entry['id']?>'<?=($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')") : '')?>; document.getElementById('listForm').submit();"><?=htmlspecialchars($entry['title'])?></a>
												</td>
												<td class="protect">
<?php
if ($entry['visibility'] == 1) {
?>
													<a id="protectedSettingIcon_<?=$entry['id']?>" class="protect-off-button button" href="<?php echo $blogURL?>/owner/entry/edit/<?=$entry['id']?>#status-line" onclick="showProtectSetter('<?=$entry['id']?>'); return false;" title="<?=_t('보호 패스워드를 설정합니다.')?>"><span class="text"><?=_t('보호설정')?></span></a>
<?php
}
?>
												</td>
												<td class="trackback">
													<a id="trackbackIcon_<?=$entry['id']?>" class="trackback-off-button button" href="#void" onclick="showTrackbackSender(<?=$entry['id']?>,event)" title="<?=_t('관련글에 트랙백을 보냅니다.')?>"><span class="text"><?=_t('트랙백')?></span></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="<?php echo $blogURL?>/owner/entry/delete/<?=$entry['id']?>?javascript=disabled" onclick="deleteEntry(<?=$entry['id']?>); return false;" title="<?=_t('이 포스트를 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
												</td>
											</tr>
<?
								}
?>
										</tbody>
									</table>
									
									<hr class="hidden" />
									
									<div class="data-subbox">
										<div id="change-section" class="section">
											<h2><?php echo _t('페이지 네비게이션')?></h2>
											
											<label for="commandBox"><?=_t('선택한 글을')?></label>
											<select id="commandBox" onchange="toggleDeleteButton(this)"> 
												<option></option>
<?
	$categories = getCategories($owner);
	if (count($categories) >0) {
?>
												<optgroup class="category" label="<?=_t('아래의 카테고리로 변경합니다.')?>">
<?
		foreach ($categories as $category) {
?>
													<option class="parent-category" value="category_<?php echo $child['id']?>" label="<?=htmlspecialchars($category['name'])?>"><?=htmlspecialchars($category['name'])?></option>
<?
			foreach ($category['children'] as $child) {
?>
													<option class="child-category" value="category_<?php echo $child['id']?>" label="<?=htmlspecialchars($category['name'])?>/<?=htmlspecialchars($child['name'])?>">― <?=htmlspecialchars($child['name'])?></option>
<?
			}
		}
	}
?>
												</optgroup>
												<optgroup class="status" label="<?=_t('아래의 상태로 변경합니다.')?>">
													<option value="classify"><?=_t('비공개로 변경합니다.')?></option>
													<option value="publish"><?=_t('공개로 변경합니다.')?></option>
												</optgroup>
												<optgroup class="delete" label="<?=_t('삭제합니다.')?>">
													<option value="delete"><?=_t('삭제합니다.')?></option>
												</optgroup>
											</select>
											<a id="apply-button" class="apply-button button" href="#void" onclick="processBatch(document.getElementById('commandBox'));"><span class="text"><?=_t('적용')?></span></a>
										</div>
										
										<div id="page-section" class="section">
											<div id="page-navigation">
												<span id="total-count"><?=_f('총 %1건', empty($paging['total']) ? "0" : $paging['total'])?></span>
												<span id="page-list">
<?
//$paging['onclick_url'] = 'document.getElementById('listForm').page.value=';
//$paging['onclick_prefix'] = '';
//$paging['onclick_postfix'] = '; document.getElementById('listForm').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
echo str_repeat("\t", 12).getPagingView($paging, $pagingTemplate, $pagingItemTemplate).CRLF;
?>
												</span>
											</div>
											<div class="page-count">
												<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?>
												
												<select name="perPage" onchange="document.getElementById('listForm').page.value=1; document.getElementById('listForm').submit()">
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
												<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1).CRLF?>
											</div>
										</div>
									</div>
								</div>
							</form>
							
							<hr class="hidden" />
							
							<form id="searchForm" class="data-inbox" method="post" action="<?=$blogURL?>/owner/entry">
								<h2><?php echo _t('검색')?></h2>
								
								<div class="grouping">
									<label for="search"><?=_t('제목')?>, <?=_t('내용')?></label>
									<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.getElementById('searchForm').withSearch.value = 'on'; document.getElementById('searchForm').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<a class="search-button button" href="#void" onclick="document.getElementById('searchForm').withSearch.value = 'on'; document.getElementById('searchForm').submit();"><span class="text"><?=_t('검색')?></span></a>
								</div>
							</form>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
