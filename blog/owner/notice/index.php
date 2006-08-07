<?php
define('ROOT', '../../..');
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	),
	'POST' => array(
		'withSearch' => array(array('on'), 'mandatory' => false),
		'search' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
$search = empty($_POST['withSearch']) ? '' : $_POST['search'];
list($entries, $paging) = getNoticesWithPaging($owner, $search, $suri['page'], 10);
require ROOT . '/lib/piece/owner/header7.php';
require ROOT . '/lib/piece/owner/contentMenu70.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function setEntryVisibility(entry, visibility) {
									if ((visibility < 0) || (visibility > 3))
										return false;
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
									switch (visibility) {
										case 0:
											document.getElementById("privateIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('비공개');?></span>';
											document.getElementById("privateIcon_" + entry).className = 'private-on-icon';
											document.getElementById("privateIcon_" + entry).setAttribute('title', '<?php echo _t('현재 비공개 상태입니다.');?>');
											
											document.getElementById("publicIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=public" onclick="setEntryVisibility('+entry+', 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a>';
											document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
											document.getElementById("publicIcon_" + entry).removeAttribute('title');
											
											break;
										case 2:
											document.getElementById("privateIcon_" + entry).innerHTML = '<a href="<?php echo $blogURL;?>/owner/entry/edit/' + entry + '?javascript=disabled&amp;command=private" onclick="setEntryVisibility('+entry+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a>';
											document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
											document.getElementById("privateIcon_" + entry).removeAttribute('title');
											
											document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?php echo _t('공개');?></span>';
											document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
											document.getElementById("publicIcon_" + entry).setAttribute('title', '<?php echo _t('현재 공개 상태입니다.');?>');
																						
											break;
									}
									request.onError = function () {
										switch (visibility) {
											case 0:
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=private";
												break;
											case 2:
												window.location.href = "<?php echo $blogURL;?>/owner/entry/visibility/" + id + "?javascript=disabled&amp;command=public";
												break;
										}
									}
									request.send();
								}
								
								function deleteEntry(id) { 
									if (!confirm("<?php echo _t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/entry/delete/" + id);
									request.onSuccess = function () {
										document.getElementById('list-form').submit();
									}
									request.send();
								}
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('list-form').elements[i]; i++) {
										if (document.getElementById('list-form').elements[i].name == "entry") {
											document.getElementById('list-form').elements[i].checked = checked;
											toggleThisTr(document.getElementById('list-form').elements[i]);
										}
									}
								}
								
								function processBatch(mode) {
									var entries = '';
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
											var targets = "";
											for (var i = 0; i < document.getElementById('list-form').elements.length; i++) {
												var oElement = document.getElementById('list-form').elements[i];
												if ((oElement.name == "entry") && oElement.checked)
													targets += oElement.value +'~*_)';
											}
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/delete/");
											request.onSuccess = function () {
												document.getElementById('list-form').submit();
											}
											request.send("targets="+targets);
											break;
									}
								}
								
								function searchEntry() {
									var oForm = document.search-form;
									trimAll(oForm);
									if (!checkValue(oForm.search, "<?php echo _t('검색어를 입력해 주십시오.');?>")) return false;
									oForm.page.value = "";
									oForm.withSearch.value = "on";
									oForm.submit();
								}
								
								function cancelSearch() {
									var oForm = document.search-form;
									oForm.page.value = "";
									oForm.withSearch.value = "";
									oForm.submit();
								}
								
								window.addEventListener("load", activateFormElement, false);
								function activateFormElement() {
									document.getElementById('allChecked').disabled = false;
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
						
						<div id="part-notice-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('등록된 공지 목록입니다');?></span></h2>
							
							<form id="list-form" method="post" action="<?php echo $blogURL;?>/owner/notice">
								<div class="grouping">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
									
									<table class="data-inbox" cellspacing="0" cellpadding="0">
										<thead>
											<tr>
												<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked)" disabled="disabled" /></th>
												<th class="date"><span class="text"><?php echo _t('등록일자');?></span></th>
												<th class="status"><span class="text"><?php echo _t('상태');?></span></th>
												<th class="title"><span class="text"><?php echo _t('공지');?></span></th>
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
												<td class="date"><?php echo Timestamp::format3($entry['published']);?></td>
												<td class="status">
<?php
	if ($entry['visibility'] == 0) {
?>
													<span id="privateIcon_<?php echo $entry['id'];?>" class="private-on-icon" title="<?php echo _t('현재 비공개 상태입니다.');?>"><span class="text"><?php echo _t('비공개');?></span></span>
													<span id="publicIcon_<?php echo $entry['id'];?>" class="public-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?javascript=disabled&amp;command=public" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a></span>
<?php
	} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
													<span id="privateIcon_<?php echo $entry['id'];?>" class="private-off-icon"><a href="<?php echo $blogURL;?>/owner/entry/visibility/<?php echo $entry['id'];?>?javascript=disabled&amp;command=private" onclick="setEntryVisibility(<?php echo $entry['id'];?>, 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a></span>
													<span id="publicIcon_<?php echo $entry['id'];?>" class="public-on-icon" title="<?php echo _t('현재 공개 상태입니다.');?>"><span class="text"><?php echo _t('공개');?></span></span>
<?php
	}
?>
												</td>
												<td class="title"><a href="<?php echo $blogURL;?>/owner/notice/edit/<?php echo $entry['id'];?>"><?php echo htmlspecialchars($entry['title']);?></a></td>
												<td class="delete"><a class="delete-button button" href="<?php echo $blogURL;?>/owner/notice/delete/<?php echo $entry['id'];?>?javascript=disabled" onclick="deleteEntry(<?php echo $entry['id'];?>); return false;" title="<?php echo _t('이 공지를 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
											</tr>
<?php
}
?>
										</tbody>
									</table>
									
									<hr class="hidden" />
									
									<div class="data-subbox">
										<div id="change-section" class="section">
											<label for="commandBox"><?php echo _t('선택한 공지를');?></label>
											<select id="commandBox" onchange="processBatch(this.value); this.selectedIndex=0">
												<option></option>
												<optgroup class="status" label="<?php echo _t('아래의 상태로 변경합니다.');?>">
													<option value="classify"><?php echo _t('비공개로 변경합니다.');?></option>
													<option value="publish"><?php echo _t('공개로 변경합니다.');?></option>
												</optgroup>
											</select>
										</div>
										
										<div id="page-section" class="section">
											<div id="page-navigation">
												<span id="total-count"><?php echo _f('총 %1건', empty($paging['total']) ? "0" : $paging['total']);?></span>
												<span id="page-list">
<?php
//$paging['url'] = 'document.getElementById('list-form').page.value=';
//$paging['prefix'] = '';
//$paging['postfix'] = '; document.getElementById('list-form').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
												</span>
											</div>
										</div>
									</div>
								</div>
							</form>
							
							<form id="search-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/notice">
								<h2><?php echo _t('검색');?></h2>
								
								<div class="grouping">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="text-input" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<a class="search-button button" href="#void" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();"><span class="text"><?php echo _t('검색');?></span></a>
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
