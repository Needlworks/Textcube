<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$name = empty($_POST['name']) ? '' : $_POST['name'];
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
list($comments, $paging) = getCommentsNotifiedWithPagingForOwner($owner, '', $name, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu05.php';
?>
									<script type="text/javascript">
										//<![CDATA[
											function deleteComment(id) {
												if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속하시겠습니까?')?>"))
													return;
												var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/notify/delete/" + id);
												request.onSuccess = function () {
													document.forms[0].submit();
												}
												request.send();
											}
											function deleteComments() {	
												if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속하시겠습니까?')?>"))
													return false;
												var oElement;
												var targets = '';
												for (i = 0; document.forms[0].elements[i]; i ++) {
													oElement = document.forms[0].elements[i];
													if ((oElement.name == "entry") && oElement.checked) {
														targets += oElement.value +'~*_)';
													
													}
												}
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/notify/delete/");
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
											
											function changeState(caller, value, mode) {
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

													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/filter/change/" + param);
													var iconList = document.getElementsByTagName("a");	
													for (var i = 0; i < iconList.length; i++) {
														icon = iconList[i];
														if(icon.getAttribute('name') == null || icon.getAttribute('name').toLowerCase() != name.toLowerCase()) continue;
														
														if (command == 'block') {
															icon.className = 'block-icon bullet';
															icon.innerHTML = "<span><?=_t('[차단됨]')?></span>";
															icon.setAttribute('title', "<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>");
														} else {
															icon.className = 'unblock-icon bullet';
															icon.innerHTML = "<span><?=_t('[허용됨]')?></span>";
															icon.setAttribute('title', "<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>");
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
										//]]>
									</script>
									
									<input type="hidden" name="withSearch" value="" />
									<input type="hidden" name="name" value="" />
									<input type="hidden" name="ip" value="" />
	            	
									<div id="part-post-notify" class="part">
										<h2 class="caption">
											<span class="main-text"><?=_t('다른 사람의 블로그에 단 댓글에 대한 댓글이 등록되면 알려줍니다')?></span>
<?
if (strlen($name) > 0 || strlen($ip) > 0) {
	if (strlen($name) > 0) {
?>
											<span class="divider"> : </span><span class="name"><?=htmlspecialchars($name)?></span>
<?
	}
	
	if (strlen($ip) > 0) {
?>
											<span class="divider"> : </span><span class="site"><?=htmlspecialchars($ip)?></span>
<?
	}
}
?>

											<span class="clear"></span>
										</h2>
										
										<table class="data-inbox" cellspacing="0" cellpadding="0" border="0">
											<thead>
												<tr>
													<td class="selection"><input type="checkbox" class="checkbox" onclick="checkAll(this.checked);" /></td>
													<td class="date"><span><?=_t('등록일자')?></span></td>
													<td class="site"><span><?=_t('사이트명')?></span></td>
													<td class="name"><span><?=_t('이름')?></span></td>
													<td class="content"><span><?=_t('내용')?></span></td>
													<td class="delete"><span><?=_t('삭제')?></span></td>
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
	
	if ($i == sizeof($mergedComments) - 1) {
?>
												<tr class="tr-last-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
													<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$comment['id']?>" /></td>
													<td class="date"><?=Timestamp::formatDate($comment['written'])?></td>
													<td class="site"><a href="<?=$comment['siteUrl']?>" onclick="window.open(this.href); return false;" title="사이트를 새 창으로 연결합니다."><?=htmlspecialchars($comment['siteTitle'])?></a></td>
													<td class="name">
<?
		if ($isNameFiltered) {
?>
														<a class="block-icon bullet" name="name<?=$currentNumber?>block" href="#void" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', 'name')" title="<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span><?=_t('[차단됨]')?></span></a>
<?
		} else {
?>
														<a class="unblock-icon bullet" name="name<?=$currentNumber?>block" href="#void" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>'), 'name'" title="<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span><?=_t('[허용됨]')?></span></a>
<?
		}
?>
														<a href="#void" onclick="document.forms[0].name.value='<?=escapeJSInAttribute($comment['name'])?>'; document.forms[0].submit();" title="<?=_t('이 이름으로 등록된 댓글 목록을 보여줍니다.')?>"><?=htmlspecialchars($comment['name'])?></a>
													</td>
													<td class="content">
<?
		if ($comment['parent']) {
?>
														<span class="reply-icon bullet" title="댓글에 달린 댓글입니다."><span><?=_t('[댓글의 댓글]')?></span></span>
<?
			if ($lastVisitNotifiedPage > time() - 86400) {
?>
														<span class="new-icon bullet" title="새로 등록된 댓글입니다."><span>[<?=_t('새 댓글')?>]</span></span>
<?
			}
		} else {										
			echo '<a class="entryURL" href="'.$comment['entryUrl'].'" onclick="window.open(this.href); return false;" title="'._t('댓글이 작성된 포스트로 직접 이동합니다.').'">';
			//echo '<strong>';
			echo $comment['entryTitle'];
			
			if ($comment['entryTitle'] != '' && $comment['parent'] != '') {
				echo '<span class="divider"> | </span>';
			}
			
			echo empty($comment['parent']) ? '' : "<a href=\"" . $comment['parentUrl'] . "\" onclick=\"window.open(this.href); return false;\">" . $comment['parentName'] . _t('님의 댓글에 대한 댓글') . "</a>";
			//echo "</strong>";
			echo "</a>";
			echo !empty($comment['title']) || !empty($comment['parent']) ? '<br />' : '';
		}
?>
														<a class="commentURL" href="<?=$comment['url']?>" onclick="window.open(this.href); return false;" title="<?=_t('댓글이 작성된 위치로 직접 이동합니다.')?>"><?=htmlspecialchars($comment['comment'])?></a>
													</td>
													<td class="delete">
														<a class="delete-button button" href="#void" onclick="deleteComment(<?=$comment['id']?>)" title="<?=_t('이 댓글을 삭제합니다.')?>"><span><?=_t('삭제')?></span></a>
													</td>
												</tr>
<?
	} else {
?>
												<tr class="tr-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
													<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?=$comment['id']?>" /></td>
													<td class="date"><?=Timestamp::formatDate($comment['written'])?></td>
													<td class="site"><a href="<?=$comment['siteUrl']?>" onclick="window.open(this.href); return false;" title="사이트를 새 창으로 연결합니다."><?=htmlspecialchars($comment['siteTitle'])?></a></td>
													<td class="name">
<?
		if ($isNameFiltered) {
?>
														<a class="block-icon bullet" name="name<?=$currentNumber?>block" href="#void" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', 'name')" title="<?=_t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.')?>"><span><?=_t('[차단됨]')?></span></a>
<?
		} else {
?>
														<a class="unblock-icon bullet" name="name<?=$currentNumber?>block" href="#void" onclick="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', 'name')" title="<?=_t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.')?>"><span><?=_t('[허용됨]')?></span></a>
<?
		}
?>
														<a href="#void" onclick="document.forms[0].name.value='<?=escapeJSInAttribute($comment['name'])?>'; document.forms[0].submit();" title="<?=_t('이 이름으로 등록된 댓글 목록을 보여줍니다.')?>"><?=htmlspecialchars($comment['name'])?></a>
													</td>
													<td class="content">
<?
		if ($comment['parent']) {
?>
														<span class="reply-icon bullet" title="댓글에 달린 댓글입니다."><span><?=_t('[댓글의 댓글]')?></span></span>
<?
			if ($lastVisitNotifiedPage > time() - 86400) {
?>
														<span class="new-icon bullet" title="새로 등록된 댓글입니다."><span>[<?=_t('새 댓글')?>]</span></span>
<?
			}
		} else {
			echo '<a class="entryURL" href="'.$comment['entryUrl'].'" onclick="window.open(this.href); return false;" title="'._t('댓글이 작성된 포스트로 직접 이동합니다.').'">';
			//echo '<strong>';
			echo $comment['entryTitle'];
			
			if ($comment['entryTitle'] != '' && $comment['parent'] != '') {
				echo '<span class="divider"> | </span>';
			}
			
			echo empty($comment['parent']) ? '' : "<a href=\"" . $comment['parentUrl'] . "\" onclick=\"window.open(this.href); return false;\">" . $comment['parentName'] . _t('님의 댓글에 대한 댓글') . "</a>";
			//echo "</strong>";
			echo "</a>";
			echo !empty($comment['title']) || !empty($comment['parent']) ? '<br />' : '';
		}
?>
														<a class="commentURL" href="<?=$comment['url']?>" onclick="window.open(this.href); return false;" title="<?=_t('댓글이 작성된 위치로 직접 이동합니다.')?>"><?=htmlspecialchars($comment['comment'])?></a>
													</td>
													<td class="delete">
														<a class="delete-button button" href="#void" onclick="deleteComment(<?=$comment['id']?>)" title="<?=_t('이 댓글을 삭제합니다.')?>"><span><?=_t('삭제')?></span></a>
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
											<div id="delete-section" class="section">
												<span class="label"><?=_t('선택한 알림을')?></span>
												<a class="delete-button button" href="#void" onclick="deleteComments();"><span><?=_t('삭제')?></span></a>
												
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
												
												<div class="clear"></div>
											</div>
											
											<hr class="hidden" />
											
											<div id="search-section" class="section">
												<!--label for="search"><span><?=_t('이름')?>, <?=_t('홈페이지 이름')?>, <?=_t('내용')?></span></label><span class="divider"> |</span-->
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