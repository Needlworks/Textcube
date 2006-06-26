<?
define('ROOT', '../../..');
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
										var request = new HTTPRequest("<?=$blogURL?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
										switch (visibility) {
											case 0:
												document.getElementById("privateIcon_" + entry).innerHTML = '<span class="text"><?=_t('비공개')?></span>';
												document.getElementById("privateIcon_" + entry).className = 'private-on-icon';
												document.getElementById("privateIcon_" + entry).setAttribute('title', '<?=_t('현재 비공개 상태입니다.')?>');
												document.getElementById("publicIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a>';
												document.getElementById("publicIcon_" + entry).className = 'public-off-icon';
												document.getElementById("publicIcon_" + entry).removeAttribute('title');
												break;
											case 2:
												document.getElementById("privateIcon_" + entry).innerHTML = '<a href="#void" onclick="setEntryVisibility('+entry+', 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a>';
												document.getElementById("privateIcon_" + entry).className = 'private-off-icon';
												document.getElementById("privateIcon_" + entry).removeAttribute('title');
												document.getElementById("publicIcon_" + entry).innerHTML = '<span class="text"><?=_t('공개')?></span>';
												document.getElementById("publicIcon_" + entry).className = 'public-on-icon';
												document.getElementById("publicIcon_" + entry).setAttribute('title', '<?=_t('현재 공개 상태입니다.')?>');
												break;
										}
										request.send();
									}
									
									function deleteEntry(id) { 
										if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
											return;
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/delete/" + id);
										request.onSuccess = function () {
											document.forms[0].submit();
										}
										request.send();
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
												if (!confirm("<?=_t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
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
									
									function searchEntry() {
										var oForm = document.forms[0];
										trimAll(oForm);
										if (!checkValue(oForm.search, "<?=_t('검색어를 입력해 주십시오.')?>")) return false;
										oForm.page.value = "";
										oForm.withSearch.value = "on";
										oForm.submit();
									}
									
									function cancelSearch() {
										var oForm = document.forms[0];
										oForm.page.value = "";
										oForm.withSearch.value = "";
										oForm.submit();
									}
								//]]>
							</script>
							
							<input type="hidden" name="withSearch" value="<?=(empty($_POST['withSearch']) ? '' : 'on')?>" />
							
							<div id="part-notice-list" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('등록된 공지 목록입니다')?></span></h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection"><input type="checkbox" class="checkbox" name="allChecked" onclick="checkAll(this.checked)" /></th>
											<th class="date"><span class="text"><?=_t('등록일자')?></span></th>
											<th class="status"><span class="text"><?=_t('상태')?></span></th>
											<th class="title"><span class="text"><?=_t('공지')?></span></th>
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
											<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.forms[0].allChecked.checked = false" /></td>
											<td class="date"><?=Timestamp::format3($entry['published'])?></td>
											<td class="status">
<?
	if ($entry['visibility'] == 0) {
?>
												<span id="privateIcon_<?=$entry['id']?>" class="private-off-icon" title="<?=_t('현재 비공개 상태입니다.')?>"><span class="text"><?=_t('비공개')?></span></span>
												<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" title="<?=_t('현재 상태를 공개로 전환합니다.')?>"><span class="text"><?=_t('공개')?></span></a></span>
<?
	} else if ($entry['visibility'] == 2 || $entry['visibility'] == 3) {
?>
												<span id="privateIcon_<?=$entry['id']?>" class="private-off-icon"><a href="#void" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" title="<?=_t('현재 상태를 비공개로 전환합니다.')?>"><span class="text"><?=_t('비공개')?></span></a></span>
												<span id="publicIcon_<?=$entry['id']?>" class="public-on-icon" title="<?=_t('현재 공개 상태입니다.')?>"><span class="text"><?=_t('공개')?></span></span>
<?
	}
?>
											</td>
											<td class="title"><a href="#void" onclick="document.forms[0].action='<?=$blogURL?>/owner/notice/edit/<?=$entry['id']?>'; document.forms[0].submit()"><?=htmlspecialchars($entry['title'])?></a></td>
											<td class="delete"><a class="delete-button button" href="#void" onclick="deleteEntry(<?=$entry['id']?>)" title="<?=_t('이 공지를 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a></td>
										</tr>
<?
}
?>
									</tbody>
								</table>
								
								<hr class="hidden" />
								
								<div class="data-subbox">
									<div id="change-section" class="section">
										<label for="commandBox"><?=_t('선택한 공지를')?></label>
										<select id="commandBox" onchange="processBatch(this.value); this.selectedIndex=0">
											<option></option>
											<optgroup class="status" label="<?=_t('아래의 상태로 변경합니다.')?>">
												<option value="classify"><?=_t('비공개로 변경합니다.')?></option>
												<option value="publish"><?=_t('공개로 변경합니다.')?></option>
											</optgroup>
										</select>
									</div>
									
									<div id="page-section" class="section">
										<div id="page-navigation">
											<span id="total-count"><?=_f('총 %1건', empty($paging['total']) ? "0" : $paging['total'])?></span>
											<span id="page-list">
<?
//$paging['url'] = 'document.forms[0].page.value=';
//$paging['prefix'] = '';
//$paging['postfix'] = '; document.forms[0].submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
											</span>
										</div>
									</div>
									
									<hr class="hidden" />
									
									<div id="search-section" class="section">
										<!--label for="search"><?=_t('공지')?>, <?=_t('설명')?></label><span class="divider"> | </span-->
										<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
										<a class="search-button button" href="#void" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();"><span class="text"><?=_t('검색')?></span></a>
									</div>
								</div>
							</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>