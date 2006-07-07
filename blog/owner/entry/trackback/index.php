<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$site = empty($_GET['site']) ? '' : $_GET['site'];
$ip = empty($_GET['ip']) ? '' : $_GET['ip'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = getUserSetting('rowsPerPage', 10);
if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
	setUserSetting('rowsPerPage', $_POST['perPage']);
}
list($trackbacks, $paging) = getTrackbacksWithPagingForOwner($owner, $categoryId, $site, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu02.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function changeState(caller, value, mode) {
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
										
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/trash/filter/change/" + param);
										var iconList = document.getElementsByTagName("a");	
										for (var i = 0; i < iconList.length; i++) {
											icon = iconList[i];
											if(icon.id == null || icon.id.replace(/\-[0-9]+$/, '') != name) {
												continue;
											} else {
												if (command == 'block') {
													icon.className = 'block-icon bullet';
													icon.innerHTML = '<span class="text"><?=_t('[차단됨]')?></span>';
													icon.setAttribute('title', "<?=_t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?=_t('[허용됨]')?></span>';
													icon.setAttribute('title', "<?=_t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
												}
											}
										}
										request.send();
									} catch(e) {
										alert(e.message);
									}
								}
								
								function trashTrackback(id) {
									if (!confirm("<?=_t('선택된 트랙백을 휴지통으로 옮깁니다. 계속 하시겠습니까?')?>"))
										return;
									var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/delete/" + id);
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
										request.send();
								}
								
								function trashTrackbacks() {
									try {
										if (!confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속 하시겠습니까?')?>"))
											return false;
										var oElement;
											var targets = '';
										for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
											oElement = document.getElementById('list-form').elements[i];
											if ((oElement.name == "entry") && oElement.checked) {
												targets+=oElement.value+'~*_)';
											}
										}
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/trackback/delete/");
										request.onSuccess = function() {
											document.getElementById('list-form').submit();
										}
										request.send("targets=" + targets);
									} catch(e) {
										alert(e.message);
									}
								}
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('list-form').elements[i]; i++) {
										if (document.getElementById('list-form').elements[i].name == "entry") {
											document.getElementById('list-form').elements[i].checked = checked;
											toggleThisTr(document.getElementById('list-form').elements[i]);
										}
									}
								}
								
								window.addEventListener("load", activateFormElement, false);
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
						
						<div id="part-post-trackback" class="part">
							<h2 class="caption">
								<span class="main-text"><?php echo _t('받은 트랙백 목록입니다')?></span>
<?
if (strlen($site) > 0 || strlen($ip) > 0) {
	if (strlen($site) > 0) {
?>
								<span class="filter-condition"><?=htmlspecialchars($site)?></span>
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
							
							<form id="category-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/entry/trackback">
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
							
							<form id="list-form" method="post" action="<?=$blogURL?>/owner/entry/trackback">
								<div class="grouping">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									<input type="hidden" name="site" value="" />
									<input type="hidden" name="ip" value="" />
									
									<table class="data-inbox" cellspacing="0" cellpadding="0">
										<thead>
											<tr>
												<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" /></th>
												<th class="date"><span class="text"><?=_t('등록일자')?></span></th>
												<th class="site"><span class="text"><?=_t('사이트명')?></span></th>
												<th class="category"><span class="text"><?=_t('분류')?></span></th>
												<th class="title"><span class="text"><?=_t('제목')?></span></th>
												<th class="ip"><acronym title="Internet Protocol">ip</acronym></th>
												<th class="delete"><span class="text"><?=_t('삭제')?></span></th>
											</tr>
										</thead>
										<tbody>
<?
$siteNumber = array();
for ($i=0; $i<sizeof($trackbacks); $i++) {
	$trackback = $trackbacks[$i];
	
	requireComponent('Tattertools.Data.Filter');
	$isFilterURL = Filter::isFiltered('url', $trackback['url']);
	$filteredURL = getURLForFilter($trackback['url']);

	$filter = new Filter();
	if (Filter::isFiltered('ip', $trackback['ip'])) {
		$isIpFiltered = true;
	} else {
		$isIpFiltered = false;
	}

	if (!isset($siteNumber[$trackback['site']])) {
		$siteNumber[$trackback['site']] = $i;
		$currentSite = $i;
	} else {
		$currentSite = $siteNumber[$trackback['site']];
	}
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($trackbacks) - 1) ? ' last-line' : '';
?>
											<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$trackback['id']?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
												<td class="date"><?=Timestamp::formatDate($trackback['written'])?></td>
												<td class="site">
<?
	if ($isFilterURL) {
?>
													<a id="urlFilter<?=$currentSite?>-<?php echo $i?>" class="block-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode($filteredURL)?>&amp;mode=url&amp;command=unblock" onclick="changeState(this,'<?=$filteredURL?>','url'); return false;" title="<?=_t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
													<a id="urlFilter<?=$currentSite?>-<?php echo $i?>" class="unblock-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode($filteredURL)?>&amp;mode=url&amp;command=block" onclick="changeState(this,'<?=$filteredURL?>','url'); return false;" title="<?=_t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
													<a href="?site=<?=urlencode(escapeJSInAttribute($trackback['site']))?>" title="<?=_t('이 사이트에서 보낸 트랙백 목록을 보여줍니다.')?>"><?=htmlspecialchars($trackback['site'])?></a>
												</td>
												<td class="category">
<?
	if (!empty($trackback['categoryName'])) {
?>
												<span class="categorized"><?php echo $trackback['categoryName']?></span>
<?
	} else {
?>
												<span class="uncategorized"><?php echo _t('분류 없음')?></span>
<?
	}
?>
												</td>
												<td class="title">
													<a href="<?=$trackback['url']?>" onclick="window.open(this.href); return false;" title="<?= _t('트랙백을 보낸 포스트를 보여줍니다.')?>"><?=htmlspecialchars($trackback['subject'])?></a>
												</td>
												<td class="ip">
<?
	if ($isIpFiltered) {
?>
													<a id="ipFilter<?=urlencode($trackback['ip'])?>-<?php echo $i?>" class="block-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode($trackback['ip'])?>&amp;mode=ip&amp;command=unblock" onclick="changeState(this,'<?=urlencode($trackback['ip'])?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
													<a id="ipFilter<?=urlencode($trackback['ip'])?>-<?php echo $i?>" class="unblock-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode($trackback['ip'])?>&amp;mode=ip&amp;command=block" onclick="changeState(this,'<?=urlencode($trackback['ip'])?>', 'ip'); return false;" title="<?=_t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>

													<a href="?ip=<?=urlencode(escapeJSInAttribute($trackback['ip']))?>" title="<?=_t('이 IP로 등록된 트랙백 목록을 보여줍니다.')?>"><?=$trackback['ip']?></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="<?=$blogURL?>/owner/entry/trackback/delete/<?=$trackback['id']?>?javascript=disabled" onclick="trashTrackback(<?=$trackback['id']?>); return false;" title="<?=_t('이 트랙백을 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
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
											<span class="label"><?=_t('선택한 트랙백을')?></span>
											<a class="delete-button button" href="#void" onclick="trashTrackbacks();"><span class="text"><?=_t('삭제')?></span></a>
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
							
							<form id="search-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/entry/trackback">
								<h2><?php echo _t('검색')?></h2>
								
								<div class="grouping">
									<label for="search"><?=_t('제목')?>, <?=_t('사이트명')?>, <?=_t('내용')?></label>
									<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<a class="search-button button" href="#void" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();"><span class="text"><?=_t('검색')?></span></a>
								</div>
							</form>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
