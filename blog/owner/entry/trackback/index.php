<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$site = empty($_POST['site']) ? '' : $_POST['site'];
$ip = empty($_POST['ip']) ? '' : $_POST['ip'];
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
list($trackbacks, $paging) = getTrackbacksWithPagingForOwner($owner, $categoryId, $site, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu02.php';
?>
							<input type="hidden" name="withSearch" value="" />
							<input type="hidden" name="site" value="" />
							<input type="hidden" name="ip" value="" />
							
							<script type="text/javascript">
								//<![CDATA[
									function changeState(caller, value, mode) {
										try {			
											if (caller.className == 'block-icon bullet') {
												var command 	= 'unblock';
											} else {
												var command 	= 'block';
											}
											var name 		= caller.getAttribute('name');
											var id 			= caller.getAttribute('id');
											param  	=  '?value='	+ encodeURIComponent(value);
											param 	+= '&mode=' 	+ mode;
											param 	+= '&command=' 	+ command;
											
											var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/trash/filter/change/" + param);
											var iconList = document.getElementsByTagName("a");	
											for (var i = 0; i < iconList.length; i++) {
												icon = iconList[i];
												if(icon.getAttribute('name') == null || icon.getAttribute('name').toLowerCase() != name.toLowerCase()) continue;
												
												if (command == 'block') {
													icon.className = 'block-icon bullet';
													icon.innerHTML = "<span><?=_t('[차단됨]')?></span>";
													icon.setAttribute('title', "<?=_t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = "<span><?=_t('[허용됨]')?></span>";
													icon.setAttribute('title', "<?=_t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
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
									
									function trashTrackback(id) {
										if (!confirm("<?=_t('선택된 트랙백을 휴지통으로 옮깁니다. 계속하시겠습니까?')?>"))
											return;
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/delete/" + id);
										request.onSuccess = function() {
											document.forms[0].submit();
										}
											request.send();
									}
									
									function trashTrackbacks() {
										try {
											if (!confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?')?>"))
												return false;
											var oElement;
												var targets = '';
											for (i = 0; document.forms[0].elements[i]; i ++) {
												oElement = document.forms[0].elements[i];
												if ((oElement.name == "entry") && oElement.checked) {
													targets+=oElement.value+'~*_)';
												}
											}
											var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/trackback/delete/");
											request.onSuccess = function() {
												document.forms[0].submit();
											}
											request.send("targets=" + targets);
										} catch(e) {
											alert(e.message);
										}
									}
									
									function checkAll(checked) {
										for (i = 0; document.forms[0].elements[i]; i ++)
											if (document.forms[0].elements[i].name == "entry")
												document.forms[0].elements[i].checked = checked;
									}
								//]]>
							</script>
							
							<div id="part-post-trackback" class="part">
								<h2 class="caption">
									<select id="category" name="category" onchange="document.forms[0].page.value=1; document.forms[0].submit()">
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
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<td class="selection"><input type="checkbox" class="checkbox" onclick="checkAll(this.checked);" /></td>
											<td class="date"><span class="text"><?=_t('등록일자')?></span></td>
											<td class="site"><span class="text"><?=_t('사이트명')?></span></td>
											<td class="category"><span class="text"><?=_t('분류')?></span></td>
											<td class="title"><span class="text"><?=_t('제목')?></span></td>
											<td class="ip"><acronym title="Internet Protocol">ip</acronym></td>
											<td class="delete"><span class="text"><?=_t('삭제')?></span></td>
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
											<td class="selection">
												<input type="checkbox" class="checkbox" name="entry" value="<?=$trackback['id']?>" />
											</td>
											<td class="date"><?=Timestamp::formatDate($trackback['written'])?></td>
											<td class="site">
<?
	if ($isFilterURL) {
?>
												<a class="block-icon bullet" name="url<?=$currentSite?>block" href="#void" onclick="changeState(this,'<?=$filteredURL?>','url')" title="<?=_t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
												<a class="unblock-icon bullet" name="url<?=$currentSite?>block" href="#void" onclick="changeState(this,'<?=$filteredURL?>','url')" title="<?=_t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
												<a href="#void" onclick="document.forms[0].site.value='<?=escapeJSInAttribute($trackback['site'])?>'; document.forms[0].submit();" title="<?=_t('이 사이트에서 보낸 트랙백 목록을 보여줍니다.')?>"><?=htmlspecialchars($trackback['site'])?></a>
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
												<a href="#void" onclick="window.open('<?=$trackback['url']?>')" title="<?= _t('트랙백을 보낸 포스트를 보여줍니다.')?>"><?=htmlspecialchars($trackback['subject'])?></a>
											</td>
											<td class="ip">
<?
	if ($isIpFiltered) {
?>
												<a class="block-icon bullet" name="ip<?=urlencode($trackback['ip'])?>block" href="#void" onclick="changeState(this,'<?=urlencode($trackback['ip'])?>', 'ip')" title="<?=_t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
												<a class="unblock-icon bullet" name="ip<?=urlencode($trackback['ip'])?>block" href="#void" onclick="changeState(this,'<?=urlencode($trackback['ip'])?>', 'ip')" title="<?=_t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>

												<a href="#void" onclick="document.forms[0].ip.value='<?=escapeJSInAttribute($trackback['ip'])?>'; document.forms[0].submit();" title="<?=_t('이 IP로 등록된 트랙백 목록을 보여줍니다.')?>"><?=$trackback['ip']?></a>
											</td>
											<td class="delete">
												<a class="delete-button button" href="#void" onclick="trashTrackback(<?=$trackback['id']?>)" title="<?=_t('이 트랙백을 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
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
											<span id="total-count"><?=_t('총')?> <?=$paging['total']?><?=_t('건')?></span>
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
									</div>
									
									<hr class="hidden" />
									
									<div id="search-section" class="section">
										<!--label for="search"><?=_t('이름')?>, <?=_t('홈페이지 이름')?>, <?=_t('내용')?></label><span class="divider"> | </span-->
										<input type="text" id="search" class="text-input" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
										<a class="search-button button" href="#void" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();"><span class="text"><?=_t('검색')?></span></a>
									</div>
								</div>
							</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>
