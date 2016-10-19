<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];

if (isset($_GET['category'])) $_POST['category'] = $_GET['category'];
if (isset($_GET['name'])) $_POST['name'] = $_GET['name'];
if (isset($_GET['ip'])) $_POST['ip'] = $_GET['ip'];
if (isset($_GET['site'])) $_POST['site'] = $_GET['site'];
if (isset($_GET['url'])) $_POST['url'] = $_GET['url'];
if (isset($_GET['withSearch'])) $_POST['withSearch'] = $_GET['withSearch'];
if (isset($_GET['search'])) $_POST['search'] = $_GET['search'];
if (isset($_GET['trashType'])) $_POST['trashType'] = $_GET['trashType'];

$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	),
	'POST' => array(
		'category' => array('int', 'default' => 0),
		'site' => array('string', 'default' => ''),
		'url' => array('url', 'default' => ''),
		'ip' => array('ip', 'default' => ''),
		'withSearch' => array(array('on'), 'mandatory' => false),
		'search' => array('string', 'default' => ''),
		'perPage' => array('int', 10, 30, 'mandatory' => false)
	)
);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.remoteresponse");
importlib("model.blog.trash");

$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$site = empty($_POST['site']) ? '' : $_POST['site'];
$url = empty($_POST['url']) ? '' : $_POST['url'];
$ip = empty($_POST['ip']) ? '' : $_POST['ip'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = Setting::getBlogSettingGlobal('rowsPerPage', 10);
if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
	Setting::setBlogSettingGlobal('rowsPerPage', $_POST['perPage']);
}
$tabsClass = array();
$tabsClass['postfix'] = null;
$tabsClass['postfix'] .= isset($_POST['category']) ? '&amp;category='.$_POST['category'] : '';
$tabsClass['postfix'] .= isset($_POST['site']) ? '&amp;site='.$_POST['site'] : '';
$tabsClass['postfix'] .= isset($_POST['ip']) ? '&amp;ip='.$_POST['ip'] : '';
$tabsClass['postfix'] .= isset($_POST['search']) ? '&amp;search='.$_POST['search'] : '';
$tabsClass['trash'] = true;

list($trackbacks, $paging) = getTrashTrackbackWithPagingForOwner($blogid, $categoryId, $site, $url, $ip, $search, $suri['page'], $perPage);
require ROOT . '/interface/common/owner/header.php';

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
													icon.setAttribute('title', "<?php echo _t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>");
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?php echo _t('[허용됨]');?><\/span>';
													icon.setAttribute('title', "<?php echo _t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>");
												}
											}
										}
										request.send();
									} catch(e) {
										alert(e.message);
									}
								}

								function deleteTrackback(id) {
									if (!confirm("<?php echo _t('선택된 걸린글을 지웁니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/delete/" + id);
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('걸린글을 지우지 못했습니다.');?>");
									}
									request.send();
								}

								function deleteTrackbackAll() {
									if (!confirm("<?php echo _t('휴지통 내의 모든 걸린글을 삭제합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/emptyTrash/?type=2&ajaxcall");
									request.onSuccess = function() {
										window.location.reload();
									}
									request.onError = function () {
										alert("<?php echo _t('비우기에 실패하였습니다.');?>");
									}
									request.send();
								}

								function deleteTrackbacks() {
									try {
										if (!confirm("<?php echo _t('선택된 걸린글을 삭제합니다. 계속 하시겠습니까?');?>"))
											return false;
										var oElement;
										var targets = '';
										for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
											oElement = document.getElementById('list-form').elements[i];
											if ((oElement.name == "entry") && oElement.checked) {
												targets+=oElement.value+'~*_)';
											}
										}
										var request = new HTTPRequest("POST", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/delete/");
										request.onSuccess = function() {
											document.getElementById('list-form').submit();
										}
										request.onError = function () {
											alert("<?php echo _t('걸린글을 지우지 못했습니다.');?>");
										}
										request.send("targets=" + targets);
									} catch(e) {
										alert(e.message);
									}
								}

								function revertTrackback(id) {
									if (!confirm("<?php echo _t('선택된 걸린글을 복원합니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/revert/" + id);
									request.onSuccess = function() {
										document.getElementById('list-form').submit();
									}
									request.onError = function () {
										alert("<?php echo _t('걸린글을 복원하지 못했습니다.');?>");
									}
									request.send();
								}

								function revertTrackbacks() {
									try {
										if (!confirm("<?php echo _t('선택된 걸린글을 복원합니다. 계속 하시겠습니까?');?>"))
											return false;
										var oElement;
										var targets = '';
										for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
											oElement = document.getElementById('list-form').elements[i];
											if ((oElement.name == "entry") && oElement.checked) {
												targets+=oElement.value+'~*_)';
											}
										}
										var request = new HTTPRequest("POST", "<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/revert/");
										request.onSuccess = function() {
											document.getElementById('list-form').submit();
										}
										request.onError = function () {
											alert("<?php echo _t('걸린글을 지우지 못했습니다.');?>");
										}
										request.send("targets=" + targets);
									} catch(e) {
										alert(e.message);
									}
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

									document.getElementById('track-radio-trackback').appendChild(trashSelect);
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
								<span class="main-text"><?php echo _t('삭제 대기중인 걸린글입니다');?></span>
<?php
if (strlen($site) > 0 || strlen($ip) > 0) {
	if (strlen($site) > 0) {
?>
								<span class="filter-condition"><?php echo htmlspecialchars($site);?></span>
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
								<p class="explain"><?php echo _t('휴지통에 버려진 걸린글은 15일이 지나면 자동으로 지워집니다. 광고 걸린글의 차단 및 분석을 위하여 휴지통의 데이터를 사용하는 플러그인이 있을 수 있으므로 수동으로 지우지 않는 것을 권장합니다.');?></p>
							</div>

							<form id="trash-form" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash">
								<fieldset class="section">
									<legend><?php echo _t('삭제된 파일 보기 설정');?></legend>

									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />

									<dl id="trash-type-line" class="line">
										<dt><?php echo _t('종류');?></dt>
										<dd>
											<div id="track-radio-comment">
												<input type="radio" class="radio" id="track-type-comment" name="trashType" value="comment" onclick="document.getElementById('trash-form').submit()" /><label for="track-type-comment"><?php echo _t('댓글 및 방명록');?></label>
											</div>
											<div id="track-radio-trackback">
												<input type="radio" class="radio" id="track-type-trackback" name="trashType" value="trackback" onclick="document.getElementById('trash-form').submit()" checked="checked" /><label for="track-type-trackback"><?php echo _t('글걸기');?></label>
											</div>
										</dd>
									</dl>

									<input type="submit" id="category-move-button" value="<?php echo _t('이동');?>" />
								</fieldset>
							</form>

							<form id="list-form" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback">
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection">
												<input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" />
												<label for="allChecked"></label>
											</th>
											<th class="date"><span class="text"><?php echo _t('등록일자');?></span></th>
											<th class="site"><span class="text"><?php echo _t('사이트명');?></span></th>
											<th class="category"><span class="text"><?php echo _t('분류');?></span></th>
											<th class="title"><span class="text"><?php echo _t('제목');?></span></th>
											<th class="ip"><acronym title="Internet Protocol">ip</acronym></th>
											<th class="delete"><span class="text"><?php echo _t('복원');?></span></th>
											<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
										</tr>
									</thead>
<?php
echo "									<tbody>";
if (sizeof($trackbacks) == 0) {
    ?>
    <tr class="empty-list">
        <td colspan="8"><?php echo _t('걸린글이 없습니다');?></td>
    </tr>
<?php
} else {
	$siteNumber = array();
	for ($i=0; $i<sizeof($trackbacks); $i++) {
		$trackback = $trackbacks[$i];

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
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="selection">
												<input id="trackbackCheckId<?php echo $trackback['id'];?>" type="checkbox" class="checkbox" name="entry" value="<?php echo $trackback['id'];?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" />
												<label for="trackbackCheckId<?php echo $trackback['id'];?>"></label>
											</td>
											<td class="date"><?php echo Timestamp::formatDate($trackback['written']);?></td>
											<td class="site">
<?php
	if ($isFilterURL) {
?>
												<a id="urlFilter<?php echo $currentSite;?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode($filteredURL);?>&amp;mode=url&amp;command=unblock" onclick="changeState(this,'<?php echo $filteredURL;?>','url'); return false;" title="<?php echo _t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
	} else {
?>
												<a id="urlFilter<?php echo $currentSite;?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode($filteredURL);?>&amp;mode=url&amp;command=block" onclick="changeState(this,'<?php echo $filteredURL;?>','url'); return false;" title="<?php echo _t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
	}
?>
												<a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback?url=<?php echo urlencode(escapeJSInAttribute($trackback['url']));?>" title="<?php echo _t('이 사이트에서 건 글 목록을 보여줍니다.');?>"><?php echo htmlspecialchars($trackback['site']);?></a>
											</td>
											<td class="category">
<?php
	if (!empty($trackback['categoryName'])) {
?>
												<span class="categorized"><?php echo htmlspecialchars($trackback['categoryName']);?></span>
<?php
	} else {
?>
												<span class="uncategorized"><?php echo htmlspecialchars($trackback['categoryName']);?></span>
<?php
	}
?>
											</td>
											<td class="title">
												<a href="<?php echo $trackback['url'];?>" onclick="window.open(this.href); return false;" title="<?php echo _t('글을 건 글을 보여줍니다.');?>"><?php echo htmlspecialchars($trackback['subject']);?></a>
											</td>
											<td class="ip">
<?php
	if ($isIpFiltered) {
?>
												<a id="ipFilter<?php echo urlencode($trackback['ip']);?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode($trackback['ip']);?>&amp;mode=ip&amp;command=unblock" onclick="changeState(this,'<?php echo urlencode($trackback['ip']);?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
	} else {
?>
												<a id="ipFilter<?php echo urlencode($trackback['ip']);?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/filter/change/?value=<?php echo urlencode($trackback['ip']);?>&amp;mode=ip&amp;command=block" onclick="changeState(this,'<?php echo urlencode($trackback['ip']);?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
	}
?>
												<a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback?ip=<?php echo urlencode(escapeJSInAttribute($trackback['ip']));?>" title="<?php echo _t('이 IP로 등록된 걸린글 목록을 보여줍니다.');?>"><span class="text"><?php echo $trackback['ip'];?></span></a>
											</td>
											<td class="revert">
												<a class="revert-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/revert/<?php echo $trackback['id'];?>" onclick="revertTrackback(<?php echo $trackback['id'];?>); return false;" title="<?php echo _t('이 걸린글을 복원합니다.');?>"><span class="text"><?php echo _t('복원');?></span></a>
											</td>
											<td class="delete">
												<a class="delete-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback/delete/<?php echo $trackback['id'];?>" onclick="deleteTrackback(<?php echo $trackback['id'];?>); return false;" title="<?php echo _t('이 걸린글을 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a>
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
									<input type="hidden" name="site" value="" />
									<input type="hidden" name="ip" value="" />

									<div id="delete-section" class="section">
										<span class="label"><?php echo _t('선택한 걸린글을');?></span>
										<input type="submit" class="delete-button input-button" value="<?php echo _t('삭제');?>" onclick="deleteTrackbacks(); return false;" />
										<input type="submit" class="revert-button input-button" value="<?php echo _t('복구');?>" onclick="revertTrackbacks(); return false;" />
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

							<form id="search-form" class="data-subbox" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/trackback">
								<h2><?php echo _t('검색');?></h2>

								<div class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('사이트명');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();" />
								</div>
							</form>

							<div class="button-box">
								<a class="all-delete-button button" href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash/emptyTrash/?type=2" onclick="deleteTrackbackAll(); return false;" title="<?php echo _t('휴지통의 걸린글을 한 번에 삭제합니다.');?>"><span class="text"><?php echo _t('휴지통 비우기');?></span></a>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
