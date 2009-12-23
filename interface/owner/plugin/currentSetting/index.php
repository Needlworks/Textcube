<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'Name' => array('string'),
		'Tab' => array('string', 'default' => 'about')
	)
);
require ROOT . '/library/preprocessor.php';

if (false) { // For optimization process
	textTreat();
	textareaTreat();
	selectTreat();
	checkboxTreat();
	radioTreat();
}
$targetURL = $hostURL.preg_replace( '/(currentSetting)$/' , 'receiveConfig' , $folderURL );
$pluginName = $_GET['Name'];
$tabName = $_GET['Tab'];
$active = in_array($pluginName, $activePlugins);
$result =  handleConfig($pluginName);
if( is_null($result) )	Respond::NotFoundPage();

$xmls = new XMLStruct();
if (!$xmls->open(file_get_contents(ROOT . "/plugins/{$pluginName}/index.xml"))) {
	Respond::NotFoundPage();
} else {
	$pluginAttrs = array(
						"link" => $xmls->getValue('/plugin/link[lang()]'),
						"title" => htmlspecialchars($xmls->getValue('/plugin/title[lang()]')),
						"version" => htmlspecialchars($xmls->getValue('/plugin/version[lang()]')),
						"requirements" => $xmls->doesExist('/plugin/requirements/tattertools') ? $xmls->getValue('/plugin/requirements/tattertools') : $xmls->getValue('/plugin/requirements/textcube'),
						"description" => htmlspecialchars($xmls->getValue('/plugin/description[lang()]')),
						"authorLink" => $xmls->getAttribute('/plugin/author[lang()]', 'link'),
						"author" => htmlspecialchars($xmls->getValue('/plugin/author[lang()]')),
						"license" => htmlspecialchars($xmls->getValue('/plugin/license[lang()]')),
						"scope" => array(),
						);
	if ($xmls->doesExist('/plugin/binding/adminMenu'))
		array_push($pluginAttrs['scope'], '관리자');
	if ($xmls->doesExist('/plugin/binding/tag'))
		array_push($pluginAttrs['scope'], '블로그');
	if ($xmls->doesExist('/plugin/binding/center'))
		array_push($pluginAttrs['scope'], '대시보드');
	if ($xmls->doesExist('/plugin/binding/listener'))
		array_push($pluginAttrs['scope'], '분류없음');
	if ($xmls->doesExist('/plugin/binding/sidebar'))
		array_push($pluginAttrs['scope'], '사이드바');
	if ($xmls->doesExist('/plugin/binding/editor') || $xmls->doesExist('/plugin/binding/formatter'))
		array_push($pluginAttrs['scope'], '모듈');
	
	list($currentTextcubeVersion) = explode(' ', TEXTCUBE_VERSION, 2);
	$requirements = $currentTextcubeVersion >= $pluginAttrs['requirements'] ? true : false;
}

if (!$xmls->doesExist('/plugin/binding/config') || !$active) {
	$tabName = 'about';
}

if (file_exists(ROOT . "/plugins/{$pluginName}/images/icon_plugin.png")) {
	$iconPath = $serviceURL . "/plugins/{$pluginName}/images/icon_plugin.png";
} else {
	$iconPath = $serviceURL . $adminSkinSetting['skin'] . "/image/icon_plugin.png";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo _f("%1 설정", $pluginAttrs['title']);?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/plugin-config.css" />
<?php
if (true === file_exists(ROOT . "/plugins/$pluginName/plugin-config.css")) {
?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'];?>/plugins/<?php echo $pluginName;?>/plugin-config.css" />
<?php
}
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/pluginconfig.js"></script>
	<script type="text/javascript">
		//<![CDATA[
			var fieldnamelist = <?php echo $result['script'] ;?>;
			
			var errorMessage ={
				"1": "<?php echo _t('데이터처리 오류 발생.');?>",
				"2": "<?php echo _t('잘못된 입력입니다.');?>"
			};
			
			function saveConfig(plugin) {
				var xmlcon= new Converter(document, fieldnamelist) ; 
			   	var xmlData = encodeURIComponent(xmlcon.getXMLData());
				var request = new HTTPRequest("POST", "<?php echo $targetURL;?>");
				PM.addRequest(request, "<?php echo _t('설정을 저장하고 있습니다.');?>");
				request.onSuccess = function () {
					PM.removeRequest(this);
					PM.showMessage("<?php echo _t('저장 완료');?>", "center", "bottom");
					window.close();
				};		
				request.onError = function () {
					PM.removeRequest(this);
					if( this.getText("/response/error") == "9" )
						alert(this.getText("/response/customError"));
					else if( undefined != errorMessage[ this.getText("/response/error") ] )
						alert( errorMessage[ this.getText("/response/error") ] );
					else if( undefined != this.getText("/response/error") )			
						alert("<?php echo _t('알 수 없는 에러입니다.');?>" );
					else 
						alert("<?php echo _t('데이터 처리 페이지를 찾을 수 없습니다.');?>");
				};
				request.onVerify = function() {
					return (this.getText("/response/error") == "0" );
				};			
				request.send("Name=" + encodeURIComponent(plugin) + "&DATA=" + xmlData);
				xmlcon = null;
				request = null;
			}
			
			var currentTabName = '<?php echo $tabName;?>-tab';
			var objSubmitButton = <?php echo $xmls->doesExist('/plugin/binding/config') && $active && $tabName == 'about' ? 'true' : 'false';?>;
			
			function toggleTab(name) {
				var newTabName = name + '-tab';
				
				// hide current tab.
				document.getElementById(currentTabName).className = '';
				document.getElementById(currentTabName+'-body').style.display = 'none';
				
				document.getElementById(newTabName).className = 'selected';
				document.getElementById(newTabName+'-body').style.display = 'block';
				
				if (getObject('submitButton') != null && name == 'about') {
					document.getElementById('submitButton').parentNode.removeChild(document.getElementById('submitButton'));
					objSubmitButton = true;
				} else if (objSubmitButton == true && name == 'setting') {
					temp = document.getElementById('layout-foot').innerHTML;
					document.getElementById('layout-foot').innerHTML = '';
					
					if (STD.isIE == true) {
						tempInput = document.createElement('<INPUT onclick="saveConfig(\'<?php echo $pluginName;?>\'); return false;" />');
					} else {
						tempInput = document.createElement('INPUT');
						tempInput.setAttribute('onclick', "saveConfig('<?php echo $pluginName;?>'); return false;");
					}
					tempInput.setAttribute('type','submit');
					tempInput.id = 'submitButton';
					tempInput.className = 'input-button';
					tempInput.value = '<?php echo _t('저장');?>';
					
					document.getElementById('layout-foot').appendChild(tempInput);
					document.getElementById('layout-foot').innerHTML += temp;
					objSubmitButton = false;
				}
				
				currentTabName = newTabName;
			}	
		//]]>
	</script>
</head>
<body>
	<form method="post" action="<?php echo $targetURL;?>">
		<div id="layout-head">
			<h1 class="caption"><?php echo _f("%1 설정", $pluginAttrs['title']);?></h1>
		</div>
		
		<div id="layout-body">
			<ul id="menu">
				<li id="about-tab"<?php echo $tabName == 'about' ? ' class="selected"' : '';?> onclick="toggleTab('about'); return false;"><?php echo _t('이 플러그인에 대하여');?></li>
<?php
if ($xmls->doesExist('/plugin/binding/config')) {
	if ($active) {
?>
				<li id="setting-tab"<?php echo $tabName == 'setting' ? ' class="selected"' : '';?> onclick="toggleTab('setting'); return false;"><?php echo _t('플러그인 설정');?></li>
<?php
	} else {
?>
				<li id="setting-tab" class="dimmed"><?php echo _t('플러그인 설정');?></li>
<?php
	}
}
?>
			</ul>
			
			<div id="about-tab-body" class="tab"<?php echo $tabName != 'about' ? ' style="display: none"' : '';?>>
				<div id="iconBox">
					<img src="<?php echo $iconPath;?>" width="150" alt="<?php echo _t('플러그인 아이콘');?>" />
				</div>
				<div id="information">
					<div class="title">
						<h2><?php echo $pluginAttrs['title'];?></h2>
						
						<div id="version"><?php echo _f('버전 %1', $pluginAttrs['version']);?></div>
						
<?php
if ($pluginAttrs['authorLink']) {
	$authorLink = "<a href=\"{$pluginAttrs['authorLink']}\" target=\"_blank\">{$pluginAttrs['author']}</a>";
} else {
	$authorLink = $pluginAttrs['author'];
}
?>
						<div id="author">
							<?php echo _f('%1 제작', $authorLink);?>
						</div>
					</div>
					
					<div class="description">
<?php
if ($requirements == false) {
?>
  						<p class="requirement"><em><?php echo _f('이 플러그인을 사용하시려면 Textcube %1으로 업그레이드하셔야 합니다.', $pluginAttrs['requirements']);?></em></p>
<?php
}
?>
						<div class="temp-description">
							<?php echo $pluginAttrs['description'];?>
						</div>
					</div>
				
<?php

if ($pluginAttrs['license'] == 'GPL') {
	$licenseText = _f('이 플러그인은 %1 라이센스를 따릅니다.', $pluginAttrs['license']);
} else if (!empty($pluginAttrs['license'])) {
	$licenseText = _t('라이센스') . ' : ' . $pluginAttrs['license'];
}

if (isset($licenseText)) {
?>
					<div class="license">
						<?php echo $licenseText;?>
					</div>
					<div class="pluginDir">
						<?php echo _t('설치된 플러그인 경로').' : '.$pluginName;?>
					</div>
<?php
}
?>
				</div>
				
				<div class="clear"></div>
			</div>
			
<?php
if ($xmls->doesExist('/plugin/binding/config') && $active) {
?>
			<div id="setting-tab-body" class="tab"<?php echo $tabName != 'setting' ? ' style="display: none"' : '';?>>
				<div id="config_data">
					<?php echo $result['code'];?>
				</div>
			</div>
<?php
}
?>
		</div>
		
		<div id="layout-foot" class="button-box">
<?php
if ($xmls->doesExist('/plugin/binding/config') && $active && $tabName == 'setting') {
?>
			<input type="submit" id="submitButton" class="input-button" value="<?php echo _t('저장');?>" onclick="saveConfig('<?php echo $pluginName;?>'); return false;" />
<?php
}
?>
			<input type="button" class="input-button" value="<?php echo _t('닫기');?>" onclick="self.close();" />
		</div>
	</form>
</body>
</html>
