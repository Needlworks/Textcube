<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu53.php';

if (empty($_POST['sortType'])) {
	$sortType = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'pluginListSortType'");
	$_POST['sortType'] = ($sortType == false) ? "ascend" : $sortType;
} else if ($_POST['sortType'] != "ascend" && $_POST['sortType'] != "descend") {
	$_POST['sortType'] = "ascend";
}

if (!DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'pluginListSortType'")) {
	DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'pluginListSortType', '{$_POST['sortType']}')");
} else {
	DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '{$_POST['sortType']}' WHERE `user` = $owner AND `name` = 'pluginListSortType'");
}

if (empty($_POST['listedPluginStatus'])) {
	$listType = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'listedPluginStatus'");
	$_POST['listedPluginStatus'] = ($listType == false) ? array("activated", "deactivated") : explode("|", $listType);
} else if (is_array($_POST['listedPluginStatus'])) {
	sort($_POST['listedPluginStatus']);
	if ($_POST['listedPluginStatus'] != array("activated") && $_POST['listedPluginStatus'] != array("deactivated") && $_POST['listedPluginStatus'] != array("activated", "deactivated")) {
		$_POST['listedPluginStatus'] = array("activated", "deactivated");
	}
} else {
	$_POST['listedPluginStatus'] = array("activated", "deactivated");
}

if (!DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'listedPluginStatus'")) {
	DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'listedPluginStatus', '".implode("|", $_POST['listedPluginStatus'])."')");
} else {
	DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '".implode("|", $_POST['listedPluginStatus'])."' WHERE `user` = $owner AND `name` = 'listedPluginStatus'");
}
?>
						<script type="text/javascript">
							//<![CDATA[
								function togglePlugin(plugin, num) {
									tempStr = document.getElementById("plugin" + num + "Link").innerHTML;
									
									if (!tempStr.match('<?=_t('사용중')?>')) {
										var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/activate?name=" + plugin);
										request.onSuccess = function() {												
											document.getElementById("plugin_" + num).className = 'active-icon bullet';
											
											document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?=_t('사용중')?></span>';
											document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
											document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
											
											objTR = getParentByTagName("TR", document.getElementById("plugin" + num + "Link"));
											objTR.className = objTR.className.replace('inactive', 'active');
										}
										request.onError = function() {
											alert("<?=_t('플러그인을 활성화하는데 실패했습니다.')?>");
										}
										request.send();
									} else {
										var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/deactivate?name=" + plugin);
										request.onSuccess = function() {
											document.getElementById("plugin_" + num).className = 'inactive-icon bullet';
											
											document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?=_t('미사용')?></span>';
											document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
											document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
											
											objTR = getParentByTagName("TR", document.getElementById("plugin" + num + "Link"));
											objTR.className = objTR.className.replace('active', 'inactive');
										}
										request.onError = function() {
											alert("<?=_t('플러그인을 비활성화하는데 실패했습니다.')?>");
										}
										request.send();
									}
								}
								
								function changeList() {
									document.getElementById("part-setting-plugins").submit();
								}
								
								window.addEventListener("load", loadPluignList, false);
								
								function loadPluignList() {
									document.getElementById('submit-button-box').style.display = "none";
								}
							//]]>
						</script>
						
						<form id="part-setting-plugins" class="part" method="post" action="<?=$blogURL?>/owner/setting/plugins">
							<h2 class="caption"><span class="main-text"><?=_t('설치된 플러그인입니다')?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('플러그인은 태터툴즈의 기능을 확장해 줍니다. 설치된 플러그인은 이 메뉴에서 사용여부를 결정합니다.')?></p>
							</div>
							
							<fieldset id="plugin-display-box">
								<legend><?=_t('표시될 플러그인 설정')?></legend>
								
								<dl id="sorting-line" class="line">
									<dt><?=_t('정렬')?></dt>
									<dd>
										<input type="radio" class="radio" id="ascend-sorting" name="sortType" value="ascend" onclick="changeList()"<?php echo $_POST['sortType'] == "ascend" ? ' checked="checked"' : ''?> /> <label for="ascend-sorting"><?=_t('오름차순')?></label>
										<input type="radio" class="radio" id="descend-sorting" name="sortType" value="descend" onclick="changeList()"<?php echo $_POST['sortType'] == "descend" ? ' checked="checked"' : ''?> /> <label for="descend-sorting"><?=_t('내림차순')?></label>
									</dd>
								</dl>
								<dl id="activate-status-line" class="line">
									<dt><?=_t('상태')?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="activated-plugin" name="listedPluginStatus[]" value="activated" onclick="changeList()"<?php echo in_array("activated", $_POST['listedPluginStatus']) ? ' checked="checked"' : ''?> /> <label for="activated-plugin"><?=_t('사용중인 플러그인')?></label>
										<input type="checkbox" class="checkbox" id="deactivated-plugin" name="listedPluginStatus[]" value="deactivated" onclick="changeList()"<?php echo in_array("deactivated", $_POST['listedPluginStatus']) ? ' checked="checked"' : ''?> /> <label for="deactivated-plugin"><?=_t('미사용인 플러그인')?></label>
									</dd>
								</dl>
								
								<div id="submit-button-box" class="button-box">
									<input type="submit" />
								</div>
							</fieldset>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="title"><span class="text"><?=_t('제목')?></span></th>
										<th class="version"><span class="text"><?=_t('버전')?></span></th>
										<th class="scope"><span class="text"><?=_t('종류')?></span></th>
										<th class="explain"><span class="text"><?=_t('설명')?></span></th>
										<th class="maker"><span class="text"><?=_t('만든이')?></span></th>
										<th class="status"><span class="text"><?=_t('상태')?></span></th>
									</tr>
								</thead>
								<tbody>
<?
$plugins = array();
$pluginAttrs = array();
$dir = dir(ROOT . '/plugins/');
while ($plugin = $dir->read()) {
	if (!ereg('^[[:alnum:] _-]+$', $plugin))
		continue;
	if (!is_dir(ROOT . '/plugins/' . $plugin))
		continue;
	if (!file_exists(ROOT . "/plugins/$plugin/index.xml"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml"))) {
		continue;
	} else {
		$pluginDir = trim($plugin);
		$pluginAttrs[$pluginDir] = array(
						"link" => $xmls->getValue('/plugin/link[lang()]'),
						"title" => htmlspecialchars($xmls->getValue('/plugin/title[lang()]')),
						"version" => htmlspecialchars($xmls->getValue('/plugin/version[lang()]')),
						"description" => htmlspecialchars($xmls->getValue('/plugin/description[lang()]')),
						"authorLink" => $xmls->getAttribute('/plugin/author[lang()]', 'link'),
						"author" => htmlspecialchars($xmls->getValue('/plugin/author[lang()]')),
						"scope" => htmlspecialchars($xmls->getValue('/plugin/scope[lang()]'))
						);
		
		$plugins[$pluginDir] = $pluginAttrs[$pluginDir]['title'];
	}
}

if ($_POST['sortType'] == "ascend") {
	asort($plugins);
} else {
	arsort($plugins);
}

$arrayKeys = array_keys($plugins);

for ($i=0; $i<count($arrayKeys); $i++) {
	$pluginDir = $arrayKeys[$i];
	
	$link = $pluginAttrs[$pluginDir]['link'];
	$title = $pluginAttrs[$pluginDir]['title'];
	$version = $pluginAttrs[$pluginDir]['version'];
	$description = $pluginAttrs[$pluginDir]['description'];
	$authorLink = $pluginAttrs[$pluginDir]['authorLink'];
	$author = $pluginAttrs[$pluginDir]['author'];
	$scope = $pluginAttrs[$pluginDir]['scope'];
	$active = in_array($pluginDir, $activePlugins);
	
	if ($active == true && !in_array("activated", $_POST['listedPluginStatus']))
		continue;
	else if ($active == false && !in_array("deactivated", $_POST['listedPluginStatus']))
		continue;
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($plugins) - 1) ? ' last-line' : '';
	$className .= $active ? ' active-class' : ' inactive-class';
?>
									<tr class="<?php echo $className?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="title"><?=($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title)?></td>
										<td class="version"><?=$version?></td>
										<td class="scope"><?
switch($scope) {
	case 'global': echo _t('일반');break;
	case 'blog': echo _t('블로그');break;
	case 'admin': echo _t('관리자');break;
	case 'sidebar': echo _t('사이드바');break;
	case 'center': echo _t('센터');break;
	default : echo _t('미지정');break;
}	
?></td>
										<td class="explain"><?=$description?></td>
										<td class="maker"><?=($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author)?></td>
										<td class="status">
<?
	if ($active) {
?>
											<span id="plugin_<?=$i?>" class="active-icon bullet" onclick="togglePlugin('<?=$pluginDir?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$pluginDir?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span class="text"><?=_t('사용중')?></span></a>
<?
	} else {
?>
											<span id="plugin_<?=$i?>" class="inactive-icon bullet" onclick="togglePlugin('<?=$pluginDir?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$pluginDir?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span class="text"><?=_t('미사용')?></span></a>
<?
	}
?>
										</td>
									</tr>
<?
}
?>
								</tbody>
							</table>
						</form>
						
						<div id="part-setting-more" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('플러그인을 구하려면')?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('추가 플러그인은 <a href="http://www.tattertools.com/plugin" onclick="window.open(this.href); return false;" title="태터툴즈 홈페이지에 개설되어 있는 플러그인 업로드 게시판으로 연결합니다.">태터툴즈 홈의 플러그인 게시판</a>에서 구하실 수 있습니다. 일반적으로 플러그인 파일을 태터툴즈의 plugin 디렉토리로 업로드하면 설치가 완료됩니다. 업로드가 완료된 플러그인은 이 메뉴에서 \'사용중\'으로 전환하여 사용을 시작합니다. 추천 플러그인에 대한 정보는 <a href="http://plugin.tattertools.com" onclick="window.open(this.href); return false;">TnF의 플러그인 리뷰</a>를 참고하십시오.')?></p>
							</div>
						</div>	
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
