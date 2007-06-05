<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../..');

require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
if (defined('__TEXTCUBE_CENTER__')) {
	$scopeType = 'dashboard';
	$_POST['scopeType'] = $scopeType;
}

if (empty($_POST['sortType'])) {
	$sortType = getUserSetting("pluginListSortType","ascend");
	$_POST['sortType'] = $sortType;
}
setUserSetting("pluginListSortType",$_POST['sortType']);

if (empty($_POST['scopeType'])) {
	$scopeType = getUserSetting("pluginListScopeType", "all");
	$_POST['scopeType'] = $scopeType;
}
if (!defined('__TEXTCUBE_CENTER__')) {
	setUserSetting("pluginListScopeType",$_POST['scopeType']);
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
							"title" => $xmls->getValue('/plugin/title[lang()]'),
							"version" => $xmls->getValue('/plugin/version[lang()]'),
							"description" => $xmls->getValue('/plugin/description[lang()]'),
							"authorLink" => $xmls->getAttribute('/plugin/author[lang()]', 'link'),
							"author" => $xmls->getValue('/plugin/author[lang()]'),
							"scope" => array(),
							"config" => $xmls->doesExist('/plugin/binding/config'),
							"width" => $xmls->getAttribute('/plugin/binding/config/window', 'width'),
							"height" => $xmls->getAttribute('/plugin/binding/config/window', 'height')
							);
		if ($xmls->doesExist('/plugin/binding/adminMenu'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'admin');
		if ($xmls->doesExist('/plugin/binding/tag'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'blog');
		if ($xmls->doesExist('/plugin/binding/center'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'dashboard');
		if ($xmls->doesExist('/plugin/binding/listener'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'global');
		if ($xmls->doesExist('/plugin/binding/sidebar'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'sidebar');
		if ($xmls->doesExist('/plugin/binding/editor'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'editor');
		if ($xmls->doesExist('/plugin/binding/formatter'))
			array_push($pluginAttrs[$pluginDir]['scope'], 'formatter');

		$plugins[$pluginDir] = $pluginAttrs[$pluginDir]['title'];
	}
}

if ($_POST['sortType'] == "ascend") {
	asort($plugins);
} else {
	arsort($plugins);
}

$arrayKeys = array_keys($plugins);
?>
						<script type="text/javascript">
							//<![CDATA[
								var pluginInfo = new Array();
								
<?php
for ($i=0; $i<count($arrayKeys); $i++) {
	$pluginDir = $arrayKeys[$i];
	
	$width = $pluginAttrs[$pluginDir]['width']?$pluginAttrs[$pluginDir]['width']:500;
	$height = $pluginAttrs[$pluginDir]['height']?$pluginAttrs[$pluginDir]['height']:400;
?>
								pluginInfo['<?php echo $pluginDir;?>'] = new Array();
								pluginInfo['<?php echo $pluginDir;?>']['width'] = <?php echo $width;?>;
								pluginInfo['<?php echo $pluginDir;?>']['height'] = <?php echo $height;?>;
<?php
}
?>
								
								function togglePlugin(plugin, num, width, height, obj, force) {
									var currentIcon = document.getElementById('pluginIcon'+num);
									var currentSettingButton = document.getElementById('pluginSettingButton'+num);
									
									if (force == 'activate') {
										command = true;
									} else if (force == 'deactivate') {
										command = false;
									} else if (document.getElementById('pluginStatus'+num).value == 0) {
										command = true;
									} else {
										command = false;
									}
									
									if (command) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/plugin/activate");
										request.onSuccess = function() {												
											currentIcon.setAttribute('alt', '<?php echo _t('켜짐');?>');
											currentIcon.setAttribute('title', '<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>');
											document.getElementById('pluginStatus'+num).value = 1;
											objLI = getParentByTagName("LI", obj);
											
											if (document.getElementById('activated-plugin').checked == false) {
												objLI.parentNode.removeChild(objLI);
											} else {
												objLI.className = objLI.className.replace('inactive', 'active');
											
												var icon = new Image();
												if (currentIcon.style.backgroundImage == "url(<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/icon_plugin_off.png)") {
													icon.src = '<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/icon_plugin_on.png';
												} else {
													icon.src = '<?php echo $serviceURL;?>/plugins/' + plugin + '/images/icon_plugin_on.png';
												}
												currentIcon.style.backgroundImage = "url('"+icon.src+"')";
											
												if (currentSettingButton.className == 'dimmed') {
													if (STD.isIE) {
														// tempLink.onclick, tempLink.setAttribute('onclick', ...)은 브라우저 호환성에 문제 있음. 따라서 브라우저를 detect하여 처리함.
														tempLink = document.createElement('<A onclick="getCurrentSetting(\''+plugin+'\', \'Y\', '+width+', '+height+', \'setting\'); return false;">');
													} else {
														tempLink = document.createElement('A');
														tempLink.setAttribute('onclick', "getCurrentSetting('"+plugin+"', 'Y', "+width+", "+height+", 'setting'); return false;");
													}
													tempLink.setAttribute('href', '#void');
													tempLink.innerHTML = '<?php echo _t('환경설정');?>';
													
													currentSettingButton.innerHTML = '';
													currentSettingButton.className = 'enabled';
													currentSettingButton.appendChild(tempLink);
												}
											}
										}
										request.onError = function() {
											alert("<?php echo _t('플러그인을 활성화하는데 실패했습니다.');?>");
										}
										request.send("name=" + plugin);
									} else {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/plugin/deactivate");
										request.onSuccess = function() {
											currentIcon.setAttribute('alt', '<?php echo _t('꺼짐');?>');
											currentIcon.setAttribute('title', '<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>');
											document.getElementById('pluginStatus'+num).value = 0;
											objLI = getParentByTagName("LI", obj);
											
											if (document.getElementById('deactivated-plugin').checked == false) {
												objLI.parentNode.removeChild(objLI);
											} else {
												objLI.className = objLI.className.replace('active', 'inactive');
											
												var icon = new Image();
												if (currentIcon.style.backgroundImage == "url(<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/icon_plugin_on.png)") {
													icon.src = '<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/icon_plugin_off.png';
												} else {
													icon.src = '<?php echo $serviceURL;?>/plugins/' + plugin + '/images/icon_plugin_off.png';
												}
												currentIcon.style.backgroundImage = "url('"+icon.src+"')";
											
												if (currentSettingButton.className == 'enabled') {
													currentSettingButton.innerHTML = '<?php echo _t('환경설정');?>';
													currentSettingButton.className = 'dimmed';
												}
											}
										}
										request.onError = function() {
											alert("<?php echo _t('플러그인을 비활성화하는데 실패했습니다.');?>");
										}
										request.send("name=" + plugin);
									}
								}
								
<?php
if (defined('__TEXTCUBE_CENTER__')) {
?>
								function changeList() {
									document.getElementById("part-center-plugins").submit();
								}
<?php
} else {
?>
								function changeList() {
									document.getElementById("part-plugin-list").submit();
								}
<?php
}
?>
								
								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									removeItselfById('submit-button-box');
									
									for (var i = 0; document.getElementById('part-plugin-list').elements[i]; i++) {
										oElement = document.getElementById('part-plugin-list').elements[i];
										if ((oElement.name == 'entry'))
											oElement.style.display= 'none';
									}
								}
								
								var currentSetting='';
								function getCurrentSetting( plugin, setYN, width, height, tab){
									//if( "N" == setYN ) return ;
									if( '' != currentSetting ) currentSetting.close();
									window.open('<?php echo $blogURL;?>/owner/plugin/currentSetting/?Name='+plugin+'&Tab='+tab, 'CurrentSetting', 'width='+width+', height='+height+', scrollbars=1, status=0, resizable=1');	
									return;
								}								
							//]]>
						</script>
						
						<form id="<?php
if (defined('__TEXTCUBE_CENTER__'))
	echo 'part-center-plugins';
else
	echo 'part-plugin-list';
						?>" class="part" method="post" action="<?php
if (defined('__TEXTCUBE_CENTER__'))
	echo $blogURL."/owner/center/setting";
else
	echo $blogURL."/owner/plugin";
?>">
							<h2 class="caption"><span class="main-text"><?php
if (defined('__TEXTCUBE_CENTER__'))
	echo _t('설치된 자투리 플러그인입니다');
else 
	echo _t('설치된 플러그인입니다');
?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php
if (defined('__TEXTCUBE_CENTER__'))
	echo _t('자투리는 조각보에 기능을 추가합니다. 이 곳에서 자투리들의 사용 여부를 결정할 수 있습니다.');
else
	echo _t('플러그인은 텍스트큐브의 기능을 확장합니다. 이 곳에서 설치된 플러그인의 사용 여부를 결정할 수 있습니다.');
?></p>
							</div>
							
							<fieldset id="plugin-display-box">
								<legend><?php echo _t('표시될 플러그인 설정');?></legend>

								<dl id="sorting-line" class="line">
									<dt><?php echo _t('정렬');?></dt>
									<dd>
										<input type="radio" class="radio" id="ascend-sorting" name="sortType" value="ascend" onclick="changeList()"<?php echo $_POST['sortType'] == "ascend" ? ' checked="checked"' : '';?> /><label for="ascend-sorting"><?php echo _t('오름차순');?></label>
										<input type="radio" class="radio" id="descend-sorting" name="sortType" value="descend" onclick="changeList()"<?php echo $_POST['sortType'] == "descend" ? ' checked="checked"' : '';?> /><label for="descend-sorting"><?php echo _t('내림차순');?></label>
									</dd>
								</dl>
								
								<dl id="activate-status-line" class="line">
									<dt><?php echo _t('상태');?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="activated-plugin" name="listedPluginStatus[]" value="activated" onclick="changeList()"<?php echo in_array("activated", $_POST['listedPluginStatus']) ? ' checked="checked"' : '';?> /><label for="activated-plugin"><?php echo _t('사용중인 플러그인 / 모듈');?></label>
										<input type="checkbox" class="checkbox" id="deactivated-plugin" name="listedPluginStatus[]" value="deactivated" onclick="changeList()"<?php echo in_array("deactivated", $_POST['listedPluginStatus']) ? ' checked="checked"' : '';?> /><label for="deactivated-plugin"><?php echo _t('사용하지 않는 플러그인 / 모듈');?></label>
									</dd>
								</dl>
								
<?php
if (!defined('__TEXTCUBE_CENTER__')) {
?>
								<dl id="scope-line" class="line">
									<dt><?php echo _t('일반');?></dt>
									<dd id="scope-line-general">
										<ul>
											<li><input type="radio" class="radio" id="global-scope" name="scopeType" value="all" onclick="changeList()"<?php echo $_POST['scopeType'] == "all" ? ' checked="checked"' : '';?> /><label id="global-scope-label" for="global-scope"<?php echo $_POST['scopeType'] == "all" ? ' class="selected"' : '';?>><?php echo _t('전체');?></label></li>
											<li><input type="radio" class="radio" id="none-scope" name="scopeType" value="none" onclick="changeList()"<?php echo $_POST['scopeType'] == "none" ? ' checked="checked"' : '';?> /><label id="none-scope-label" for="none-scope"<?php echo $_POST['scopeType'] == "none" ? ' class="selected"' : '';?>><?php echo _t('분류 없음');?></label></li>
										</ul>
									</dd>
									<dt><?php echo _t('플러그인');?></dt>
									<dd id="scope-line-plugin">
										<ul>
											<li><input type="radio" class="radio" id="common-scope" name="scopeType" value="global" onclick="changeList()"<?php echo $_POST['scopeType'] == "global" ? ' checked="checked"' : '';?> /><label id="common-scope-label" for="common-scope"<?php echo $_POST['scopeType'] == "global" ? ' class="selected"' : '';?>><?php echo _t('일반');?></label></li>
											<li><input type="radio" class="radio" id="blog-scope" name="scopeType" value="blog" onclick="changeList()"<?php echo $_POST['scopeType'] == "blog" ? ' checked="checked"' : '';?> /><label id="blog-scope-label" for="blog-scope"<?php echo $_POST['scopeType'] == "blog" ? ' class="selected"' : '';?>><?php echo _t('블로그 출력');?></label></li>
											<li><input type="radio" class="radio" id="sidebar-scope" name="scopeType" value="sidebar" onclick="changeList()"<?php echo $_POST['scopeType'] == "sidebar" ? ' checked="checked"' : '';?> /><label id="sidebar-scope-label" for="sidebar-scope"<?php echo $_POST['scopeType'] == "sidebar" ? ' class="selected"' : '';?>><?php echo _t('사이드바');?></label></li>
											<li><input type="radio" class="radio" id="admin-scope" name="scopeType" value="admin" onclick="changeList()"<?php echo $_POST['scopeType'] == "admin" ? ' checked="checked"' : '';?> /><label id="admin-scope-label" for="admin-scope"<?php echo $_POST['scopeType'] == "admin" ? ' class="selected"' : '';?>><?php echo _t('관리 패널');?></label></li>
										</ul>
									</dd>
									<dt><?php echo _t('모듈');?></dt>
									<dd id="scope-line-module">
										<ul>
											<li><input type="radio" class="radio" id="editor-scope" name="scopeType" value="editor" onclick="changeList()"<?php echo $_POST['scopeType'] == "editor" ? ' checked="checked"' : '';?> /><label id="editor-scope-label" for="editor-scope"<?php echo $_POST['scopeType'] == "editor" ? ' class="selected"' : '';?>><?php echo _t('에디터');?></label></li>
											<li><input type="radio" class="radio" id="formatter-scope" name="scopeType" value="formatter" onclick="changeList()"<?php echo $_POST['scopeType'] == "formatter" ? ' checked="checked"' : '';?> /><label id="formatter-scope-label" for="formatter-scope"<?php echo $_POST['scopeType'] == "formatter" ? ' class="selected"' : '';?>><?php echo _t('포매터');?></label></li>
										</ul>
									</dd>
								</dl>
<?php
} else {
	if (($_SERVER['REQUEST_METHOD'] == 'POST') && (empty($_POST['useTTdashboard']))) {
		$textcubeDashboard = getUserSetting("textcubeDashboard");
		if (is_null($textcubeDashboard)) {
			setUserSetting("textcubeDashboard", 1);
			$textcubeDashboard = 1;
		} else {
			setUserSetting("textcubeDashboard", 0);
			$textcubeDashboard = 0;
		}
	} else {
		setUserSetting("textcubeDashboard", 1);
		$textcubeDashboard = 1;
	}
?>
								<dl id="independent-notice-line" class="line">
									<dt><?php echo _t('독립패널 설정');?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="useTTdashboard" name="useTTdashboard" value="on" onclick="changeList()"<?php echo $textcubeDashboard == 1 ? " checked" : NULL;?> />
										<label for="useTTdashboard"><?php echo _t('조각보에 텍스트큐브 독립 패널을 표시합니다.');?></label>
									</dd>
								</dl>
<?php
}
?>
								
								<div id="submit-button-box" class="button-box">
									<input type="submit" value="<?php echo _t('플러그인 목록 갱신');?>" />
								</div>
							</fieldset>
							
							<div id="temp-box">
								<ul class="data-inbox">
<?php
for ($i=0; $i<count($arrayKeys); $i++) {
	$pluginDir = $arrayKeys[$i];
	
	$link = $pluginAttrs[$pluginDir]['link'];
	$title = $pluginAttrs[$pluginDir]['title'];
	$version = $pluginAttrs[$pluginDir]['version'];
	$description = $pluginAttrs[$pluginDir]['description'];
	$authorLink = $pluginAttrs[$pluginDir]['authorLink'];
	$author = $pluginAttrs[$pluginDir]['author'];
	$scope = $pluginAttrs[$pluginDir]['scope'];
	$config = $pluginAttrs[$pluginDir]['config']? 'Y':'N';
	$width = $pluginAttrs[$pluginDir]['width']?$pluginAttrs[$pluginDir]['width']:500;
	$height = $pluginAttrs[$pluginDir]['height']?$pluginAttrs[$pluginDir]['height']:400;
	$active = in_array($pluginDir, $activePlugins);
	
	if (count($scope) == 0)
		$scope = array('none');
		
	if ($_POST['scopeType'] != 'all')
		if (!in_array($_POST['scopeType'], $scope))
			continue;
	if (!defined('__TEXTCUBE_CENTER__')) {
		if (in_array('dashboard', $scope) && (count($scope) == 1)) {
			continue;
		}
	}
	
	if ($active == true && !in_array("activated", $_POST['listedPluginStatus']))
		continue;
	else if ($active == false && !in_array("deactivated", $_POST['listedPluginStatus']))
		continue;
	
	$className = $active ? 'active-class' : 'inactive-class';
	$className .= $i == (count($arrayKeys) - 1) ? ' last-item' : NULL;
?>
									<li class="<?php echo $className;?>">
<?php
	if ($active) {
		if (file_exists(ROOT . "/plugins/{$pluginDir}/images/icon_plugin_on.png")) {
?>
										<div class="plugin-box">
											<div id="pluginIcon<?php echo $i;?>" class="plugin-icon" style="background-image: url('<?php echo $serviceURL . "/plugins/{$pluginDir}/images/icon_plugin_on.png";?>');" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>', this, null); return false;">
												<img id="pluginStatusIcon<?php echo $i;?>" src="<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/spacer.gif";?>" width="28" height="29" alt="<?php echo _t('켜짐');?>" title="<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>" />
											</div>
<?php
		} else {
?>
										<div class="plugin-box">
											<div id="pluginIcon<?php echo $i;?>" class="plugin-icon" style="background-image: url('<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/icon_plugin_on.png";?>');" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>', this, null); return false;">
												<img id="pluginStatusIcon<?php echo $i;?>" src="<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/spacer.gif";?>" width="28" height="29" alt="<?php echo _t('켜짐');?>" title="<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>" />
											</div>
<?php
		}
?>
											<input type="hidden" id="pluginStatus<?php echo $i;?>" value="1" />
<?php
	} else {
		if (file_exists(ROOT . "/plugins/{$pluginDir}/images/icon_plugin_off.png")) {
?>
										<div class="plugin-box">
											<div id="pluginIcon<?php echo $i;?>" class="plugin-icon" style="background-image: url('<?php echo $serviceURL . "/plugins/{$pluginDir}/images/icon_plugin_off.png";?>');" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>', this, null); return false;">
												<img id="pluginStatusIcon<?php echo $i;?>" src="<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/spacer.gif";?>" width="28" height="29" alt="<?php echo _t('꺼짐');?>" title="<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>" />
											</div>
<?php
		} else {
?>
										<div class="plugin-box">
											<div id="pluginIcon<?php echo $i;?>" class="plugin-icon" style="background-image: url('<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/icon_plugin_off.png";?>');" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>', this, null); return false;">
												<img id="pluginStatusIcon<?php echo $i;?>" src="<?php echo $serviceURL . $adminSkinSetting['skin'] . "/image/spacer.gif";?>" width="28" height="29" alt="<?php echo _t('꺼짐');?>" title="<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>" />
											</div>
<?php
		}
?>
											<input type="hidden" id="pluginStatus<?php echo $i;?>" value="0" />
<?php
	}
?>
										</div>
										<div class="summary">
											<div class="plugin-title">
<?php
	if ($active) {
?>
												<input type="checkbox" class="input-checkbox" name="entry" value="<?php echo $pluginDir;?>" title="<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>" checked="checked" />
<?php
	} else {
?>
												<input type="checkbox" class="input-checkbox" name="entry" value="<?php echo $pluginDir;?>" title="<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>" />
<?php
	}
?>
												<?php echo ($link ? "<a href=\"" . htmlspecialchars($link) . "\" title=\"".htmlspecialchars($title)." - " . _t('버전') . " {$version}\">" . htmlspecialchars(UTF8::lessenAsEm($title, 20)) . '</a>' : "<span title=\"".htmlspecialchars($title)." - " . _t('버전') . " {$version}\">" . htmlspecialchars(UTF8::lessenAsEm($title, 20)) . '</span>');?>
											</div>
											<div class="plugin-buttons">
												<a href="#void" onclick="getCurrentSetting('<?php echo $pluginDir;?>','<?php echo $config;?>','<?php echo $width;?>','<?php echo $height;?>', 'about'); return false;"><?php echo _t('자세히 보기');?></a> <span class="divider">|</span>
<?php
	if ($config=='Y') {
		if ($active) {
?>
												<span id="pluginSettingButton<?php echo $i;?>" class="enabled"><a href="#void" onclick="getCurrentSetting('<?php echo $pluginDir;?>','<?php echo $config;?>','<?php echo $width;?>','<?php echo $height;?>', 'setting'); return false;"><?php echo _t('환경설정');?></a></span>
<?php
		} else {
?>
												<span id="pluginSettingButton<?php echo $i;?>" class="dimmed"><?php echo _t('환경설정');?></span>
<?php
		}
	} else {
?>
												<span id="pluginSettingButton<?php echo $i;?>" class="disabled"><?php echo _t('환경설정');?></span>
<?php
	}
?>
											</div>
										</div>
									</li>
<?php
}
?>
								</ul>
								
								<div class="clear"></div>
							</div>
						</form>
						
						<hr class="hidden" />
						
						<div id="part-plugin-more" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('플러그인을 구하려면');?></span></h2>
							
<?php
$linkString = '<a href="http://plugin.textcube.com/" onclick="window.open(this.href); return false;" title="' . _t('플러그인 업로드 게시판으로 연결합니다.') . '">' . _t('플러그인 업로드 게시판'). '</a>';
$tempString = _f('텍스트큐브 홈페이지의 %1을 방문하시면 다양한 플러그인을 다운로드 하실 수 있습니다. 일반적으로 플러그인 파일을 텍스트큐브의 plugin 디렉토리로 업로드하면 설치가 완료됩니다. 업로드가 완료된 플러그인은 이 메뉴에서 사용중으로 전환하여 사용을 시작합니다. 추천 플러그인에 대한 정보는 <a href="http://blog.textcube.com/plugin" onclick="window.open(this.href); return false;">TNF의 플러그인 리뷰</a>를 참고하십시오.', $linkString);
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo $tempString;?></p>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
