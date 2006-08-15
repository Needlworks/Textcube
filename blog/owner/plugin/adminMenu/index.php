<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerB.php';
require ROOT . '/lib/piece/owner/contentMenuB0.php';
if (false) {
	fetchConfigVal();
	getUserSetting();
	setUserSetting();
}
$plugin = $_GET['name'];
$pluginAttrs = array();
$pluginDir = $plugin;
if (file_exists(ROOT . "/plugins/$plugin/index.xml")) {
	$xmls = new XMLStruct();
	if ($xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml")) && (htmlspecialchars($xmls->getValue('/plugin/scope[lang()]')) == 'admin')) {
		$pluginAttrs = array(
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
		$link = $pluginAttrs['link'];
		$title = $pluginAttrs['title'];
		$version = $pluginAttrs['version'];
		$description = $pluginAttrs['description'];
		$authorLink = $pluginAttrs['authorLink'];
		$author = $pluginAttrs['author'];
		$scope = $pluginAttrs['scope'];
		$config = $pluginAttrs['config']? 'Y':'N';
		$adminMenu = $pluginAttrs['adminMenu'];
		$active = in_array($pluginDir, $activePlugins);
		if ($active)
			include_once (ROOT . "/plugins/$plugin/index.php");
	}
}
require ROOT . '/lib/piece/owner/footer1.php';
?>