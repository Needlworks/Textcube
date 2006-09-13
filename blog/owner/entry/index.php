<?php
define('ROOT', '../../..');

if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];

$IV = array(
	'GET' => array(
		'category' => array('int', 'mandatory' => false),
		'page' => array('int', 1, 'default' => 1),
		'search' => array('string', 'mandatory' => false)
	),
	'POST' => array(
		'category' => array('int', 'mandatory' => false),
		'categoryAtHome' => array('int', 'mandatory' => false),
		'perPage' => array('int', 1, 'mandatory' => false),
		'search' => array('string', 'mandatory' => false),
		'withSearch' => array(array('on'), 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
publishEntries();

// 카테고리 설정.
if (isset($_POST['category'])) {
	$categoryId = $_POST['category'];
} else if (isset($_GET['category'])) {
	$categoryId = $_GET['category'];
} else if (isset($_POST['categoryAtHome'])) {
	$categoryId = $_POST['categoryAtHome'];
} else {
	$categoryId = 0;	
}

// 찾기 키워드 설정.
$searchKeyword = NULL;
if (isset($_POST['search']) && !empty($_POST['search']))
	$searchKeyword = trim($_POST['search']);
else if (isset($_GET['search']) && !empty($_GET['search']))
	$searchKeyword = trim($_GET['search']);

// 페이지당 출력되는 포스트 수.
$perPage = getUserSetting('rowsPerPage', 10);
if ( isset($_POST['perPage']) && (in_array($_POST['perPage'], array(10, 15, 20, 25, 30)))) {
	if ($_POST['perPage'] != $perPage) {
		setUserSetting('rowsPerPage', $_POST['perPage']);
		$perPage = $_POST['perPage'];
	}
}

// 컨텐츠 목록 생성.
list($entries, $paging) = getEntriesWithPagingForOwner($owner, $categoryId, $searchKeyword, $suri['page'], $perPage);

// query string 생성.
$paging['postfix'] = NULL;
if ($categoryId != 0)
	$paging['postfix'] .= "&amp;category=$categoryId";
if (!empty($searchKeyword))
	$paging['postfix'] .= '&amp;search='.urlencode($searchKeyword);

require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu00.php';
?>
						<script type="text/javascript">
							//<![CDATA[
<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
								window.addEventListener("load", checkTattertoolsVersion, false);
								function checkTattertoolsVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
}
if (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION) {
?>
								window.addEventListener("load", checkTattertoolsVersion, false);
								function checkTattertoolsVersion() {
									if (confirm("<?php echo _t('태터툴즈 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
}
?>
								function setEntryVisibility(entry, visibility) {
									if ((visibility < 0) || (visibility > 3))
										return false;
									
									if (visibility == 3 && document.getElementById("syndicatedIcon_" + entry).style.backgroundPosition == 'left bottom') {
										visibility = 2;
									}
								
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
									
									request.onSuccess = function () {
										switch (visibility) {
											case 0:
												document.getElementById("privateIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('비공개');?></span>';
												document.getElementById("privateIcon_" + entry).className = 'private-on-icon';
												document.getElementById("privateIcon_" + entry).setAttribute('title', '<?php echo _t('현재 비공개 상태입니다.');?>');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=public" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a>';
												document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
												document.getElementById("publicIcon_" + entry).removeAttribute('title');
																									
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?php echo _t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.');?>"><span class="text"><?php echo _t('비발행');?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].innerHTML = "";
												
												break;
											case 1:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('보호');?></span>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-on-icon';
												document.getElementById("protectedIcon_" + entry).setAttribute('title', '<?php echo _t('현재 보호 상태입니다.');?>');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=public" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a>';
												document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
												document.getElementById("publicIcon_" + entry).removeAttribute('title');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?php echo _t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.');?>"><span class="text"><?php echo _t('비발행');?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempLink = document.createElement("A");
												tempLink.id = "protectedSettingIcon_" + entry;
												tempLink.className = "protect-off-button button";
												tempLink.setAttribute("href", "<?php echo $blogURL;?>/owner/entry/edit/" + entry + "#status-line");
												tempLink.setAttribute("onclick", "showProtectSetter(" + entry + "); return false;");
												tempLink.setAttribute("title", "<?php echo _t('보호 패스워드를 설정합니다.');?>");
												tempLink.innerHTML = '<span class="text"><?php echo _t('보호설정');?></span>';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].appendChild(tempLink);
												
												break;
											case 2:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('공개');?></span>';
												document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
												document.getElementById("publicIcon_" + entry).setAttribute('title', '<?php echo _t('현재 공개 상태입니다.');?>');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=syndicate" onclick="setEntryVisibility('+entry+', 3); return false;" title="<?php echo _t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.');?>"><span class="text"><?php echo _t('비발행');?></span></a>';
												document.getElementById("syndicatedIcon_" + entry).className = 'syndicated-off-icon';
												
												tempTd = document.getElementById("protectedIcon_" + entry).parentNode;
												tempTr = tempTd.parentNode;
												tempTr.cells[6].innerHTML = "";
																							
												break;
											case 3:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												
												document.getElementById("protectedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=protect" onclick="setEntryVisibility('+entry+', 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a>';
												document.getElementById("protectedIcon_" + entry).className = 'protected-off-icon';
												document.getElementById("protectedIcon_" + entry).removeAttribute('title');
												
												document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('공개');?></span>';
												document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
												document.getElementById("publicIcon_" + entry).setAttribute('title', '<?php echo _t('현재 공개 상태입니다.');?>');
												
												document.getElementById("syndicatedIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?command=syndicate" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?php echo _t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.');?>"><span class="text"><?php echo _t('발행');?></span></a>';
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
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?command=private";
												break;
											case 1:
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?command=protect";
												break;
											case 2:
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?command=public";
												break;
											case 3:
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?command=syndicate";
												break;
										}
									}
									request.send();
								}
								
								function deleteEntry(id) {
									if (!confirm("<?php echo _t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/delete/" + id);
									request.onSuccess = function () {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										window.location.href = "<?php echo $blogURL;?>/owner/entry/delete/" + id + "?command=delete";
									}
									request.send();
								}
								
								function protectEntry(id) {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/protect/" + id);
									request.onSuccess = function () {
										objTable = getParentByTagName("TABLE", getObject("protectedSettingIcon_" + id));
										objTr = getParentByTagName("TR", getObject("protectedSettingIcon_" + id));
										objTable.deleteRow(objTr.rowIndex + 1);
										
										document.getElementById("protectedSettingIcon_" + id).className = "protect-off-button button";
									}
									request.onError = function () {
										alert("<?php echo _t('보호글의 비밀번호를 변경하지 못했습니다.');?>");
									}
									request.send("password=" + encodeURIComponent(document.getElementById("entry" + id + "Password").value));
								}
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('list-form').elements[i]; i++) {
										if (document.getElementById('list-form').elements[i].name == "entry") {
											if (document.getElementById('list-form').elements[i].checked != checked) {
												document.getElementById('list-form').elements[i].checked = checked;
												toggleThisTr(document.getElementById('list-form').elements[i]);
											}
										}
									}
								}
								
								function processBatch(obj) {	
									mode = obj.value;
									if (mode.match('^category_')) {
										mode = 'category';
									}
									
									var entries = '';
									var isSelected = false;
									for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
										var oElement = document.getElementById('list-form').elements[i];
										if ((oElement.name == "entry") && oElement.checked) {
											isSelected = true;
											break;
										}
									}
									if (!isSelected) {
										alert("<?php echo _t('적용할 글을 선택해 주십시오.');?>")
										return false;
									}
									switch (mode) {
										case 'classify':
											for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
												var oElement = document.getElementById('list-form').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													setEntryVisibility(oElement.value, 0);
											}
											break;
										case 'publish':
											for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
												var oElement = document.getElementById('list-form').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													setEntryVisibility(oElement.value, 2);
											}
											break;
										case 'delete':
											if (!confirm("<?php echo _t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?');?>"))
												return false;
											var targets = new Array();
											for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
												var oElement = document.getElementById('list-form').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													targets[targets.length] = oElement.value;
											}
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/delete/");
											request.onSuccess = function () {
												document.getElementById('list-form').submit();
											}
											request.send("targets="+targets.join(","));
											break;
										case 'category':
											var targets = "";
											var currentCategory = "<?php echo $categoryId;?>";
											var category = obj.options[obj.options.selectedIndex].value.replace('category_', '');
											var label = obj.options[obj.options.selectedIndex].getAttribute('label');
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/changeCategory/");
											for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
												var oElement = document.getElementById('list-form').elements[i];
												if ((oElement.name == "entry") && oElement.checked) {
													targets += oElement.value + ',';
												}
											}
											targets = targets.substr(0,targets.length-1);
											
											if (targets == '') {
												return false;
											}
											
											request.onSuccess = function () {
												hrefString = "<?php echo $blogURL;?>/owner/entry/";
												queryPage = document.getElementById('list-form').page.value;
												queryCategory = document.getElementById('category-form').category.value;
												querySearch = document.getElementById('search-form').search.value;
												
												queryString = "";
												if (queryPage != "") {
													queryString = "page=" + queryPage;
												}
												if (queryCategory != "") {
													if (queryString == "")
														queryString = queryCategory;
													else
														queryString = queryString + "&category=" + queryCategory;
												}
												if (querySearch != "") {
													if (queryString == "")
														queryString = querySearch;
													else
														queryString = queryString + "&search=" + querySearch;	
												}
												if (queryString != "")
													queryString = "?" + queryString;
												
												// 공지/키워드 화면에서 선택된 포스트를 카테고리로 변경할 경우 or 카테고리 리스트에서 포스트를 공지/키워드 포스트로 전환할 경우.
												if (((category == -1 || category == -2) && currentCategory != category) || ((currentCategory == -1 || currentCategory == -2) && (category != -1 && category != -2))) {
													window.location.href = hrefString + queryString;
												} else {
													for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
														var oElement = document.getElementById('list-form').elements[i];
														if ((oElement.name == "entry") && oElement.checked) {
															id = "category_" + oElement.value;
															if (label == "<?php echo _t('분류 없음');?>") {
																document.getElementById(id).className = "uncategorized";
															} else {
																document.getElementById(id).className = "categorized";
															}
															document.getElementById(id).innerHTML = label;
														}
													}
												}
											}
											
											request.onError = function () {
												alert("<?php echo _t('분류를 변경하지 못했습니다.');?>");
											}
											
											// "분류없음"이 DB 상으로는 카테고리 "0"으로 설정되어야 함.
											if (category == -3)
												category = 0;
											request.send("category="+category+"&targets="+targets);
											break;
									}
									obj.selectedIndex = 0
								}
								
								function removeTrackbackLog(id,entry) {
									if (confirm("<?php echo _t('선택된 글걸기를 삭제합니다. 계속 하시겠습니까?');?>")) {
										var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/trackback/log/remove/" + id);
										request.onSuccess = function () {
											document.getElementById("logs_"+entry).innerHTML = "";
											printTrackbackLog(entry);
										}
										request.onError = function () {
											alert("<?php echo _t('글걸기를 삭제하는데 실패했습니다.');?>");
										}
										request.send();
									}
								}
								
								function printTrackbackLog(id) {
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/trackback/log/" + id);
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
												tempA.setAttribute("title", "<?php echo _t('이 글걸기를 삭제합니다.');?>");
												
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
										var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/protect/getPassword/" + id);
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
											newSection.innerHTML = '<label for="entry' + id + 'Password"><?php echo _t('비밀번호');?></label><span class="divider"> | </span><input type="text" id="entry' + id + 'Password" class="password-input" value="' + this.getText("/response/password") + '" maxlength="16" onkeydown="if (event.keyCode == 13) protectEntry(' + id + ')" /> ';
											
											tempLink = document.createElement("A");
											tempLink.className = "edit-button button";
											tempLink.setAttribute("href", "#void");
											tempLink.setAttribute("onclick", "protectEntry(" + id + ")");
											tempLink.innerHTML = '<span class="text"><?php echo _t('수정');?></span>';
											
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
										newSection.innerHTML = '<label for="trackbackForm_' + id + '"><?php echo _t('글걸기 주소');?></label><span class="divider"> | </span><input type="text" id="trackbackForm_' + id + '" class="input-text" name="trackbackURL" value="http://" size="50" onkeydown="if (event.keyCode == 13) sendTrackback(' + id + ')" /> ';
										
										tempLink = document.createElement("A");
										tempLink.className = "send-button button";
										tempLink.setAttribute("href", "#void");
										tempLink.setAttribute("onclick", "sendTrackback(" + id + ")");
										tempLink.innerHTML = '<span class="text"><?php echo _t('전송');?></span>';
										
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
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
									request.onSuccess = function () {
										document.getElementById('trackbackForm_'+id).value = "http://";
										document.getElementById("logs_"+id).innerHTML = "";
										printTrackbackLog(id);
									}
									request.onError = function () {
										alert("<?php echo _t('글걸기에 실패하였습니다.');?>");
									}
									request.send();
								}
								
								function toggleDeleteButton(obj) {
									index = obj.selectedIndex;
									button = document.getElementById("apply-button");
									
									if (obj.options[index].value == "delete") {
										button.className = button.className.replace("apply-button", "delete-button");
										button.value = '<?php echo _t('삭제');?>';
									} else {
										button.className = button.className.replace("delete-button", "apply-button");
										button.value = '<?php echo _t('적용');?>';
									}
								}
								
								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
									document.getElementById('allChecked').disabled = false;
									removeItselfById('category-move-button');
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
							<h2 class="caption"><span class="main-text"><?php
	if ($categoryId == -1) { 
		echo _t('등록된 키워드 목록입니다');
	} else if ($categoryId == -2) {
		echo _t('등록된 공지 목록입니다');
	} else {
		echo _t('등록된 글 목록입니다');
	}
?></span></h2>

							<form id="category-form" class="category-box" method="post" action="<?php echo $blogURL;?>/owner/entry">
								<div class="section">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
									
									<select id="category" name="category" onchange="document.getElementById('category-form').page.value=1; document.getElementById('category-form').submit()">
										<optgroup class="category" label="<?php echo _t('글 종류');?>">
											<option value="-2"<?php echo ($categoryId == -2 ? ' selected="selected"' : '');?>><?php echo _t('공지');?></option>
											<option value="-1"<?php echo ($categoryId == -1 ? ' selected="selected"' : '');?>><?php echo _t('키워드');?></option>
										</optgroup>
										<optgroup class="category" label="<?php echo _t('분류');?>">
											<option value="0"<?php echo ($categoryId == 0 ? ' selected="selected"' : '');?>><?php echo htmlspecialchars(getCategoryNameById($owner,0) ? getCategoryNameById($owner,0) : _t('전체'));?></option>
<?php
foreach (getCategories($owner) as $category) {
	if ($category['id'] != 0) {
?>
											<option value="<?php echo $category['id'];?>"<?php echo ($category['id'] == $categoryId ? ' selected="selected"' : '');?>><?php echo htmlspecialchars($category['name']);?></option>
<?php
	}
	foreach ($category['children'] as $child) {
		if ($category['id'] != 0) {
?>
											<option value="<?php echo $child['id'];?>"<?php echo ($child['id'] == $categoryId ? ' selected="selected"' : '');?>>&nbsp;― <?php echo htmlspecialchars($child['name']);?></option>
<?php
		}
	}
}
?>
											<option value="-3"<?php echo ($categoryId == -3 ? ' selected="selected"' : '');?>><?php echo _t('(분류 없음)');?></option>
										</optgroup>
									</select>
									<input type="submit" id="category-move-button" class="move-button button" value="<?php echo _t('이동');?>" />
								</div>
							</form>
							
							<form id="list-form" method="post" action="<?php echo $blogURL;?>/owner/entry">
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" /></th>
											<th class="date"><span class="text"><?php echo _t('등록일자');?></span></th>
											<th class="status"><span class="text"><?php echo _t('상태');?></span></th>
											<th class="syndicate"><span class="text"><?php echo _t('발행');?></span></th>
											<th class="category"><span class="text"><?php echo _t('분류');?></span></th>
											<th class="title"><span class="text"><?php echo _t('제목');?></span></th>
											<th class="protect"><span class="text"><?php echo _t('보호설정');?></span></th>
											<th class="trackback"><span class="text"><?php echo _t('글걸기');?></span></th>
											<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
for ($i=0; $i<sizeof($entries); $i++) {
	$entry = $entries[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($entries) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?php echo $entry['id'];?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
											<td class="date"><?php echo Timestamp::formatDate($entry['published']);?></td>
											<td class="status">
<?php
	if ($entry['visibility'] == 0) {
?>
												<span id="privateIcon_<?php echo $entry['id'];?>" class="private-on-icon" title="<?php echo _t('현재 비공개 상태입니다.');?>"><span class="text"><?php echo _t('비공개');?></span></span>
												<span id="protectedIcon_<?php echo $entry['id'];?>" class="protected-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=protect" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a></span>
												<span id="publicIcon_<?php echo $entry['id'];?>" class="public-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=public" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a></span>
<?php
	} else if ($entry['visibility'] == 1) {
?>
												<span id="privateIcon_<?php echo $entry['id'];?>" class="private-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=private" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a></span>
												<span id="protectedIcon_<?php echo $entry['id'];?>" class="protected-on-icon" title="<?php echo _t('현재 보호 상태입니다.');?>"><span class="text"><?php echo _t('보호');?></span></span>
												<span id="publicIcon_<?php echo $entry['id'];?>" class="public-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=public" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a></span>
<?php
	} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
												<span id="privateIcon_<?php echo $entry['id'];?>" class="private-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=private" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a></span>
												<span id="protectedIcon_<?php echo $entry['id'];?>" class="protected-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=protect" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a></span>
												<span id="publicIcon_<?php echo $entry['id'];?>" class="public-on-icon" title="<?php echo _t('현재 공개 상태입니다.');?>"><span class="text"><?php echo _t('공개');?></span></span>
<?php
	}
?>
											</td>
											<td class="syndicate">
<?php
	if ($entry['visibility'] == 3) {
?>
												<span id="syndicatedIcon_<?php echo $entry['id'];?>" class="syndicated-on-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=public" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 2); return false;" title="<?php echo _t('발행되었습니다. 클릭하시면 비발행으로 전환합니다.');?>"><span class="text"><?php echo _t('발행');?></span></a></span>
<?php
	} else {
?>
												<span id="syndicatedIcon_<?php echo $entry['id'];?>" class="syndicated-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?command=syndicate" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 3); return false;" title="<?php echo _t('발행되지 않았습니다. 클릭하시면 발행으로 전환합니다.');?>"><span class="text"><?php echo _t('비발행');?></span></a></span>
<?php
	}
?>
											</td>
											<td class="category">
<?php
	if ($entry['category'] == 0) {
?>
												<a id="category_<?php echo $entry['id'];?>" class="uncategorized" href="<?php echo $blogURL;?>/owner/entry?category=-3"><?php echo _t('분류 없음');?></a>
<?php
	} else if (!empty($entry['categoryLabel'])) {
?>
												<a id="category_<?php echo $entry['id'];?>" class="categorized" href="<?php echo $blogURL;?>/owner/entry?category=<?php echo $entry['category'];?>"><?php echo htmlspecialchars($entry['categoryLabel']);?></a>
<?php
	} else if ($categoryId == -2) {
?>
												<span class="notice"><?php echo _t('공지');?></span>
<?php
	} else if ($categoryId == -1) {
?>
												<span class="keyword"><?php echo _t('키워드');?></span>
<?php
	}
?>
											</td>
											<td class="title">
												<?php echo ($entry['draft'] ? ('<span class="temp-icon bullet" title="' . _t('임시 저장본이 있습니다.') . '"><span>' . _t('[임시]') . '</span></span> ') : '');?>
<?php
	if ($categoryId == -1) {
		$editmode = 'keyword';
	} else if ($categoryId == -2) {
		$editmode = 'notice';
	} else {
		$editmode = 'entry';
	}
?>
												<a href="<?php echo $blogURL;?>/owner/<?php echo $editmode;?>/edit/<?php echo $entry['id'];?>" onclick="document.getElementById('list-form').action='<?php echo $blogURL;?>/owner/<?php echo $editmode;?>/edit/<?php echo $entry['id'];?>'<?php echo ($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')") : '');?>; document.getElementById('list-form').submit(); return false;"><?php echo htmlspecialchars($entry['title']);?></a>
											</td>
											<td class="protect">
<?php
	if ($entry['visibility'] == 1) {
?>
												<a id="protectedSettingIcon_<?php echo $entry['id'];?>" class="protect-off-button button" href="<?php echo $blogURL;?>/owner/entry/edit/<?php echo $entry['id'];?>#status-line" onclick="showProtectSetter('<?php echo $entry['id'];?>'); return false;" title="<?php echo _t('보호 패스워드를 설정합니다.');?>"><span class="text"><?php echo _t('보호설정');?></span></a>
<?php
	}
?>
											</td>
											<td class="trackback">
												<a id="trackbackIcon_<?php echo $entry['id'];?>" class="trackback-off-button button" href="#void" onclick="showTrackbackSender(<?php echo $entry['id'];?>,event)" title="<?php echo _t('관련글에 트랙백을 보냅니다.');?>"><span class="text"><?php echo _t('트랙백');?></span></a>
											</td>
											<td class="delete">
												<a class="delete-button button" href="<?php echo $blogURL;?>/owner/entry/delete/<?php echo $entry['id'];?>" onclick="deleteEntry(<?php echo $entry['id'];?>); return false;" title="<?php echo _t('이 포스트를 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a>
											</td>
										</tr>
<?php
}
?>
									</tbody>
								</table>
								
								<hr class="hidden" />
								
								<div class="data-subbox">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
									
									<div id="change-section" class="section">
										<h2><?php echo _t('페이지 네비게이션');?></h2>
										
										<label for="commandBox"><?php echo _t('선택한 글을');?></label>
										<select id="commandBox" onchange="toggleDeleteButton(this)"> 
											<option></option>
<?php
	$categories = getCategories($owner);
	if (count($categories) >0) {
?>
											<optgroup class="category" label="<?php echo _t('아래의 분류로 변경합니다.');?>">
<?php
		foreach ($categories as $category) {
			if ($category['id']!= 0) {
?>
												<option class="parent-category" value="category_<?php echo $category['id'];?>" label="<?php echo htmlspecialchars($category['name']);?>"><?php echo htmlspecialchars($category['name']);?></option>
<?php
			}
			foreach ($category['children'] as $child) {
				if ($category['id']!= 0) {
?>
												<option class="child-category" value="category_<?php echo $child['id'];?>" label="<?php echo htmlspecialchars($category['name']);?>/<?php echo htmlspecialchars($child['name']);?>">― <?php echo htmlspecialchars($child['name']);?></option>
<?php
				}
			}
		}
?>
												<option class="parent-category" value="category_-3" label="<?php echo _t('분류 없음');?>">(<?php echo _t('분류 없음');?>)</option>
<?php
	}
?>
											</optgroup>
											<optgroup class="status" label="<?php echo _t('아래의 상태로 변경합니다.');?>">
												<option value="classify"><?php echo _t('비공개로 변경합니다.');?></option>
												<option value="publish"><?php echo _t('공개로 변경합니다.');?></option>
											</optgroup>
											<optgroup class="category" label="<?php echo _t('아래의 글 종류로 변경합니다.');?>">
												<option class="parent-category" value="category_-2" label="<?php echo _t('공지');?>"><?php echo _t('공지');?></option>
												<option class="parent-category" value="category_-1" label="<?php echo _t('키워드');?>"><?php echo _t('키워드');?></option>
											</optgroup>
											<optgroup class="delete" label="<?php echo _t('삭제합니다.');?>">
												<option value="delete"><?php echo _t('삭제합니다.');?></option>
											</optgroup>
										</select>
										<input type="button" id="apply-button" class="apply-button input-button" value="<?php echo _t('적용');?>" onclick="processBatch(document.getElementById('commandBox'));" />
									</div>
									
									<div id="page-section" class="section">
										<div id="page-navigation">
											<span id="total-count"><?php echo _f('총 %1건', empty($paging['total']) ? "0" : $paging['total']);?></span>
											<span id="page-list">
<?php
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
echo str_repeat("\t", 12).getPagingView($paging, $pagingTemplate, $pagingItemTemplate).CRLF;
?>
											</span>
										</div>
										<div class="page-count">
											<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0);?>
											
											<select name="perPage" onchange="document.getElementById('list-form').page.value=1; document.getElementById('list-form').submit()">
<?php
for ($i = 10; $i <= 30; $i += 5) {
	if ($i == $perPage) {
?>
												<option value="<?php echo $i;?>" selected="selected"><?php echo $i;?></option>
<?php
	} else {
?>
												<option value="<?php echo $i;?>"><?php echo $i;?></option>
<?php
	}
}
?>
											</select>
											<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1).CRLF;?>
										</div>
									</div>
								</div>
							</form>
							
							<hr class="hidden" />
							
							<form id="search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/entry">
								<h2><?php echo _t('검색');?></h2>
								
								<div class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($searchKeyword);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
