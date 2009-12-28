<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';

requireStrictRoute();
if (false) {
	fetchConfigVal();
	getBlogSetting();
	setBlogSetting();
}

if ((isset($_REQUEST['name'])) && (isset($adminHandlerMappings[$_REQUEST['name']]))) 
{
	
	$IV = array (
		'REQUEST' => array(
			'name' => array('string')
			)
		);
	
	foreach($adminHandlerMappings[$_GET['name']]['params'] as $param) {
		$ivItem = array ( $param['type']);
		if (isset($param['default']) && !is_null($param['default']) ) $ivItem['default'] = $param['default'];
		if (isset($param['mandatory']) && !is_null($param['mandatory']) ) $ivItem['mandatory'] = $param['mandatory'];
		
		$IV['REQUEST'][$param['name']] = $ivItem;
	}
	
	if (Validator::validate($IV)) {
		$plugin = $adminHandlerMappings[$_REQUEST['name']]['plugin'];
		$handler = $adminHandlerMappings[$_REQUEST['name']]['handler'];
		
		$pluginAccessURL = $blogURL . '/owner/plugin/adminMenu?name=' . $plugin;
		$pluginMenuURL = 'invalid link';
		$pluginHandlerURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin;
		$pluginSelfURL = $blogURL . '/owner/plugin/adminHandler?name=' . $plugin . '/' . $handler;
		
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

?>
