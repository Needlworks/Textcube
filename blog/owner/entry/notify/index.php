<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$name = empty($_GET['name']) ? '' : $_GET['name'];
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
list($comments, $paging) = getCommentsNotifiedWithPagingForOwner($owner, '', $name, '', $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu05.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function deleteComment(id) {
									if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속 하시겠습니까?')?>"))
										return;
									var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/notify/delete/" + id);
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
									var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/notify/delete/");
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
													icon.setAttribute('title', "<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?=_t('[허용됨]')?></span>';
													icon.setAttribute('title', "<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
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
									
						<div id="part-post-notify" class="part">
							<h2 class="caption">
								<span class="main-text"><?=_t('댓글 알리미입니다')?></span>
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
							
							<div class="main-explain-box">
								<p class="explain"><?=_t('다른 사람의 블로그에 단 댓글에 대한 댓글이 등록되면 알려줍니다. 알리미가 동작하기 위해서는 댓글 작성시 \'홈페이지\' 기입란에 자신의 홈페이지의 setup.php 파일이 존재하는 경로까지 정확하게 입력하셔야 합니다.(예:setup.php 파일이 존재하는 위치가 <samp>http//www.xxx.com/tt/setup.php</samp>라면 <kbd>http//www.xxx.com/tt</kbd>까지 입력.)')?></p>
							</div>
							
							<form id="list-form" method="post" action="<?=$blogURL?>/owner/entry/notify">
								<div class="grouping">
									<input type="hidden" name="page" value="<?=$suri['page']?>" />
									<input type="hidden" name="name" value="" />
									
									<table class="data-inbox" cellspacing="0" cellpadding="0">
										<thead>
											<tr>
												<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" /></th>
												<th class="date"><span class="text"><?=_t('등록일자')?></span></th>
												<th class="site"><span class="text"><?=_t('사이트명')?></span></th>
												<th class="name"><span class="text"><?=_t('이름')?></span></th>
												<th class="content"><span class="text"><?=_t('내용')?></span></th>
												<th class="delete"><span class="text"><?=_t('삭제')?></span></th>
											</tr>
										</thead>
										<tbody>
<?
$more = false;
$mergedComments = array();
$lastVisitNotifiedPage = getPersonalization($owner, 'lastVisitNotifiedPage');
setPersonalization($owner, 'lastVisitNotifiedPage', time());
for ($i = 0; $i < count($comments); $i++) {
	array_push($mergedComments, $comments[$i]);
	$result = getCommentCommentsNotified($comments[$i]['id']);
	if (empty($_POST['search']) && empty($_POST['name'])) {
		for ($j = 0; $j < count($result); $j++) {
			array_push($mergedComments, $result[$j]);
		}
	}
}

$nameNumber = array();
for ($i=0; $i<sizeof($mergedComments); $i++) {
	$comment = $mergedComments[$i];
	
	requireComponent('Tattertools.Data.Filter');
	if (Filter::isFiltered('name', $comment['name']))
		$isNameFiltered = true;
	else
		$isNameFiltered = false;
	
	if (!isset($nameNumber[$comment['name']])) {
		$nameNumber[$comment['name']] = $i;
		$currentNumber = $i;
	} else {
		$currentNumber = $nameNumber[$comment['name']];
	}
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= $comment['parent'] ? ' reply-line' : null;
	$className .= ($i == sizeof($mergedComments) - 1) ? ' last-line' : '';
?>
											<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$comment['id']?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
												<td class="date"><?=Timestamp::formatDate($comment['written'])?></td>
												<td class="site"><a href="<?=$comment['siteUrl']?>" onclick="window.open(this.href); return false;" title="<?php echo _t('사이트를 연결합니다.')?>"><?=htmlspecialchars($comment['siteTitle'])?></a></td>
												<td class="name">
<?
	if ($isNameFiltered) {
?>
													<a id="nameFilter<?=$currentNumber?>-<?php echo $i?>" class="block-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=unblock" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', 'name'); return false;" title="<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span class="text"><?=_t('[차단됨]')?></span></a>
<?
	} else {
?>
													<a id="nameFilter<?=$currentNumber?>-<?php echo $i?>" class="unblock-icon bullet" href="<?=$blogURL?>/owner/trash/filter/change/?javascript=disabled&amp;value=<?php echo urlencode(escapeJSInAttribute($comment['name']))?>&amp;mode=name&amp;command=block" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', 'name'); return false;" title="<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span class="text"><?=_t('[허용됨]')?></span></a>
<?
	}
?>
													<a href="?name=<?=urlencode(escapeJSInAttribute($comment['name']))?>" title="<?=_t('이 이름으로 등록된 댓글 목록을 보여줍니다.')?>"><?=htmlspecialchars($comment['name'])?></a>
												</td>
												<td class="content">
<?
	if ($comment['parent']) {
		if ($lastVisitNotifiedPage > time() - 86400) {
?>
													<span class="new-icon bullet" title=">?php echo _t('새로 등록된 댓글입니다.')?>"><span class="text">[<?=_t('새 댓글')?>]</span></span>
<?
		}
	} else {										
		echo '<a class="entryURL" href="'.$comment['entryUrl'].'" onclick="window.open(this.href); return false;" title="'._t('댓글이 작성된 포스트로 직접 이동합니다.').'">';
		echo '<span class="entry-title">'.$comment['entryTitle'].'</span>';
		
		if ($comment['entryTitle'] != '' && $comment['parent'] != '') {
			echo '<span class="divider"> | </span>';
		}
		
		echo empty($comment['parent']) ? '' : "<a href=\"" . $comment['parentUrl'] . "\" onclick=\"window.open(this.href); return false;\">" . $comment['parentName'] . _t('님의 댓글에 대한 댓글') . "</a>";
		echo "</a>";
		echo !empty($comment['title']) || !empty($comment['parent']) ? '<br />' : '';
	}
?>
													<a class="commentURL" href="<?=$comment['url']?>" onclick="window.open(this.href); return false;" title="<?=_t('댓글이 작성된 위치로 직접 이동합니다.')?>"><?=htmlspecialchars($comment['comment'])?></a>
												</td>
												<td class="delete">
													<a class="delete-button button" href="<?=$blogURL?>/owner/entry/notify/delete/<?=$comment['id']?>?javascript=disabled" onclick="deleteComment(<?=$comment['id']?>); return false;" title="<?=_t('이 댓글을 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a>
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
											<span class="label"><?=_t('선택한 알림을')?></span>
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
										</div>
									</div>
								</div>
							</form>
							
							<hr class="hidden" />
							
							<form id="search-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/entry/notify">
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
