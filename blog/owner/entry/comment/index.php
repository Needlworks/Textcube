<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireComponent('Tattertools.Data.Filter');
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$name = empty($_GET['name']) ? '' : $_GET['name'];
$ip = empty($_GET['ip']) ? '' : $_GET['ip'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$page = getPersonalization($owner, 'rowsPerPage');
if (empty($_POST['perPage'])) {
	$perPage = $page;
} else if ($page != $_POST['perPage']) {
	setPersonalization($owner, 'rowsPerPage', $_POST['perPage']);
	$perPage = $_POST['perPage'];
} else {
	$perPage = $_POST['perPage'];
}
list($comments, $paging) = getCommentsWithPagingForOwner($owner, $categoryId, $name, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu01.php';
?>
							<input type="hidden" name="withSearch" value="" />
							<input type="hidden" name="name" value="" />
							<input type="hidden" name="ip" value="" />
							
							<script type="text/javascript">
								//<![CDATA[
									function deleteComment(id) {
										if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속 하시겠습니까?')?>"))
											return;
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/comment/delete/" + id);
										request.onSuccess = function () {
											document.forms[0].submit();
										}
										request.send();
									}
									
									function deleteComments() {	
										if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속 하시겠습니까?')?>"))
											return false;
										
										var oElement;
										var targets = '';
										for (i = 0; document.forms[0].elements[i]; i ++) {
											oElement = document.forms[0].elements[i];
											if ((oElement.name == "entry") && oElement.checked) {
												targets += oElement.value +'~*_)';
											}
										}
										
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/comment/delete/");
										request.onSuccess = function() {
											document.forms[0].submit();
										}
										request.send("targets=" + targets);
									}
									
									function checkAll(checked) {
										for (i = 0; document.forms[0].elements[i]; i ++)
											if (document.forms[0].elements[i].name == "entry")
												document.forms[0].elements[i].checked = checked;
									}
									
									function changeState(caller, value, no, mode) {
										try {
											if (caller.className == 'block-icon bullet') {
												var command 	= 'unblock';
											} else {
												var command 	= 'block';
											}
											var name 		= caller.getAttribute('name');
											param  	=  '?value='	+ encodeURIComponent(value);
											param 	+= '&mode=' 	+ mode;
											param 	+= '&command=' 	+ command;
											param 	+= '&id=' 	+ no;
											
											var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/trash/filter/change/" + param);
											var iconList = document.getElementsByTagName("a");	
											for (var i = 0; i < iconList.length; i++) {
												icon = iconList[i];
												if(icon.getAttribute('name') == null || icon.getAttribute('name').toLowerCase() != name.toLowerCase()) continue;
												
												if (command == 'block') {
													icon.className = 'block-icon bullet';
													icon.innerHTML = '<span class="text"><?=_t('[차단됨]')?></span>';
													if (mode == 'name') {
														icon.setAttribute('title', "<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
													} else {
														icon.setAttribute('title', "<?=_t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
													}
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?=_t('[허용됨]')?></span>';
													if (mode == 'name') {
														icon.setAttribute('title', "<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
													} else {
														icon.setAttribute('title', "<?=_t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
													}
												}
												//if(icon.getAttribute('id').toLowerCase() != id.toLowerCase())
												//?? request.presetProperty(icon.style, "display", "block");
												//else
												//?? request.presetProperty(icon.style, "display", "none");
											}
											request.send();
										} catch(e) {
											alert(e.message);
										}
									}
									
									tt_init_funcs.push(function() { activateFormElement(); });
									function activateFormElement() {
										for (i=0; i<document.forms[0].elements.length; i++) {
											if (document.forms[0].elements[i].type == "checkbox" || document.forms[0].elements[i].tagName == "SELECT") {
												document.forms[0].elements[i].disabled = false;
											}
										}
										document.getElementById("search").disabled = false;
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
							
							<div id="part-post-comment" class="part">
								<h2 class="caption">
									<select id="category" name="category" onchange="document.forms[0].page.value=1; document.forms[0].submit()" disabled="disabled">
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
									<span class="interword"><?php echo _t('분류에')?></span>
									<span class="main-text"><?php echo _t('등록된 댓글 목록입니다')?></span>
<?
if (strlen($name) > 0 || strlen($ip) > 0) {
	if (strlen($name) > 0) {
?>
									<span class="filter-codition"><?=htmlspecialchars($name)?></span>
<?
	}
	
	if (strlen($ip) > 0) {
?>
									<span class="filter-codition"><?=htmlspecialchars($ip)?></span>
<?
	}
}
?>
								</h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" /></th>
											<th class="date"><span class="text"><?=_t('등록일자')?></span></th>
											<th class="name"><span class="text"><?=_t('이름')?></span></th>
											<th class="content"><span class="text"><?=_t('내용')?></span></th>
											<th class="ip"><acronym title="Internet Protocol">ip</acronym></th>
											<th class="delete"><span class="text"><?=_t('삭제')?></span></th>
										</tr>
									</thead>
									<tbody>
<?
$nameNumber = array();
$ipNumber = array();
for ($i=0; $i<sizeof($comments); $i++) {
	$comment = $comments[$i];
	
	$filter = new Filter();
	if (Filter::isFiltered('name', $comment['name']))
		$isNameFiltered = true;
	else
		$isNameFiltered = false;
	
	if (Filter::isFiltered('ip', $comment['ip']))
		$isIpFiltered = true;
	else
		$isIpFiltered = false;
	
	if (!isset($nameNumber[$comment['name']])) {
		$nameNumber[$comment['name']] = $i;
		$currentNumber = $i;
	} else {
		$currentNumber = $nameNumber[$comment['name']];
	}
	
	if (!isset($ipNumber[$comment['ip']])) {
		$ipNumber[$comment['ip']] = $i;
		$currentIP = $i;
	} else {
		$currentIP = $ipNumber[$comment['ip']];
	}
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= $comment['parent'] ? ' reply-line' : null;
	$className .= ($i == sizeof($comments) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$comment['id']?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" disabled="disabled" /></td>
											<td class="date"><?=Timestamp::formatDate($comment['written'])?></td>
											<td class="name">
<?
	if ($isNameFiltered) {
?>
												<a class="block-icon bullet" name="name<?=$currentNumber?>block" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=unblock&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>', 'name'); return false;" title="<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
												<a class="unblock-icon bullet" name="name<?=$currentNumber?>block" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=block&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>', 'name'); return false;" title="<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
												<a href="?name=<?=escapeJSInAttribute($comment['name'])?>" title="<?=_t('이 이름으로 등록된 댓글 목록을 보여줍니다.')?>"><?=htmlspecialchars($comment['name'])?></a>
											</td>
											<td class="content">
<?
	echo '<a class="entryURL" href="'.$blogURL.'/'.$comment['entry'].'#comment'.$comment['id'].'" title="'._t('댓글이 작성된 포스트로 직접 이동합니다.').'">';
	echo '<span class="entry-title">'.$comment['title'].'</span>';
	
	if ($comment['title'] != '' && $comment['parent'] != '') {
		echo '<span class="divider"> | </span>';
	}
	
	echo empty($comment['parent']) ? '' : '<span class="explain">' . $comment['parentName'] . _t('님의 댓글에 대한 댓글') . '</span>';
	echo "</a>";
?>
												<?=((!empty($comment['title']) || !empty($comment['parent'])) ? '<br />' : '')?>
												<?=htmlspecialchars($comment['comment'])?>
							 				</td>
											<td class="ip">
<?
	if ($isIpFiltered) {
?>
												<a class="block-icon bullet" name="ip<?=$currentIP?>block" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['ip']))?>&amp;mode=ip&amp;command=unblock&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['ip'])?>', '<?=$filter->id?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
												<a class="unblock-icon bullet" name="ip<?=$currentIP?>block" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['ip']))?>&amp;mode=ip&amp;command=block&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['ip'])?>', '<?=$filter->id?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
												<a href="?ip=<?=urlencode(escapeJSInAttribute($comment['ip']))?>" title="<?=_t('이 IP로 등록된 댓글 목록을 보여줍니다.')?>"><?=$comment['ip']?></a>
											</td>
											<td class="delete">
												<a class="delete-button button" href="<?=$blogURL?>/owner/entry/comment/delete/<?=$comment['id']?>?javascript=disabled" onclick="deleteComment(<?=$comment['id']?>); return false;" title="<?=_t('이 댓글을 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
											</td>
				  						</tr>
<?
}
?>
									</tbody>
								</table>
   								
   								<hr class="hidden" />
   								
								<div class="data-subbox">
									<div id="delete-section" class="section">
										<span class="label"><?=_t('선택한 댓글을')?></span>
										<a class="delete-button button" href="#void" onclick="deleteComments();"><span class="text"><?=_t('삭제')?></span></a>
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
										<div class="page-count">
											<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?>

											<select name="perPage" onchange="document.forms[0].page.value=1; document.forms[0].submit()" disabled="disabled">					
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
									</div>
									
									<hr class="hidden" />
									
									<div id="search-section" class="section">
										<!--label for="search"><?=_t('이름')?>, <?=_t('홈페이지 이름')?>, <?=_t('내용')?></label><span class="divider"> | </span-->
										<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" disabled="disabled" />
										<a class="search-button button" href="#void" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();"><span class="text"><?=_t('검색')?></span></a>
									</div>
								</div>
							</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>