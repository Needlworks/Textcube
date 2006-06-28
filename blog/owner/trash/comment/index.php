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
list($comments, $paging) = getTrashCommentsWithPagingForOwner($owner, $categoryId, $name, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header9.php';
require ROOT . '/lib/piece/owner/contentMenu91.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function deleteComment(id) {
									if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속 하시겠습니까?')?>"))
										return;
									var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/trash/comment/delete/" + id);
									request.onSuccess = function () {
										document.getElementById('list-form').submit();
									}
									request.send();
								}
								
								function deleteComments() {	
									if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속 하시겠습니까?')?>"))
										return false;
									var oElement;
									var targets = '';
									for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
										oElement = document.getElementById('list-form').elements[i];
										if ((oElement.name == "entry") && oElement.checked) {
											targets += oElement.value +'~*_)';
										}
									}
									var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/trash/comment/delete/");
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
									request.send("targets=" + targets);
								}
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('list-form').elements[i]; i++) {
										if (document.getElementById('list-form').elements[i].name == "entry") {
											document.getElementById('list-form').elements[i].checked = checked;
											toggleThisTr(document.getElementById('list-form').elements[i]);
										}
									}
								}
								
								function changeState(caller, value, no, mode) {
									try {
										if (caller.className == 'block-icon bullet') {
											var command 	= 'unblock';
										} else {
											var command 	= 'block';
										}
										var name 		= caller.id.replace(/\-[0-9]+$/, '');
										param  	=  '?value='	+ encodeURIComponent(value);
										param 	+= '&mode=' 	+ mode;
										param 	+= '&command=' 	+ command;
										param 	+= '&id=' 	+ no;
										
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/filter/change/" + param);
										var iconList = document.getElementsByTagName("a");
										for (var i = 0; i < iconList.length; i++) {
											icon = iconList[i];
											if(icon.id == null || icon.id.replace(/\-[0-9]+$/, '') != name) {
												continue;
											} else {
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
											}
										}
										request.send();
									} catch(e) {
										alert(e.message);
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
						
						<div id="part-trash-comment" class="part">
							<h2 class="caption">
								<span class="main-text"><?php echo _t('삭제 대기중인 댓글 목록입니다')?></span>
<?
if (strlen($name) > 0 || strlen($ip) > 0) {
	if (strlen($name) > 0) {
?>
								<span class="filter-condition"><?=htmlspecialchars($name)?></span>
<?
	}
	
	if (strlen($ip) > 0) {
?>
								<span class="filter-condition"><?=htmlspecialchars($ip)?></span>
<?
	}
}
?>
							</h2>
							
							<form id="category-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/trash/comment">
								<div class="grouping">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									<select id="category" class="normal-class" name="category" onchange="document.getElementById('category-form').page.value=1; document.getElementById('category-form').submit()">
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
							
							<form id="list-form" method="post" action="<?=$blogURL?>/owner/trash/comment">
								<div class="grouping">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									<input type="hidden" name="name" value="" />
									<input type="hidden" name="ip" value="" />
									
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
	
	($i % 2) == 1 ? $className = 'even-line' : $className = 'odd-line';
	$comment['parent'] ? $className .= ' reply-line' : null;
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
	$className .= ($i == sizeof($comments) - 1) ? ' last-line' : '';
?>
											<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" name="entry" value="<?=$comment['id']?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
												<td class="date"><?=Timestamp::formatDate($comment['written'])?></td>
												<td class="name">
<?
	if ($isNameFiltered) {
?>
													<a id="nameFilter<?=$currentNumber?>-<?php echo $i?>" class="block-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=unblock&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>', 'name'); return false;" title="<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
													<a id="nameFilter<?=$currentNumber?>-<?php echo $i?>" class="unblock-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=block&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>', 'name'); return false;" title="<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
													<a href="?name=<?=urlencode(escapeJSInAttribute($comment['name']))?>" title="<?=_t('이 이름으로 등록된 댓글 목록을 보여줍니다.')?>"><?=htmlspecialchars($comment['name'])?></a>
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
													<a id="ipFilter<?=$currentIP?>-<?php echo $i?>" class="block-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['ip']))?>&amp;mode=ip&amp;command=unblock&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['ip'])?>', '<?=$filter->id?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
													<a id="ipFilter<?=$currentIP?>-<?php echo $i?>" class="unblock-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['ip']))?>&amp;mode=ip&amp;command=block&amp;id=<?=$filter->id?>" onclick="changeState(this,'<?=escapeJSInAttribute($comment['ip'])?>', '<?=$filter->id?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
													<a href="?ip=<?=urlencode(escapeJSInAttribute($comment['ip']))?>" title="<?=_t('이 IP로 등록된 댓글 목록을 보여줍니다.')?>"><?=$comment['ip']?></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="<?=$blogURL?>/owner/trash/comment/delete/<?=$comment['id']?>?javascript=disabled" onclick="deleteComment(<?=$comment['id']?>); return false;" title="<?=_t('이 댓글을 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
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
//$paging['url'] = 'document.getElementById('list-form').page.value=';
//$paging['prefix'] = '';
//$paging['postfix'] = '; document.getElementById('list-form').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
												</span>
											</div>
											<div class="page-count">
												<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?>
												<select name="perPage" onchange="document.getElementById('list-form').page.value=1; document.getElementById('list-form').submit()">					
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
									</div>
								</div>
							</form>
							
							<hr class="hidden" />
							
							<form id="search-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/trash/comment">
								<h2><?php echo _t('검색')?></h2>
								
								<div class="grouping">
									<label for="search"><?=_t('이름')?>, <?=_t('사이트명')?>, <?=_t('내용')?></label>
									<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<a class="search-button button" href="#void" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();"><span class="text"><?=_t('검색')?></span></a>
								</div>
							</form>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
