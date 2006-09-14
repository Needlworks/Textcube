			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 플러그인');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-plugin"<?php echo ((!isset($_GET['name'])) ? ' class="selected"' : '');?>><a href="<?php echo $blogURL;?>/owner/plugin"><span class="text"><?php echo _t('플러그인 목록');?></span></a></li>
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
		if (htmlspecialchars($xmls->getValue('/plugin/scope[lang()]')) == 'admin') {
			$pluginAttrs[$pluginDir] = array(
				"link" => $xmls->getValue('/plugin/link[lang()]'),
				"title" => htmlspecialchars($xmls->getValue('/plugin/title[lang()]')),
				"version" => htmlspecialchars($xmls->getValue('/plugin/version[lang()]')),
				"description" => htmlspecialchars($xmls->getValue('/plugin/description[lang()]')),
				"authorLink" => $xmls->getAttribute('/plugin/author[lang()]', 'link'),
				"author" => htmlspecialchars($xmls->getValue('/plugin/author[lang()]')),
				"scope" => htmlspecialchars($xmls->getValue('/plugin/scope[lang()]')),
				"adminMenu" => htmlspecialchars($xmls->getValue('/plugin/binding/adminMenu')),
				"config" => $xmls->doesExist('/plugin/binding/config'),
				"width" => $xmls->getAttribute('/plugin/binding/config/window', 'width'),
				"height" => $xmls->getAttribute('/plugin/binding/config/window', 'height')
			);
			$plugins[$pluginDir] = $pluginAttrs[$pluginDir]['title'];
		}
	}
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
	$adminMenu = $pluginAttrs[$pluginDir]['adminMenu'];
	$width = $pluginAttrs[$pluginDir]['width']?$pluginAttrs[$pluginDir]['width']:500;
	$height = $pluginAttrs[$pluginDir]['height']?$pluginAttrs[$pluginDir]['height']:400;
	$active = in_array($pluginDir, $activePlugins);

	if ($active) {
?>
						<li <?php echo ((isset($_GET['name']) && ($_GET['name']==$pluginDir)) ? ' class="selected"' : '');?>><a href="<?php echo $blogURL;?>/owner/plugin/adminMenu?name=<?php echo $pluginDir;?>"><span class="text"><?php echo $title;?></span></a></li>
<?php
	}
}
?>



						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/19" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
