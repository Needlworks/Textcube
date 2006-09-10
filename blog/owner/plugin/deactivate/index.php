<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'name' => array('directory', 'default'=> null),
		'scope' => array('string', 'default' => 'global')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if ($_POST['scope'] == "sidebar-basic") {
	$sidebarOrder = explode("|", getUserSetting('sidebarOrder'));
	for ($i=0; $i<count($sidebarOrder); $i++) {
		if ($sidebarOrder[$i] == "%{$_POST['name']}%") {
			unset($sidebarOrder[$i]);
			break;
		}
	}
	setUserSetting('sidebarOrder', implode("|", $sidebarOrder));
	respondResultPage(0);
} else {
	$xmls = new XMLStruct();
	$xmls->open(file_get_contents(ROOT . "/plugins/{$_POST['name']}/index.xml"));
	if ($xmls->getValue('/plugin/scope') == "sidebar") {
		$sidebarOrder = explode("|", getUserSetting('sidebarOrder'));
		for ($i=0; $i<count($sidebarOrder); $i++) {
			if ($sidebarOrder[$i] == $_POST['name']) {
				unset($sidebarOrder[$i]);
				break;
			}
		}
		setUserSetting('sidebarOrder', implode("|", $sidebarOrder));
	}
	
	if (!empty($_POST['name']) && deactivatePlugin($_POST['name']))
		respondResultPage(0);
	respondResultPage(1);
}
?>
