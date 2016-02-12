<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
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
		$context = Model_Context::getInstance();

		$plugin = $adminHandlerMappings[$_REQUEST['name']]['plugin'];
		$handler = $adminHandlerMappings[$_REQUEST['name']]['handler'];

		$pluginAccessURL = $context->getProperty('uri.blog') . '/owner/plugin/adminMenu?name=' . $plugin;
		$pluginMenuURL = 'invalid link';
		$pluginHandlerURL = $context->getProperty('uri.blog') . '/owner/plugin/adminHandler?name=' . $plugin;
		$pluginSelfURL = $context->getProperty('uri.blog') . '/owner/plugin/adminHandler?name=' . $plugin . '/' . $handler;

		$pluginAccessParam = '?name=' . $plugin;
		$pluginSelfParam = '?name=' . $plugin . '/' . $handler;

		$context->setProperty('plugin.uri.access',$pluginAccessURL);
		$context->setProperty('plugin.uri.menu',$pluginMenuURL);
		$context->setProperty('plugin.uri.handler',$pluginHandlerURL);
		$context->setProperty('plugin.uri.self',$pluginSelfURL);
		$context->setProperty('plugin.parameter.access',$pluginAccessParam);
		$context->setProperty('plugin.parameter.self',$pluginSelfParam);

		$context->setProperty('plugin.uri',$context->getProperty('service.path')."/plugins/{$plugin}");
		$context->setProperty('plugin.path',ROOT . "/plugins/{$plugin}");
		$context->setProperty('plugin.name',$plugin);


		$pluginURL = $context->getProperty('plugin.uri');
		$pluginPath = $context->getProperty('plugin.path');
		$pluginName = $context->getProperty('plugin.name');

		include_once (ROOT . "/plugins/{$plugin}/index.php");
		if (function_exists($handler)) {
			if( !empty( $configMappings[$plugin]['config'] ) ) {
				$configVal = getCurrentSetting($plugin);
				$context->setProperty('plugin.config',Setting::fetchConfigVal($configVal));
			} else {
				$configVal ='';
				$context->setProperty('plugin.config',array());
			}

			call_user_func($handler);
		}
		$context->unsetProperty('plugin.uri');
		$context->unsetProperty('plugin.path');
		$context->unsetProperty('plugin.name');
		$context->unsetProperty('plugin.uri.access');
		$context->unsetProperty('plugin.uri.menu');
		$context->unsetProperty('plugin.uri.handler');
		$context->unsetProperty('plugin.uri.self');
		$context->unsetProperty('plugin.parameter.access');
		$context->unsetProperty('plugin.parameter.self');

	}
}

?>
