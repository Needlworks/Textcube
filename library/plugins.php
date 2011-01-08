<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
		
$activePlugins        = array();
$eventMappings        = array();
$tagMappings          = array();
$sidebarMappings      = array();
$coverpageMappings    = array();
$centerMappings       = array();
//$storageMappings      = array();
//$storageKeymappings   = array();
$adminMenuMappings    = array();
$adminHandlerMappings = array();
$configMappings       = array();
$baseConfigPost = $context->getProperty('service.path').'/owner/setting/plugins/currentSetting';
$configPost  = '';
$configVal   = '';
$typeSchema  = null;

$formatterMappings = array('html' => array('name' => _t('HTML'), 'editors' => array('plain' => '')));
$editorMappings    = array('plain' => array('name' => _t('편집기 없음')));
list($currentTextcubeVersion) = explode(' ', TEXTCUBE_VERSION, 2);

if (getBlogId()) {
	if($gCacheStorage->getContent('activePlugins')) $activePlugins = $gCacheStorage->getContent('activePlugins');
	else {
		$pool = DBModel::getInstance();
		$pool->reset('Plugins');
		$pool->setQualifier('blogid','eq',getBlogId());
		$activePlugins = $pool->getColumn('name');
		$gCacheStorage->setContent('activePlugins',$activePlugins);
	}
	$pageCache = pageCache::getInstance();
	$pageCache->reset('PluginSettings');
	$pageCache->load();
	
	$pluginSettings = $pageCache->contents;	

	$storageList = array('activePlugins','eventMappings','tagMappings',
		'sidebarMappings','coverpageMappings','centerMappings',//'storageMappings','storageKeymappings',
		'adminMenuMappings','adminHandlerMappings','configMappings','editorMappings','formatterMappings',
		'editorCount','formatterCount');

	$p = array();
	if(!empty($pluginSettings)) {
		$p = unserialize($pluginSettings);
		foreach ($storageList as $s) {
			${$s} = $p[$s];	
		}
	} else {
		$xmls = new XMLStruct();
		$editorCount     = 0;
		$formatterCount  = 0;
		if(!empty($activePlugins)) {
			if (file_exists(ROOT . "/cache/code/plugins-".getBlogId().".php")) {
				require_once(ROOT . "/cache/code/plugins-".getBlogId().".php");
				// TODO : set the editor / formatter count while using plugin php cache.
			} else {
				foreach ($activePlugins as $plugin) {
					$version = '';
					$disablePlugin= false;
					$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
					if ($manifest && $xmls->open($manifest)) {
						$requiredTattertoolsVersion = $xmls->getValue('/plugin/requirements/tattertools');
						$requiredTextcubeVersion = $xmls->getValue('/plugin/requirements/textcube');
						if(is_null($requiredTextcubeVersion) && !is_null($requiredTattertoolsVersion)) {
							$requiredTextcubeVersion = $requiredTattertoolsVersion;
						}
						$requiredMinVersion = $xmls->getValue('/plugin/requirements/textcube/minVersion');
						$requiredMaxVersion = $xmls->getValue('/plugin/requirements/textcube/maxVersion');
						if (!is_null($requiredMinVersion)) {
							if (version_compare($currentTextcubeVersion, $requiredMinVersion) < 0)
								$disablePlugin = true;
						}
						if (!is_null($requiredMaxVersion)) {
							if (version_compare($currentTextcubeVersion, $requiredMaxVersion) > 0)
								$disablePlugin = true;
						}
	
						if (!is_null($requiredTextcubeVersion)) {
							if (version_compare($currentTextcubeVersion,$requiredTextcubeVersion) < 0)
								$disablePlugin = true;
						}
						
						if ($disablePlugin == false) {
							if ($xmls->doesExist('/plugin/version')) {
								$version = $xmls->getValue('/plugin/version');
							}
							if ($xmls->doesExist('/plugin/storage')) {
								foreach ($xmls->selectNodes('/plugin/storage/table') as $table) {
									$storageMappings = array();
									$storageKeymappings = array();					 
									if(empty($table['name'][0]['.value'])) continue;
									$tableName = htmlspecialchars($table['name'][0]['.value']);
									if (!empty($table['fields'][0]['field'])) {
										foreach($table['fields'][0]['field'] as $field) 
										{
											if (!isset($field['name']))
												continue; // Error? maybe loading fail, so skipping is needed.
											$fieldName = $field['name'][0]['.value'];
										
											if (!isset($field['attribute']))
												continue; // Error? maybe loading fail, so skipping is needed.
											$fieldAttribute = $field['attribute'][0]['.value'];
										
											$fieldLength = isset($field['length']) ? $field['length'][0]['.value'] : -1;
											$fieldIsNull = isset($field['isnull']) ? $field['isnull'][0]['.value'] : 1;
											$fieldDefault = isset($field['default']) ? $field['default'][0]['.value'] : null;
											$fieldAutoIncrement = isset($field['autoincrement']) ? $field['autoincrement'][0]['.value'] : 0;
										
											array_push($storageMappings, array('name' => $fieldName, 'attribute' => $fieldAttribute, 'length' => $fieldLength, 'isnull' => $fieldIsNull, 'default' => $fieldDefault, 'autoincrement' => $fieldAutoIncrement));
										}
									}
									if (!empty($table['key'][0]['.value'])) {
										foreach($table['key'] as $key) {
											array_push($storageKeymappings, $key['.value']);
										}
									}
									treatPluginTable($plugin, $tableName, $storageMappings, $storageKeymappings, $version);
									unset($tableName);
									unset($storageMappings);
									unset($storageKeymappings);
								}
							}
							if ($xmls->doesExist('/plugin/binding/listener')) {
								foreach ($xmls->selectNodes('/plugin/binding/listener') as $listener) {
									if (!empty($listener['.attributes']['event']) && !empty($listener['.attributes']['handler'])) {
										if (!isset($eventMappings[$listener['.attributes']['event']]))
											$eventMappings[$listener['.attributes']['event']] = array();
										if (isset($listener['.attributes']['scope']) && in_array($listener['.attributes']['scope'], array('blog','mobile','owner')))
											$scope = $listener['.attributes']['scope'];
										else $scope = 'blog';
										array_push($eventMappings[$listener['.attributes']['event']], array('plugin' => $plugin, 'listener' => $listener['.attributes']['handler'], 'scope' => $scope));
									} else if (!empty($listener['.attributes']['event']) && !empty($listener['.value'])) {	// Legacy routine.
										if (!isset($eventMappings[$listener['.attributes']['event']]))
											$eventMappings[$listener['.attributes']['event']] = array();
										array_push($eventMappings[$listener['.attributes']['event']], array('plugin' => $plugin, 'listener' => $listener['.value'], 'scope' => 'blog'));
									}
								}
								unset($listener);
							}
							if ($xmls->doesExist('/plugin/binding/tag')) {
								foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
									if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
										if (!isset($tagMappings[$tag['.attributes']['name']]))
											$tagMappings[$tag['.attributes']['name']] = array();
										array_push($tagMappings[$tag['.attributes']['name']], array('plugin' => $plugin, 'handler' => $tag['.attributes']['handler']));
									}
								}
								unset($tag);
							}
							if (doesHaveMembership() && $xmls->doesExist('/plugin/binding/center')) {
								$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
								foreach ($xmls->selectNodes('/plugin/binding/center') as $center) {
									if (!empty($center['.attributes']['handler'])) {
										if(isset($center['.attributes']['title'])) {
											$title = $center['.attributes']['title'];
										} else {
											$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
										}
										array_push($centerMappings, array('plugin' => $plugin, 'handler' => $center['.attributes']['handler'], 'title' => $title));
									}
								}
								unset($title);
								unset($center);
							}
							if ($xmls->doesExist('/plugin/binding/sidebar')) {
								$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
								foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
									if (!empty($sidebar['.attributes']['handler'])) {
										// parameter parsing
										$parameters = array();
										if (isset($sidebar['params']) && isset($sidebar['params'][0]) && isset($sidebar['params'][0]['param'])) {
											foreach($sidebar['params'][0]['param'] as $param) {
												$parameter = array('name' => $param['name'][0]['.value'], 'type' => $param['type'][0]['.value'], 'title' => XMLStruct::getValueByLocale($param['title']));
												array_push($parameters, $parameter);				
											}
										}
										array_push($sidebarMappings, array('plugin' => $plugin, 'title' => $sidebar['.attributes']['title'], 'display' => $title, 'handler' => $sidebar['.attributes']['handler'], 'parameters' => $parameters));
									}
								}
								unset($sidebar);
							}
							if ($xmls->doesExist('/plugin/binding/coverpage')) {
								$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
								foreach ($xmls->selectNodes('/plugin/binding/coverpage') as $coverpage) {
									if (!empty($coverpage['.attributes']['handler'])) {
										// parameter parsing
										$parameters = array();
										if (isset($coverpage['params']) && isset($coverpage['params'][0]) && isset($coverpage['params'][0]['param'])) {
											foreach($coverpage['params'][0]['param'] as $param) {
												$parameter = array('name' => $param['name'][0]['.value'], 'type' => $param['type'][0]['.value'], 'title' => XMLStruct::getValueByLocale($param['title']));
												array_push($parameters, $parameter);				
											}
										}
										array_push($coverpageMappings, array('plugin' => $plugin, 'title' => $coverpage['.attributes']['title'], 'display' => $title, 'handler' => $coverpage['.attributes']['handler'], 'parameters' => $parameters));
									}
								}
								unset($coverpage);
							}
							if($xmls->doesExist('/plugin/binding/config[lang()]')) {
								$config = $xmls->selectNode('/plugin/binding/config[lang()]');
								if( !empty( $config['.attributes']['dataValHandler'] ) )
									$configMappings[$plugin] = 
									array( 'config' => 'ok' , 'dataValHandler' => $config['.attributes']['dataValHandler'] );
								else
									$configMappings[$plugin] = array( 'config' => 'ok') ;
							}
							if (doesHaveOwnership() && $xmls->doesExist('/plugin/binding/adminMenu')) {
								$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
			
								if ($xmls->doesExist('/plugin/binding/adminMenu/viewMethods')) {
									foreach($xmls->selectNodes('/plugin/binding/adminMenu/viewMethods/method') as $adminViewMenu) {
										$menutitle = htmlspecialchars(XMLStruct::getValueByLocale($adminViewMenu['title']));
										if (empty($menutitle)) continue;
										if(isset($adminViewMenu['topMenu'][0]['.value'])) {
											$pluginTopMenuLocation = htmlspecialchars($adminViewMenu['topMenu'][0]['.value']);
											switch($pluginTopMenuLocation) {
												case 'center':
												case 'entry':
												case 'link':
												case 'skin':
												case 'plugin':
												case 'setting':
													break;
												default:
													$pluginTopMenuLocation = 'plugin';
											}
										} else {
											$pluginTopMenuLocation = 'plugin';
										}
										$pluginContentMenuOrder = empty($adminViewMenu['contentMenuOrder'][0]['.value'])? '100':$adminViewMenu['contentMenuOrder'][0]['.value'];
										$menuhelpurl = empty($adminViewMenu['helpurl'][0]['.value'])?'':$adminViewMenu['helpurl'][0]['.value'];
									
										if (!isset($adminViewMenu['handler'][0]['.value'])) continue;
										$viewhandler = htmlspecialchars($adminViewMenu['handler'][0]['.value']);	
										if (empty($viewhandler)) continue;
										$params = array();
										if (isset($adminViewMenu['params'][0]['param'])) {
											foreach($adminViewMenu['params'][0]['param'] as $methodParam) {
													if (!isset($methodParam['name'][0]['.value']) || !isset($methodParam['type'][0]['.value'])) continue;
													$mandatory = null;
													$default   = null;
													if( isset($methodParam['mandatory'][0]['.value']) ) {
														$mandatory = $methodParam['mandatory'][0]['.value'];
													}
													if( isset($methodParam['default'][0]['.value']) ) {
														$default = $methodParam['default'][0]['.value'];
													}
													array_push($params,array(
															'name' => $methodParam['name'][0]['.value'],
															'type' => $methodParam['type'][0]['.value'],
															'mandatory' => $mandatory,
															'default' => $default
															));
											}
										}
											
										$adminMenuMappings[$plugin . '/' . $viewhandler] = array(
											'plugin'   => $plugin, 
											'title'    => $menutitle,
											'handler'  => $viewhandler,
											'params'   => $params,
											'helpurl'  => $menuhelpurl,
											'topMenu'  => $pluginTopMenuLocation,
											'contentMenuOrder' => $pluginContentMenuOrder
										);
									}
								}
							
								unset($menutitle);
								unset($viewhandler);
								unset($adminViewMenu);
								unset($params);
							
								if (doesHaveOwnership() &&$xmls->doesExist('/plugin/binding/adminMenu/methods')) {
									foreach($xmls->selectNodes('/plugin/binding/adminMenu/methods/method') as $adminMethods) {
										$method = array();
										$method['plugin'] = $plugin;
										if (!isset($adminMethods['handler'][0]['.value'])) continue;
										$method['handler'] = $adminMethods['handler'][0]['.value'];
										$method['params'] = array();
											if (isset($adminMethods['params'][0]['param'])) {
											foreach($adminMethods['params'][0]['param'] as $methodParam) {
												if (!isset($methodParam['name'][0]['.value']) || !isset($methodParam['type'][0]['.value'])) continue;
												$mandatory = null;
												$default   = null;
												if( isset($methodParam['mandatory'][0]['.value']) ) {
													$mandatory = $methodParam['mandatory'][0]['.value'];
												}
												if( isset($methodParam['default'][0]['.value']) ) {
													$default = $methodParam['default'][0]['.value'];
												}
												array_push($method['params'],array(
													'name' => $methodParam['name'][0]['.value'],
													'type' => $methodParam['type'][0]['.value'],
													'mandatory' => $mandatory,
													'default' => $default
												));
											}
										}
										$adminHandlerMappings[$plugin . '/' . $method['handler']] = $method;
									}
								}
							
								unset($method);
								unset($methodParam);
								unset($adminMethods);
							
							}
							if ($xmls->doesExist('/plugin/binding/formatter[lang()]')) {
								$formatterCount = $formatterCount + 1;
								foreach (array($xmls->selectNode('/plugin/binding/formatter[lang()]')) as $formatter) {
									if (!isset($formatter['.attributes']['name'])) continue;
									if (!isset($formatter['.attributes']['id'])) continue;
									$formatterid = $formatter['.attributes']['id'];
									$formatterinfo = array('id' => $formatterid, 'name' => $formatter['.attributes']['name'], 'plugin' => $plugin, 'editors' => array());
									if (isset($formatter['format'][0]['.value'])) $formatterinfo['formatfunc'] = $formatter['format'][0]['.value'];
									if (isset($formatter['summary'][0]['.value'])) $formatterinfo['summaryfunc'] = $formatter['summary'][0]['.value'];
									if (isset($formatter['usedFor'])) {
										foreach ($formatter['usedFor'] as $usedFor) {
											if (!isset($usedFor['.attributes']['editor'])) continue;
											$formatterinfo['editors'][$usedFor['.attributes']['editor']] = @$usedFor['.value'];
										}
									}
									$formatterMappings[$formatterid] = $formatterinfo;
								}
								unset($formatter);
								unset($formatterid);
								unset($formatterinfo);
								unset($usedFor);
							}
							if (doesHaveOwnership() && $xmls->doesExist('/plugin/binding/editor[lang()]')) {
								$editorCount = $editorCount + 1;
								foreach (array($xmls->selectNode('/plugin/binding/editor[lang()]')) as $editor) {
									if (!isset($editor['.attributes']['name'])) continue;
									if (!isset($editor['.attributes']['id'])) continue;
									$editorid = $editor['.attributes']['id'];
									$editorinfo = array('id' => $editorid, 'name' => $editor['.attributes']['name'], 'plugin' => $plugin);
									if (isset($editor['initialize'][0]['.value'])) $editorinfo['initfunc'] = $editor['initialize'][0]['.value'];
									if (isset($editor['usedFor'])) {
										foreach ($editor['usedFor'] as $usedFor) {
											if (!isset($usedFor['.attributes']['formatter'])) continue;
											if(isset($formatterMappings[$usedFor['.attributes']['formatter']]))
												$formatterMappings[$usedFor['.attributes']['formatter']]['editors'][$editorid] = @$usedFor['.value'];
										}
									}
									$editorMappings[$editorid] = $editorinfo;
								}
								unset($editor);
								unset($editorid);
								unset($editorinfo);
								unset($usedFor);
							}
						}
					} else {
						$disablePlugin = true;
					}
					
					if ($disablePlugin == true) {
						deactivatePlugin($plugin);
					}
				}
				unset($xmls);
				unset($currentTextcubeVersion, $disablePlugin, $plugin, $query, $requiredTattertoolsVersion, $requiredTextcubeVersion);
			}
		}
		foreach ($storageList as $s) {
			$p[$s] = ${$s};	
		}
		$pageCache->contents = serialize($p);	
		$pageCache->update();
	}
	if(empty($formatterCount)) { // Any formatter is used, add the ttml formatter.
		activatePlugin('FM_TTML');
	}
	if(empty($editorCount)) { // Any editor is used, add the textcube editor.
		activatePlugin('FM_Modern');
	}
	// sort mapping by its name, with exception for default formatter and editor
	if (doesHaveOwnership()) {
		$_fMapping = $formatterMappings;
		$_eMapping = $editorMappings;
		function _cmpfuncByFormatterName($x, $y) {
			global $_fMapping;
			if ($x == 'html') return -1;
			if ($y == 'html') return +1;
			return strcmp($_fMapping[$x]['name'], $_fMapping[$y]['name']);
		}
		function _cmpfuncByEditorName($x, $y) {
			global $_eMapping;
			if ($x == 'plain') return -1;
			if ($y == 'plain') return +1;
			return strcmp($_eMapping[$x]['name'], $_eMapping[$y]['name']);
		}
		uksort($editorMappings, '_cmpfuncByEditorName');
		uksort($formatterMappings, '_cmpfuncByFormatterName');
		foreach ($formatterMappings as $formatterid => $formatterentry) {
//			uksort($formatterMapping[$formatterid]['editors'], '_cmpfuncByEditorName');
		}
	}
	unset($formatterid);
	unset($formatterentry);
}
?>
