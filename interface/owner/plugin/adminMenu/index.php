<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';


if (false) {
	fetchConfigVal();
	getBlogSetting();
	setBlogSetting();
}

if ((isset($_REQUEST['name'])) && (isset($adminMenuMappings[$_REQUEST['name']]))) 
{
	
	$IV = array (
		'REQUEST' => array(
			'name' => array('string')
			)
		);
	
	foreach($adminMenuMappings[$_REQUEST['name']]['params'] as $param) {
		$ivItem = array ( $param['type']);
		if (isset($param['default']) && !is_null($param['default']) ) $ivItem['default'] = $param['default'];
		if (isset($param['mandatory']) && !is_null($param['mandatory']) ) $ivItem['mandatory'] = $param['mandatory'];
		
		$IV['REQUEST'][$param['name']] = $ivItem;
	}
	
	if (Validator::validate($IV)) {
		$_GET = $_POST = $_REQUEST;		
		$plugin = $adminMenuMappings[$_REQUEST['name']]['plugin'];
		$handler = $adminMenuMappings[$_REQUEST['name']]['handler'];

		$pluginAccessURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin;
		$pluginMenuURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin . '/' . $handler;
		$pluginHandlerURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin;
		$pluginSelfURL = $pluginMenuURL;

		$pluginAccessParam = '?name=' . $plugin;
		$pluginSelfParam = '?name=' . $plugin . '/' . $handler;
		
		$pluginURL = "{$service['path']}/plugins/{$plugin}";
		$pluginPath = ROOT . "/plugins/{$plugin}";
		$pluginName = $plugin;
		include_once (ROOT . "/plugins/{$plugin}/index.php");
		if (function_exists($handler)) {
			if( !empty( $configMappings[$plugin]['config'] ) ) 				
				$configVal = getCurrentSetting($plugin);
			else
				$configVal ='';
			call_user_func($handler);
		}
	}
}
require ROOT . '/interface/common/owner/footer.php';
?>
