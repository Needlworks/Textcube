<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];

if (isset($_GET['category'])) $_POST['category'] = $_GET['category'];
if (isset($_GET['name'])) $_POST['name'] = $_GET['name'];
if (isset($_GET['ip'])) $_POST['ip'] = $_GET['ip'];
if (isset($_GET['withSearch'])) $_POST['withSearch'] = $_GET['withSearch'];
if (isset($_GET['search'])) $_POST['search'] = $_GET['search'];
if (isset($_GET['trashType'])) $_POST['trashType'] = $_GET['trashType'];

$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	),
	'POST' => array(
		'category' => array('int', 'default' => 0),
		'name' => array('string', 'default' => ''),
		'ip' => array('ip', 'default' => ''),
		'withSearch' => array(array('on'), 'mandatory' => false),
		'search' => array('string', 'default' => ''),
		'perPage' => array('int', 10, 30, 'mandatory' => false),
		'trashType' => array('string', 'default' => 'comment')
	)
);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.comment");
importlib("model.blog.trash");

$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$name = isset($_GET['name']) && !empty($_GET['name']) ? $_GET['name'] : '';
$name = isset($_POST['name']) && !empty($_POST['name']) ? $_POST['name'] : $name;
$ip = isset($_GET['ip']) && !empty($_GET['ip']) ? $_GET['ip'] : '';
$ip = isset($_POST['ip']) && !empty($_POST['ip']) ? $_POST['ip'] : $ip;
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = Setting::getBlogSettingGlobal('rowsPerPage', 10);
if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
	Setting::setBlogSettingGlobal('rowsPerPage', $_POST['perPage']);
}

$tabsClass = array();
$tabsClass['postfix'] = null;
$tabsClass['postfix'] .= isset($_POST['category']) ? '&amp;category='.$_POST['category'] : '';
$tabsClass['postfix'] .= isset($_POST['name']) ? '&amp;name='.$_POST['name'] : '';
$tabsClass['postfix'] .= isset($_POST['ip']) ? '&amp;ip='.$_POST['ip'] : '';
$tabsClass['postfix'] .= isset($_POST['search']) ? '&amp;search='.$_POST['search'] : '';
$tabsClass['trash'] = true;

list($comments, $paging) = getTrashCommentsWithPagingForOwner($blogid, $categoryId, $name, $ip, $search, $suri['page'], $perPage);
require ROOT . '/interface/common/owner/header.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function deleteComment(id) {
									if (!confirm("<?php echo _t('선택된 댓글 또는 방명록을 삭제합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/delete/" + id);
									request.onSuccess = function () {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('삭제하지 못했습니다.');?>");
									}
									request.send();
								}
								function deleteCommentAll() {
									if (!confirm("<?php echo _t('휴지통 내의 모든 댓글 및 방명록을 삭제합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/emptyTrash/?type=1&ajaxcall");
									request.onSuccess = function () {
										window.location.reload();
									}
									request.onError = function () {
										alert("<?php echo _t('비우기에 실패하였습니다.');?>");
									}
									request.send();
								}

								function deleteComments() {
									if (!confirm("<?php echo _t('선택된 댓글 및 방명록을 삭제합니다. 계속 하시겠습니까?');?>"))
										return false;
									var oElement;
									var targets = '';
									for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
										oElement = document.getElementById('list-form').elements[i];
										if ((oElement.name == "entry") && oElement.checked) {
											targets += oElement.value +'~*_)';
										}
									}
									var request = new HTTPRequest("POST", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/delete/");
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('삭제하지 못했습니다.');?>");
									}
									request.send("targets=" + targets);
								}

								function revertComment(id) {
									if (!confirm("<?php echo _t('선택된 댓글 및 방명록을 복원합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/revert/" + id);
									request.onSuccess = function () {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('복원하지 못했습니다.');?>");
									}
									request.send();
								}

								function revertComments() {
									if (!confirm("<?php echo _t('선택된 댓글 및 방명록을 복원합니다. 계속 하시겠습니까?');?>"))
										return false;
									var oElement;
									var targets = '';
									for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
										oElement = document.getElementById('list-form').elements[i];
										if ((oElement.name == "entry") && oElement.checked) {
											targets += oElement.value +'~*_)';
										}
									}
									var request = new HTTPRequest("POST", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/revert/");
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('복원하지 못했습니다.');?>");
									}
									request.send("targets=" + targets);
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

										var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/" + param);
										var iconList = document.getElementsByTagName("a");
										for (var i = 0; i < iconList.length; i++) {
											icon = iconList[i];
											if(icon.id == null || icon.id.replace(/\-[0-9]+$/, '') != name) {
												continue;
											} else {
												if (command == 'block') {
													icon.className = 'block-icon bullet';
													icon.innerHTML = '<span class="text"><?php echo _t('[차단됨]');?><\/span>';
													if (mode == 'name') {
														icon.setAttribute('title', "<?php echo _t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>");
													} else {
														icon.setAttribute('title', "<?php echo _t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>");
													}
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?php echo _t('[허용됨]');?><\/span>';
													if (mode == 'name') {
														icon.setAttribute('title', "<?php echo _t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.');?>");
													} else {
														icon.setAttribute('title', "<?php echo _t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>");
													}
												}
											}
										}
										request.send();
									} catch(e) {
										alert(e.message);
									}
								}

								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
									document.getElementById('allChecked').disabled = false;
									removeItselfById('category-move-button');

									trashSelect = document.createElement("SELECT");
									trashSelect.id = "category";
									trashSelect.name = "category";
									trashSelect.onchange = function() { document.getElementById('trash-form').page.value=1; document.getElementById('trash-form').submit(); return false; };

									trashOption = document.createElement("OPTION");
									trashOption.innerHTML = "<?php echo _t('전체');?>";
									trashOption.value = "0";
									trashSelect.appendChild(trashOption);
<?php
foreach (getCategories($blogid) as $category) {
?>
									trashOption = document.createElement("OPTION");
									trashOption.innerHTML = "<?php echo htmlspecialchars($category['name']);?>";
									trashOption.value = "<?php echo $category['id'];?>";
<?php
	if ($category['id'] == $categoryId) {
?>

									trashOption.setAttribute("selected", "selected");
<?php
	}
?>
									trashSelect.appendChild(trashOption);
<?php
	foreach ($category['children'] as $child) {
?>
									trashOption = document.createElement("OPTION");
									trashOption.innerHTML = " ― <?php echo htmlspecialchars($child['name']);?>";
									trashOption.value = "<?php echo $child['id'];?>";
<?php
		if ($child['id'] == $categoryId) {
?>
									trashOption.setAttribute("selected", "selected");
<?php
		}
?>
									trashSelect.appendChild(trashOption);
<?php
	}
}
?>

									document.getElementById('track-radio-comment').appendChild(trashSelect);
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

						<div id="part-post-trash" class="part">
							<h2 class="caption">
								<span class="main-text"><?php echo _t('삭제 대기중인 댓글 목록입니다');?></span>
<?php
if (strlen($name) > 0 || strlen($ip) > 0) {
	if (strlen($name) > 0) {
?>
								<span class="filter-condition"><?php echo htmlspecialchars($name);?></span>
<?php
	}

	if (strlen($ip) > 0) {
?>
								<span class="filter-condition"><?php echo htmlspecialchars($ip);?></span>
<?php
	}
}
?>
							</h2>
<?php
require ROOT . '/interface/common/owner/communicationTab.php';
?>

							<div class="main-explain-box">
								<p class="explain"><?php echo _t('휴지통에 버려진 댓글은 15일이 지나면 자동으로 지워집니다. 광고 댓글의 차단 및 분석을 위하여 휴지통의 데이터를 사용하는 플러그인이 있을 수 있으므로 수동으로 지우지 않는 것을 권장합니다.');?></p>
							</div>

							<form id="trash-form" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash">
								<fieldset class="section">
									<legend><?php echo _t('삭제된 파일 보기 설정');?></legend>

									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />

									<dl id="trash-type-line" class="line">
										<dt><?php echo _t('종류');?></dt>
										<dd>
											<div id="track-radio-comment">
												<input type="radio" class="radio" id="track-type-comment" name="trashType" value="comment" onclick="document.getElementById('trash-form').submit()" checked="checked" /><label for="track-type-comment"><?php echo _t('댓글 및 방명록');?></label>
											</div>
											<div id="track-radio-trackback">
												<input type="radio" class="radio" id="track-type-trackback" name="trashType" value="trackback" onclick="document.getElementById('trash-form').submit()" /><label for="track-type-trackback"><?php echo _t('글걸기');?></label>
											</div>
										</dd>
									</dl>
									<input type="submit" id="category-move-button" value="<?php echo _t('이동');?>" />
								</fieldset>
							</form>

							<form id="list-form" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment">
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection">
												<input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" />
												<label for="allChecked"></label>
											</th>
											<th class="date"><span class="text"><?php echo _t('등록일자');?></span></th>
											<th class="name"><span class="text"><?php echo _t('이름');?></span></th>
											<th class="content"><span class="text"><?php echo _t('내용');?></span></th>
											<th class="ip"><acronym title="Internet Protocol">ip</acronym></th>
											<th class="revert"><span class="text"><?php echo _t('복원');?></span></th>
											<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
										</tr>
									</thead>
<?php
echo "									<tbody>";
if (sizeof($comments) == 0) {
    ?>
    <tr class="empty-list">
        <td colspan="7"><?php echo _t('댓글이 없습니다');?></td>
    </tr>
<?php
} else {
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
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="selection">
												<input id="commentCheckId<?php echo $comment['id'];?>" type="checkbox" class="checkbox" name="entry" value="<?php echo $comment['id'];?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" />
												<label for="commentCheckId<?php echo $comment['id'];?>"></label>
											</td>
											<td class="date"><?php echo Timestamp::formatDate($comment['written']);?></td>
											<td class="name">
<?php
	if ($isNameFiltered) {
?>
												<a id="nameFilter<?php echo $currentNumber;?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode(escapeJSInAttribute($comment['name']));?>&amp;mode=name&amp;command=unblock&amp;id=<?php echo $filter->id;?>" onclick="changeState(this,'<?php echo escapeJSInAttribute($comment['name']);?>', '<?php echo $filter->id;?>', 'name'); return false;" title="<?php echo _t('이 이름은 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
	} else {
?>
												<a id="nameFilter<?php echo $currentNumber;?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode(escapeJSInAttribute($comment['name']));?>&amp;mode=name&amp;command=block&amp;id=<?php echo $filter->id;?>" onclick="changeState(this,'<?php echo escapeJSInAttribute($comment['name']);?>', '<?php echo $filter->id;?>', 'name'); return false;" title="<?php echo _t('이 이름은 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
	}
?>
												<a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment?name=<?php echo urlencode(escapeJSInAttribute($comment['name']));?>" title="<?php echo _t('이 이름으로 등록된 댓글 목록을 보여줍니다.');?>"><?php echo htmlspecialchars($comment['name']);?></a>
											</td>
											<td class="content">
<?php
	echo '<a class="entryURL" href="'.$context->getProperty('uri.blog').'/'.$comment['entry'].'#comment'.$comment['id'].'" title="'._t('댓글이 작성된 포스트로 직접 이동합니다.').'">';
	echo '<span class="entry-title">'. htmlspecialchars($comment['title']) .'</span>';

	if ($comment['title'] != '' && $comment['parent'] != '') {
		echo '<span class="divider"> | </span>';
	}
	if($comment['entry'] == 0) {	// Guestbook case
		if(empty($comment['parent'])) {
			echo '<span class="explain">' . _t('방명록') . '</span>';
		} else {
			echo '<span class="explain">' . _f('%1 님의 방명록에 대한 댓글',$comment['parentName']) . '</span>';
		}
	} else {
		echo empty($comment['parent']) ? '' : '<span class="explain">' . _f('%1 님의 댓글에 대한 댓글',$comment['parentName']) . '</span>';
	}
	echo "</a>";
?>
												<?php echo ((!empty($comment['title']) || !empty($comment['parent'])) ? '<br />' : '');?>
												<?php echo htmlspecialchars(Utils_Unicode::lessen($comment['comment'],80));?>
											</td>
											<td class="ip">
<?php
	if ($isIpFiltered) {
?>
												<a id="ipFilter<?php echo $currentIP;?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode(escapeJSInAttribute($comment['ip']));?>&amp;mode=ip&amp;command=unblock&amp;id=<?php echo $filter->id;?>" onclick="changeState(this,'<?php echo escapeJSInAttribute($comment['ip']);?>', '<?php echo $filter->id;?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
	} else {
?>
												<a id="ipFilter<?php echo $currentIP;?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode(escapeJSInAttribute($comment['ip']));?>&amp;mode=ip&amp;command=block&amp;id=<?php echo $filter->id;?>" onclick="changeState(this,'<?php echo escapeJSInAttribute($comment['ip']);?>', '<?php echo $filter->id;?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
	}
?>
												<a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment?ip=<?php echo urlencode(escapeJSInAttribute($comment['ip']));?>" title="<?php echo _t('이 IP로 등록된 댓글 목록을 보여줍니다.');?>"><?php echo $comment['ip'];?></a>
											</td>
											<td class="revert">
												<a class="revert-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/revert/<?php echo $comment['id'];?>" onclick="revertComment(<?php echo $comment['id'];?>); return false;" title="<?php echo _t('이 댓글을 복원합니다.');?>"><span class="text"><?php echo _t('복원');?></span></a>
											</td>
											<td class="delete">
												<a class="delete-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment/delete/<?php echo $comment['id'];?>" onclick="deleteComment(<?php echo $comment['id'];?>); return false;" title="<?php echo _t('이 댓글을 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a>
											</td>
						  				</tr>
<?php
	}
}
echo "									</tbody>";
?>
								</table>

		    					<hr class="hidden" />

								<div class="data-subbox">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
									<input type="hidden" name="name" value="" />
									<input type="hidden" name="ip" value="" />

									<div id="delete-section" class="section">
										<span class="label"><?php echo _t('선택한 댓글을');?></span>
										<input type="submit" class="delete-button input-button" value="<?php echo _t('삭제');?>" onclick="deleteComments(); return false;" />
										<input type="submit" class="revert-button input-button" value="<?php echo _t('복구');?>" onclick="revertComments(); return false;" />
									</div>

									<div id="page-section" class="section">
										<div id="page-navigation">
											<span id="page-list">
<?php
//$paging['url'] = 'document.getElementById('list-form').page.value=';
//$paging['prefix'] = '';
//$paging['postfix'] = '; document.getElementById('list-form').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[##_paging_rep_link_num_##]</a>';
print Paging::getPagingView($paging, $pagingTemplate, $pagingItemTemplate, false);
?>
											</span>
											<span id="total-count"><?php echo _f('총 %1건', empty($paging['total']) ? "0" : $paging['total']);?></span>
										</div>
										<div class="page-count">
											<?php echo Utils_Misc::getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0);?>
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
											<?php echo Utils_Misc::getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1);?>
										</div>
									</div>
								</div>
							</form>

							<hr class="hidden" />

							<form id="search-form" class="data-subbox" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/comment">
								<h2><?php echo _t('검색');?></h2>

								<div class="section">
									<label for="search"><?php echo _t('이름');?>, <?php echo _t('사이트명');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();" />
								</div>
							</form>

							<div class="button-box">
								<a class="all-delete-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/emptyTrash/?type=1" onclick="deleteCommentAll(); return false;" title="<?php echo _t('휴지통의 댓글을 한 번에 삭제합니다.');?>"><span class="text"><?php echo _t('휴지통 비우기');?></span></a>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
