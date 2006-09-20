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

if (isset($_POST['name'])) $_GET['name'] = $_POST['name'];

if ((isset($_GET['name'])) && (isset($adminMenuMappings[$_GET['name']]))) 
{
	
	$IV = array (
		'GET' => array(
			'name' => array('string', 'default' => '')
			)
		);
	
	foreach($adminMenuMappings[$_GET['name']]['params'] as $param) {
		$ivItem = array ( $param['type']);
		if (isset($param['default']) && !is_null($param['default']) ) $ivItem['default'] = $param['default'];
		if (isset($param['mandatory']) && !is_null($param['mandatory']) ) $ivItem['mandatory'] = $param['mandatory'];
		
		$IV['GET'][$param['name']] = $ivItem;
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$IV['POST'] = $IV['GET'];
		$IV['GET'] = array();
	}
	
	if (Validator::validate($IV)) {
		$plugin = $adminMenuMappings[$_GET['name']]['plugin'];
		$handler = $adminMenuMappings[$_GET['name']]['handler'];

		$pluginAccessURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin;
		$pluginMenuURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin . '/' . $handler;
		$pluginHandlerURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin;
		$pluginSelfURL = $pluginMenuURL;

		$pluginAccessParam = '?name=' . $plugin;
		$pluginSelfParam = '?name=' . $plugin . '/' . $handler;
		
		include_once (ROOT . "/plugins/$plugin/index.php");
		call_user_func($handler);
	}
}
require ROOT . '/lib/piece/owner/footer1.php';
?>