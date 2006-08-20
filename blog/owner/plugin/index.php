<?php
if (defined('__TATTERTOOLS_CENTER__')) {
	define('ROOT', '../../../..');
} else if (defined('__TATTERTOOLS_SIDEBAR__')) {
	define('ROOT', '../../../..');	
} else {
	define('ROOT', '../../..');
}
require ROOT . '/lib/includeForOwner.php';
if (defined('__TATTERTOOLS_CENTER__')) {
	require ROOT . '/lib/piece/owner/headerA.php';
	require ROOT . '/lib/piece/owner/contentMenuA1.php';
	$scopeType = 'dashboard';
	$_POST['scopeType'] = $scopeType;
} else if (defined('__TATTERTOOLS_SIDEBAR__')) {
	require ROOT . '/lib/piece/owner/header3.php';
	require ROOT . '/lib/piece/owner/contentMenu33.php';
	$scopeType = 'sidebar';
	$_POST['scopeType'] = $scopeType;
} else {
	require ROOT . '/lib/piece/owner/headerB.php';
	require ROOT . '/lib/piece/owner/contentMenuB0.php';
}

if (empty($_POST['sortType'])) {
	$sortType = getUserSetting("pluginListSortType","ascend");
	$_POST['sortType'] = $sortType;
}
setUserSetting("pluginListSortType",$_POST['sortType']);

if (empty($_POST['scopeType'])) {
	$scopeType = getUserSetting("pluginListScopeType","all");
	$_POST['scopeType'] = $scopeType;
}
if (!defined('__TATTERTOOLS_CENTER__')) {
	if (!defined('__TATTERTOOLS_SIDEBAR__')) {
		setUserSetting("pluginListScopeType",$_POST['scopeType']);
	}
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
								function togglePlugin(plugin, num, width, height) {
									tempStr = document.getElementById("plugin" + num + "Link").innerHTML;
									
									if (!tempStr.match('<?php echo _t('사용중');?>')) {
										var request = new HTTPRequest("<?php echo $blogURL;?>/owner/plugin/activate?name=" + plugin);
										request.onSuccess = function() {												
											document.getElementById("plugin" + num + "Link").className = 'active-class';
											
											document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?php echo _t('사용중');?></span>';
											document.getElementById("plugin" + num + "Link").setAttribute('title', '<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>');
											
											objTR = getParentByTagName("TR", document.getElementById("plugin" + num + "Link"));
											objTR.className = objTR.className.replace('inactive', 'active');
											
											if (objTR.cells[5].innerHTML.match('<?php echo _t('설정');?>')) {
												objTR.cells[5].innerHTML = '<a href="#void" id="config_' + num +'" class="config-enabled-icon bullet" onclick="getCurrentSetting(\'' + plugin + '\',\'Y\',\''+width+'\',\''+height+'\')"><?php echo _t('설정');?></a>';
											}
											tempStr = document.getElementById("plugin" + num + "Scope").innerHTML;
											if (tempStr.match('<?php echo _t('관리자');?>'))
											{
												changeList();
											}
										}
										request.onError = function() {
											alert("<?php echo _t('플러그인을 활성화하는데 실패했습니다.');?>");
										}
										request.send();
									} else {
										var request = new HTTPRequest("<?php echo $blogURL;?>/owner/plugin/deactivate?name=" + plugin);
										request.onSuccess = function() {
											document.getElementById("plugin" + num + "Link").className = 'inactive-class';
											
											document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?php echo _t('미사용');?></span>';
											document.getElementById("plugin" + num + "Link").setAttribute('title', '<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>');
											
											objTR = getParentByTagName("TR", document.getElementById("plugin" + num + "Link"));
											objTR.className = objTR.className.replace('active', 'inactive');
											
											if (objTR.cells[5].innerHTML.match('<?php echo _t('설정');?>')) {
												objTR.cells[5].innerHTML = '<span class="config-disabled-icon bullet"><?php echo _t('설정');?></span>';
											}
											tempStr = document.getElementById("plugin" + num + "Scope").innerHTML;
											if (tempStr.match('<?php echo _t('관리자');?>'))
											{
												changeList();
											}
										}
										request.onError = function() {
											alert("<?php echo _t('플러그인을 비활성화하는데 실패했습니다.');?>");
										}
										request.send();
									}
								}
								
								function changeList() {
									document.getElementById("<?php
if (defined('__TATTERTOOLS_CENTER__'))
	echo 'part-center-plugins';
else if (defined('__TATTERTOOLS_SIDEBAR__'))
	echo 'part-sidebar-plugins';
else
	echo 'part-plugin-list';
									?>").submit();
								}
								
								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									removeItselfById('submit-button-box');
								}
								
								var currentSetting='';
								function getCurrentSetting( plugin, setYN, width, height){
									if( "N" == setYN ) return ;
									if( '' != currentSetting ) currentSetting.close();
									window.open('<?php echo $blogURL;?>/owner/plugin/currentSetting/?Name='+plugin,'CurrentSetting', 'width='+width+', height='+height+', scrollbars=1, status=0, resizable=1');	
									return;
								}								
							//]]>
						</script>
						
						<form id="<?php
if (defined('__TATTERTOOLS_CENTER__'))
	echo 'part-center-plugins';
else if (defined('__TATTERTOOLS_SIDEBAR__'))
	echo 'part-sidebar-plugins';
else
	echo 'part-plugin-list';
						?>" class="part" method="post" action="<?php
if (defined('__TATTERTOOLS_CENTER__'))
	echo $blogURL."/owner/center/setting";
else if (defined('__TATTERTOOLS_SIDEBAR__'))
	echo $blogURL."/owner/skin/sidebar";
else
	echo $blogURL."/owner/plugin";
?>">
							<h2 class="caption"><span class="main-text"><?php
if (defined('__TATTERTOOLS_CENTER__'))
	echo _t('설치된 자투리 플러그인입니다');
else if (defined('__TATTERTOOLS_SIDEBAR__'))
	echo _t('설치된 사이드바 플러그인입니다');
else 
	echo _t('설치된 플러그인입니다');
?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php
if (defined('__TATTERTOOLS_CENTER__'))
	echo _t('자투리 플러그인은 조각보에 기능을 추가하는 역할을 합니다. 이 곳에서 자투리들의 사용 여부를 결정할 수 있습니다.');
else if (defined('__TATTERTOOLS_SIDEBAR__'))
	echo _t('사이드바 플러그인은 스킨의 사이드바 기능을 확장합니다. 이 곳에서 설치된 플러그인의 사용 여부를 결정할 수 있습니다.');
else
	echo _t('플러그인은 태터툴즈의 기능을 확장해 줍니다. 이 곳에서 설치된 플러그인의 사용 여부를 결정할 수 있습니다.');
?></p>
							</div>
							
							<fieldset id="plugin-display-box">
								<legend><?php echo _t('표시될 플러그인 설정');?></legend>

<?php
if (!defined('__TATTERTOOLS_CENTER__')) {
	if (!defined('__TATTERTOOLS_SIDEBAR__')) {
?>
								<dl id="scope-line" class="line">
									<dt><?php echo _t('종류');?></dt>
									<dd>
										<input type="radio" class="radio" id="global-scope" name="scopeType" value="all" onclick="changeList()"<?php echo $_POST['scopeType'] == "all" ? ' checked="checked"' : '';?> /> <label for="global-scope"><?php echo _t('전체');?></label>
										<input type="radio" class="radio" id="common-scope" name="scopeType" value="global" onclick="changeList()"<?php echo $_POST['scopeType'] == "global" ? ' checked="checked"' : '';?> /> <label for="common-scope"><?php echo _t('일반');?></label>
										<input type="radio" class="radio" id="blog-scope" name="scopeType" value="blog" onclick="changeList()"<?php echo $_POST['scopeType'] == "blog" ? ' checked="checked"' : '';?> /> <label for="blog-scope"><?php echo _t('블로그');?></label>
										<input type="radio" class="radio" id="admin-scope" name="scopeType" value="admin" onclick="changeList()"<?php echo $_POST['scopeType'] == "admin" ? ' checked="checked"' : '';?> /> <label for="admin-scope"><?php echo _t('관리자');?></label>
										<input type="radio" class="radio" id="none-scope" name="scopeType" value="none" onclick="changeList()"<?php echo $_POST['scopeType'] == "none" ? ' checked="checked"' : '';?> /> <label for="none-scope"><?php echo _t('미지정');?></label>
									</dd>
								</dl>
<?php
	}
}
?>
								<dl id="sorting-line" class="line">
									<dt><?php echo _t('정렬');?></dt>
									<dd>
										<input type="radio" class="radio" id="ascend-sorting" name="sortType" value="ascend" onclick="changeList()"<?php echo $_POST['sortType'] == "ascend" ? ' checked="checked"' : '';?> /> <label for="ascend-sorting"><?php echo _t('오름차순');?></label>
										<input type="radio" class="radio" id="descend-sorting" name="sortType" value="descend" onclick="changeList()"<?php echo $_POST['sortType'] == "descend" ? ' checked="checked"' : '';?> /> <label for="descend-sorting"><?php echo _t('내림차순');?></label>
									</dd>
								</dl>
								<dl id="activate-status-line" class="line">
									<dt><?php echo _t('상태');?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="activated-plugin" name="listedPluginStatus[]" value="activated" onclick="changeList()"<?php echo in_array("activated", $_POST['listedPluginStatus']) ? ' checked="checked"' : '';?> /> <label for="activated-plugin"><?php echo _t('사용중인 플러그인');?></label>
										<input type="checkbox" class="checkbox" id="deactivated-plugin" name="listedPluginStatus[]" value="deactivated" onclick="changeList()"<?php echo in_array("deactivated", $_POST['listedPluginStatus']) ? ' checked="checked"' : '';?> /> <label for="deactivated-plugin"><?php echo _t('사용하지 않는 플러그인');?></label>
									</dd>
								</dl>
								
								<div id="submit-button-box" class="button-box">
									<input type="submit" value="<?php echo _t('플러그인 목록 갱신');?>" />
								</div>
							</fieldset>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="title"><span class="text"><?php echo _t('제목');?></span></th>
										<th class="version"><span class="text"><?php echo _t('버전');?></span></th>
										<th class="scope"><span class="text"><?php echo _t('종류');?></span></th>
										<th class="explain"><span class="text"><?php echo _t('설명');?></span></th>
										<th class="maker"><span class="text"><?php echo _t('만든이');?></span></th>
										<th class="config"><span class="text"><?php echo _t('설정');?></span></th>
										<th class="status"><span class="text"><?php echo _t('상태');?></span></th>
									</tr>
								</thead>
								<tbody>
<?php
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
						"scope" => htmlspecialchars($xmls->getValue('/plugin/scope[lang()]')),
						"config" => $xmls->doesExist('/plugin/binding/config'),
						"width" => $xmls->getAttribute('/plugin/binding/config/window', 'width'),
						"height" => $xmls->getAttribute('/plugin/binding/config/window', 'height')
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
$rowCount = 0;
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

	if (empty($scope)) $scope = 'none';

	if ($_POST['scopeType'] != 'all')
		if ($scope != $_POST['scopeType'])
			continue;
	if (!defined('__TATTERTOOLS_CENTER__')) {
		if ($scope == 'dashboard') {
			continue;
		}
	}
	if (!defined('__TATTERTOOLS_SIDEBAR__')) {
		if ($scope == 'sidebar') {
			continue;
		}
	}

	if ($active == true && !in_array("activated", $_POST['listedPluginStatus']))
		continue;
	else if ($active == false && !in_array("deactivated", $_POST['listedPluginStatus']))
		continue;
	
	$className = ($rowCount % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($plugins) - 1) ? ' last-line' : '';
	$className .= $active ? ' active-class' : ' inactive-class';
?>
									<tr class="<?php echo $className;?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="title"><?php echo ($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title);?></td>
										<td class="version"><?php echo $version;?></td>
										<td id="plugin<?php echo $i;?>Scope" class="scope">
<?php
	switch($scope) {
		case 'global': echo str_repeat("\t", 11)._t('일반');break;
		case 'blog': echo str_repeat("\t", 11)._t('블로그');break;
		case 'admin': echo str_repeat("\t", 11)._t('관리자');break;
		case 'sidebar': echo str_repeat("\t", 11)._t('사이드바');break;
		case 'dashboard': echo str_repeat("\t", 11)._t('자투리');break;
		default : echo str_repeat("\t", 11)._t('미지정');break;
	}
	echo CRLF;
?>
										</td>
										<td class="explain"><?php echo $description;?></td>
										<td class="maker"><?php echo ($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author);?></td>
										<td class="config">
<?php
	if ($config=='Y') {
		if ($active) {
?>
											<a href="#void" id="config_<?php echo $i;?>" class="config-enabled-icon bullet" onclick="getCurrentSetting('<?php echo $pluginDir;?>','<?php echo $config;?>','<?php echo $width;?>','<?php echo $height;?>')"><?php echo _t('설정');?></a>
<?php
		} else {
?>
											<span class="config-disabled-icon bullet"><?php echo _t('설정');?></span>
<?php
		}
	} else {
?>
											<span class="config-none-icon bullet"><?php echo _t('없음');?></span>
<?php
	}
?>
										</td>
										<td class="status">
<?php
	if ($active) {
?>
											<a id="plugin<?php echo $i;?>Link" class="active-class" href="#void" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>')" title="<?php echo _t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.');?>"><span class="text"><?php echo _t('사용중');?></span></a>
<?php
	} else {
?>
											<a id="plugin<?php echo $i;?>Link" class="inactive-class" href="#void" onclick="togglePlugin('<?php echo $pluginDir;?>',<?php echo $i;?>,'<?php echo $width;?>','<?php echo $height;?>')" title="<?php echo _t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.');?>"><span class="text"><?php echo _t('미사용');?></span></a>
<?php
	}
?>
										</td>
									</tr>
<?php
	$rowCount++;
}
?>
								</tbody>
							</table>
						</form>
						
						<div id="part-plugin-more" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('플러그인을 구하려면');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('추가 플러그인은 <a href="http://www.tattertools.com/plugin" onclick="window.open(this.href); return false;" title="태터툴즈 홈페이지에 개설되어 있는 플러그인 업로드 게시판으로 연결합니다.">태터툴즈 홈페이지의 플러그인 게시판</a>에서 구하실 수 있습니다. 일반적으로 플러그인 파일을 태터툴즈의 plugin 디렉토리로 업로드하면 설치가 완료됩니다. 업로드가 완료된 플러그인은 이 메뉴에서 \'사용중\'으로 전환하여 사용을 시작합니다. 추천 플러그인에 대한 정보는 <a href="http://plugin.tattertools.com" onclick="window.open(this.href); return false;">TnF의 플러그인 리뷰</a>를 참고하십시오.');?></p>
							</div>
						</div>	
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
