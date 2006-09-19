<?php


define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';

requireStrictRoute();
if (false) {
	fetchConfigVal();
	getUserSetting();
	setUserSetting();
}

if (isset($_POST['name'])) $_GET['name'] = $_POST['name'];

if ((isset($_GET['name'])) && (isset($adminHandlerMappings[$_GET['name']]))) 
{
	
	$IV = array (
		'GET' => array(
			'name' => array('string', 'default' => '')
			)
		);
	
	foreach($adminHandlerMappings[$_GET['name']]['params'] as $param) {
		$ivItem = array ( $param['type']);
		if (isset($param['default']) && !is_null($param['default']) ) $ivItem['default'] = $param['default'];
		if (isset($param['mandatory']) && !is_null($param['mandatory']) ) $ivItem['mandatory'] = $param['mandatory'];
		
		$IV['GET'][$param['name']] = $ivItem;
	}
	$IV['POST'] = $IV['GET'];
	
	if (Validator::validate($IV)) {
		$plugin = $adminHandlerMappings[$_GET['name']]['plugin'];
		$handler = $adminHandlerMappings[$_GET['name']]['handler'];
		
		$pluginAccessURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin;
		$pluginMenuURL = 'invalid link';
		$pluginHandlerURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin;
		$pluginSelfURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin . '/' . $handler;
		
		$pluginAccessParam = '?name=' . $plugin;
		$pluginSelfParam = '?name=' . $plugin . '/' . $handler;
		
		include_once (ROOT . "/plugins/$plugin/index.php");
		call_user_func($handler);
	}
}

?>