<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

global $pluginSetting;
$pluginSetting = array();

/***** Plugin data manipulation *****/
function clearPluginSettingCache()
{
	global $pluginSetting, $gCacheStorage;
	$gCacheStorage->purge();
	if( !empty($pluginSetting) ) {
		$pluginSetting = array();
	}
}

function activatePlugin($name) {
	global $database, $activePlugins, $gCacheStorage;
	if (in_array($name, $activePlugins))
		return true;
	if (!preg_match('/^[-a-zA-Z0-9_ ]+$/', $name))
		return false;
	if (!is_dir(ROOT . "/plugins/$name"))
		return false;
	if (!file_exists(ROOT . "/plugins/$name/index.xml") || !file_exists(ROOT . "/plugins/$name/index.php"))
		return false;
	$xmls = new XMLStruct();
	$manifest = @file_get_contents(ROOT . "/plugins/$name/index.xml");
	if ($xmls->open($manifest)) {
		list($currentTextcubeVersion) = explode(' ', TEXTCUBE_VERSION, 2);
		
		$requiredTattertoolsVersion = $xmls->getValue('/plugin/requirements/tattertools');
		$requiredTextcubeVersion = $xmls->getValue('/plugin/requirements/textcube');
		if(is_null($requiredTextcubeVersion) && !is_null($requiredTattertoolsVersion)) {
			$requiredTextcubeVersion = $requiredTattertoolsVersion;
		}
		$requiredMinVersion = $xmls->getValue('/plugin/requirements/textcube/minVersion');
		$requiredMaxVersion = $xmls->getValue('/plugin/requirements/textcube/maxVersion');
		if (!is_null($requiredMinVersion)) {
			if (version_compare($currentTextcubeVersion, $requiredMinVersion) < 0)
				return false;
		}
		if (!is_null($requiredMaxVersion)) {
			if (version_compare($currentTextcubeVersion, $requiredMaxVersion) > 0)
				return false;
		}
		if (!is_null($requiredTextcubeVersion)) {
			if (version_compare($currentTextcubeVersion,$requiredTextcubeVersion) < 0)
				return false;
		}		
	} else {
		return false;
	}
	$pluginName = $name;
	$name = POD::escapeString(UTF8::lessenAsEncoding($name, 255));
	$result = POD::queryCount("INSERT INTO {$database['prefix']}Plugins VALUES (".getBlogId().", '$name', null)");
	clearPluginSettingCache();
	CacheControl::flushItemsByPlugin($pluginName);
	return ($result == 1);
}

function deactivatePlugin($name) {
	global $database, $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$pluginName = $name;
	$name = POD::escapeString($name);
	POD::query("DELETE FROM {$database['prefix']}Plugins 
			WHERE blogid = ".getBlogId()."
				AND name = '$name'");
	clearPluginSettingCache();
	CacheControl::flushItemsByPlugin($pluginName);
	return true;
}

function getCurrentSetting($name) {
	global $database, $activePlugins;
	global $pluginSetting;
	if( !in_array( $name , $activePlugins))
		return false;
	if( empty($pluginSetting) ) {
		$settings = POD::queryAllWithCache("SELECT name, settings 
				FROM {$database['prefix']}Plugins 
				WHERE blogid = ".getBlogId(), MYSQL_NUM );
		foreach( $settings as $k => $v ) {
			$pluginSetting[ $v[0] ] = $v[1];
		}
	}
	if( isset($pluginSetting[$name]) ) {
		return $pluginSetting[$name];
	}
	return null;
}

function isActivePlugin( $name ) {
	global $activePlugins;
	return in_array($name , $activePlugins);
}

function updatePluginConfig( $name , $setVal) {
	global $database,  $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$pluginName = $name;
	$name = POD::escapeString( UTF8::lessenAsEncoding($name, 255) ) ;
	$setVal = POD::escapeString( $setVal ) ;
	$count = POD::queryCount(
		"UPDATE {$database['prefix']}Plugins 
			SET settings = '$setVal' 
			WHERE blogid = ".getBlogId()."
			AND name = '$name'"
		);
	if( $count == 1 )
		$result = '0';
	clearPluginSettingCache();
	CacheControl::flushItemsByPlugin($pluginName);
	if(isset($result) && $result = '0') return $result;
	return (POD::error() == '') ? '0' : '1';
}

function getPluginInformation($plugin) {
	$xmls = new XMLStruct();
	// Error checking routine
	if (!preg_match('@^[A-Za-z0-9 _-]+$@', $plugin))
		return false;
	if (!file_exists(ROOT . "/plugins/$plugin/index.xml"))
		return false;
	if (!$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml"))) {
		error_log( "PLUGIN XML_PARSE_ERROR: ". $plugin. ": ". 
		xml_error_string($xmls->error['code']));
		return false;
	} else {
		// Determine plugin scopes.
		$scopeByXMLPath = array(
			'admin'     => '/plugin/binding/adminMenu',
			'blog'      => '/plugin/binding/tag',
			'center'    => '/plugin/binding/center',
			'coverpage'  => '/plugin/binding/coverpage',
			'global'    => '/plugin/binding/listener',
			'sidebar'   => '/plugin/binding/sidebar',
			'editor'    => '/plugin/binding/editor',
			'formatter' => '/plugin/binding/formatter'
		);
		$pluginScope = array();
		$scopeCount = 0;
		foreach ($scopeByXMLPath as $key => $value) {
			if ($xmls->doesExist($value)) {
				array_push($pluginScope, $key);
				$scopeCount = $scopeCount + 1;
			}
		}
		if($scopeCount == 0) array_push($pluginScope, 'none');
		// load plugin information.
		$maxVersion = max($xmls->getValue('/plugin/requirements/tattertools'),$xmls->getValue('/plugin/requirements/textcube'));
		$requiredVersion = empty($maxVersion) ? 0 : $maxVersion; 

		$pluginInformation = array(
			'link'         => $xmls->getValue('/plugin/link[lang()]'),
			'title'        => $xmls->getValue('/plugin/title[lang()]'),
			'version'      => $xmls->getValue('/plugin/version[lang()]'),
			'requirements' => $requiredVersion,
			'scope'        => $pluginScope,
			'description'  => $xmls->getValue('/plugin/description[lang()]'),
			'authorLink'   => $xmls->getAttribute('/plugin/author[lang()]', 'link'),
			'author'       => $xmls->getValue('/plugin/author[lang()]'),
			'config'       => $xmls->doesExist('/plugin/binding/config'),
			'directory'    => trim($plugin),
			'width'        => $xmls->getAttribute('/plugin/binding/config/window', 'width'),
			'height'       => $xmls->getAttribute('/plugin/binding/config/window', 'height'),
			'privilege'    => $xmls->getValue('/plugin/requirements/privilege')
		);
		return $pluginInformation;
	}
	return null;
}

function treatPluginTable($plugin, $name, $fields, $keys, $version) {
	global $database;
	if(doesExistTable($database['prefix'] . $name)) {
		$keyname = 'Database_' . $name;
		$value = $plugin;
		$result = getServiceSetting($keyname, null);
		if (is_null($result)) {
			$keyname = UTF8::lessenAsEncoding($keyname, 32);
			$value = UTF8::lessenAsEncoding($plugin . '/' . $version , 255);
			$query = DBModel::getInstance();
			$query->reset('ServiceSettings');
			$query->setAttribute('name',$keyname,true);
			$query->setAttribute('value',$value,true);
			$query->insert();
		} else {
			$keyname = UTF8::lessenAsEncoding($keyname, 32);
			$value = UTF8::lessenAsEncoding($plugin . '/' . $version , 255);
			$values = explode('/', $result, 2);
			if (strcmp($plugin, $values[0]) != 0) { // diff plugin
				return false; // nothing can be done
			} else if (strcmp($version, $values[1]) != 0) {
				$query = DBModel::getInstance();
				$query->reset('ServiceSettings');
				$query->setQualifier('name','equals',$keyname,true);
				$query->setAttribute('value',$value,true);
				$query->update();
				$eventName = 'UpdateDB_' . $name;
				fireEvent($eventName, $values[1]);
			}
		}
		return true;
	} else {
		$query = "CREATE TABLE {$database['prefix']}{$name} (blogid int(11) NOT NULL default 0,";
		$isaiExists = false;
		$index = '';
		foreach($fields as $field) {
			$ai = '';
			if( strtolower($field['attribute']) == 'int' || strtolower($field['attribute']) == 'mediumint'  ) {
				if($field['autoincrement'] == 1 && !$isaiExists) {
					$ai = ' AUTO_INCREMENT ';
					$isaiExists = true;
					if(!in_array($field['name'], $keys))
						$index = ", KEY({$field['name']})";
				}
			}
			$isNull = ($field['isnull'] == 0) ? ' NOT NULL ' : ' NULL ';
			$defaultValue = is_null($field['default']) ? '' : " DEFAULT '" . POD::escapeString($field['default']) . "' ";
			$fieldLength = ($field['length'] >= 0) ? "(".$field['length'].")" : '';
			$sentence = $field['name'] . " " . $field['attribute'] . $fieldLength . $isNull . $defaultValue . $ai . ",";
			$query .= $sentence;
		}
		
		array_unshift($keys, 'blogid');
		$query .= " PRIMARY KEY (" . implode(',',$keys) . ")";
		$query .= $index;
		$query .= ") TYPE=MyISAM ";
		$query .= (POD::charset() == 'utf8') ? 'DEFAULT CHARSET=utf8' : '';
		if (POD::execute($query)) {
				$keyname = POD::escapeString(UTF8::lessenAsEncoding('Database_' . $name, 32));
				$value = POD::escapeString(UTF8::lessenAsEncoding($plugin . '/' . $version , 255));
				POD::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
			return true;
		}
		else return false;
		
	}
	return true;
}

function clearPluginTable($name) {
	global $database;
	$name = POD::escapeString($name);
	$count = POD::queryCount("DELETE FROM {$database['prefix']}{$name} WHERE blogid = ".getBlogId());
	return ($count == 1);
}

function deletePluginTable($name) {
	global $database;
	if(getBlogId() !== 0) return false;
	$name = POD::escapeString($name);
	POD::query("DROP {$database['prefix']}{$name}");
	return true;
}

function getPluginTableName() {
	requireModel('common.setting');

	global $database;
	
	$likeEscape = array ( '/_/' , '/%/' );
	$likeReplace = array ( '\\_' , '\\%' );
	$escapename = preg_replace($likeEscape, $likeReplace, $database['prefix']);

	$dbtables = POD::tableList($escapename);

	$dbCaseInsensitive = getServiceSetting('lowercaseTableNames');
	if($dbCaseInsensitive === null) {
		$result = POD::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
		$dbCaseInsensitive = ($result['Value'] == 1) ? 1 : 0;
		setServiceSetting('lowercaseTableNames',$dbCaseInsensitive);
	}

	$definedTables = getDefinedTableNames();

	$dbtables = array_values(array_diff($dbtables, $definedTables));
	if ($dbCaseInsensitive == 1) {
		$tempTables = $definedTables;
		$definedTables = array();
		foreach($tempTables as $table) {
			$table = strtolower($table);
			array_push($definedTables, $table);
		}
		$tempTables = $dbtables;
		$dbtables = array();
		foreach($tempTables as $table) {
			$table = strtolower($table);
			array_push($dbtables, $table);
		}
		$dbtables = array_values(array_diff($dbtables, $definedTables));
	}
	return $dbtables;
}


/***** Events and configuration handles *****/

function eventExists($event)
{
	global $eventMappings;
	return isset($eventMappings[$event]);
}

function fireEvent($event, $target = null, $mother = null, $condition = true) {
	global $service, $eventMappings, $pluginURL, $pluginPath, $pluginName, $configMappings, $configVal;
	$context = Model_Context::getInstance();
	if (!$condition)
		return $target;
	if (!isset($eventMappings[$event]))
		return $target;
	foreach ($eventMappings[$event] as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		if (function_exists($mapping['listener'])) {
			if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
				$configVal = getCurrentSetting($mapping['plugin']);
			else
				$configVal = null;
			$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
			$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
			$pluginName = $mapping['plugin'];
			// Loading locale resource
			$languageDomain = null;
			if(is_dir($pluginPath . '/locale/')) {
				$locale = Locales::getInstance();
				$languageDomain = $locale->domain;

				// Event listener language setting as scope defined.
				if(strpos($event, "/plugin/") === 0) {
					if($mapping['scope'] == 'owner') {
						$language = $context->getProperty('blog.language') !== null ? $context->getProperty('blog.language') : $context->getProperty('service.language');
					} else {
						$language = $context->getProperty('blog.blogLanguage') !== null ? $context->getProperty('blog.blogLanguage') : $context->getProperty('service.language');
					}
				} else {
					$language = $locale->defaultLanguage;
				}

				if(file_exists($pluginPath.'/locale/'.$language.'.php')) {
					$locale->setDirectory($pluginPath.'/locale');
					$locale->set($language, $mapping['plugin']);
					$locale->domain = $mapping['plugin'];
				}
			}
			$target = call_user_func($mapping['listener'], $target, $mother);
			/// unload.
			if(!is_null($languageDomain)) $locale->domain = $languageDomain;
			$pluginURL = $pluginPath = $pluginName = "";
		}
	}
	return $target;
}

function handleTags( & $content) {
	global $service, $tagMappings, $pluginURL, $pluginPath, $pluginName, $configMappings, $configVal;
	if (preg_match_all('/\[##_(\w+)_##\]/', $content, $matches)) {
		foreach ($matches[1] as $tag) {
			if (!isset($tagMappings[$tag]))
				continue;
			$target = '';
			foreach ($tagMappings[$tag] as $mapping) {
				include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
				if (function_exists($mapping['handler'])) {
					if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
						$configVal = getCurrentSetting($mapping['plugin']);
					else
						$configVal ='';
					$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
					$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
					$pluginName = $mapping['plugin'];
					// Loading locale resource
					$languageDomain = null;
					if(is_dir($pluginPath . '/locale/')) {
						$locale = Locales::getInstance();
						$languageDomain = $locale->domain;
						if(file_exists($pluginPath.'/locale/'.$locale->defaultLanguage.'.php')) {
							$locale->setDirectory($pluginPath.'/locale');
							$locale->set($locale->defaultLanguage, $mapping['plugin']);
							$locale->domain = $mapping['plugin'];
						}
					}					
					$target = call_user_func($mapping['handler'], $target);
					if(!is_null($languageDomain)) $locale->domain = $languageDomain;
					$pluginURL = $pluginPath = $pluginName = "";
				}
			}
			dress($tag, $target, $content);
		}
	}
}

function handleCenters($mapping) {
	global $service, $pluginURL, $pluginPath, $pluginName, $configMappings, $configVal;
	$target = '';

	include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
	if (function_exists($mapping['handler'])) {
		if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
			$configVal = getCurrentSetting($mapping['plugin']);
		else
			$configVal ='';
		$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
		$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
		$pluginName = $mapping['plugin'];
		// Loading locale resource
		$languageDomain = null;
		if(is_dir($pluginPath . '/locale/')) {
			$locale = Locales::getInstance();
			$languageDomain = $locale->domain;
			if(file_exists($pluginPath.'/locale/'.$locale->defaultLanguage.'.php')) {
				$locale->setDirectory($pluginPath.'/locale');
				$locale->set($locale->defaultLanguage, $pluginName);
				$locale->domain = $mapping['plugin'];
			}
		}		
		$target = call_user_func($mapping['handler'], $target);
		if(!is_null($languageDomain)) $locale->domain = $languageDomain;
		$pluginURL = $pluginPath = $pluginName = "";
	}

	return $target;
}

// 저장된 사이드바 정렬 순서 정보를 가져온다.
function handleSidebars(& $sval, & $obj, $previewMode) {
	global $service, $pluginURL, $pluginPath, $pluginName, $configVal, $configMappings;
	requireModel('blog.sidebar');
	$newSidebarAllOrders = array(); 
	// [sidebar id][element id](type, id, parameters)
	// type : 1=skin text, 2=default handler, 3=plug-in
	// id : type1=sidebar i, type2=handler id, type3=plug-in handler name
	// parameters : type1=sidebar j, blah blah~
	
	$sidebarCount = count($obj->sidebarBasicModules);
	$sidebarAllOrders = getSidebarModuleOrderData($sidebarCount);
	if ($previewMode == true) {
		$sidebarAllOrders = null;
	} else {
		if (is_null($sidebarAllOrders)) $sidebarAllOrders = array();
	}
	
	for ($i=0; $i<$sidebarCount; $i++) {
		$str = "";
		if ((!is_null($sidebarAllOrders)) && ((array_key_exists($i, $sidebarAllOrders)))) {
			$currentSidebarOrder = $sidebarAllOrders[$i];
			for ($j=0; $j<count($currentSidebarOrder); $j++) {
				if ($currentSidebarOrder[$j]['type'] == 1) { // skin text
					$skini = $currentSidebarOrder[$j]['id'];
					$skinj = $currentSidebarOrder[$j]['parameters'];
					if (isset($obj->sidebarBasicModules[$skini]) && isset($obj->sidebarBasicModules[$skini][$skinj])) {
						$str .= $obj->sidebarBasicModules[$skini][$skinj]['body'];
					}
				} else if ($currentSidebarOrder[$j]['type'] == 2) { // default handler
					// TODO : implement
				} else if ($currentSidebarOrder[$j]['type'] == 3) { // plugin
					$plugin = $currentSidebarOrder[$j]['id']['plugin'];
					$handler = $currentSidebarOrder[$j]['id']['handler'];
					include_once (ROOT . "/plugins/{$plugin}/index.php");
					if (function_exists($handler)) {
						$str .= "[##_temp_sidebar_element_{$i}_{$j}_##]";
						$parameter = $currentSidebarOrder[$j]['parameters'];
						$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"]['plugin'] = $plugin;
						$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"]['handler'] = $handler;
						$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"]['parameters'] = $parameter;
					} else {
						$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"] = "";
					}
				} else {
					// WHAT?
				}
			}
		} else {
			$newSidebarAllOrders[$i] = array();
			
			for ($j=0; $j<count($obj->sidebarBasicModules[$i]); $j++) {
				$str .= $obj->sidebarBasicModules[$i][$j]['body'];
				array_push($newSidebarAllOrders[$i], array('type' => '1', 'id' => "$i", 'parameters' => "$j"));
			}
			
			if (!is_null($sidebarAllOrders)) $sidebarAllOrders[$i] = $newSidebarAllOrders[$i];  
		}
		dress("sidebar_{$i}", $str, $sval);
	}
	
	if (count($newSidebarAllOrders) > 0) {
		if (($previewMode == false) && !is_null($sidebarAllOrders)) {
			setBlogSetting("sidebarOrder", serialize($sidebarAllOrders));
			CacheControl::flushSkin();
		}
	}
}

// 저장된 표지 정렬 순서 정보를 가져온다.
function handleCoverpages(& $obj, $previewMode = false) {
	global $service, $pluginURL, $pluginPath, $pluginName, $configVal, $configMappings;
	requireModel("blog.coverpage");
	// [coverpage id][element id](type, id, parameters)
	// type : 3=plug-in
	// id : type1=coverpage i, type2=handler id, type3=plug-in handler name
	// parameters : type1=coverpage j, blah blah~
	
	$coverpageAllOrders = getCoverpageModuleOrderData();
	if ($previewMode == true) $coverpageAllOrders = null;
	
	$i = 0;
	$obj->coverpageModule = array();
	if ((!is_null($coverpageAllOrders)) && ((array_key_exists($i, $coverpageAllOrders)))) {
		$currentCoverpageOrder = $coverpageAllOrders[$i];
		for ($j=0; $j<count($currentCoverpageOrder); $j++) {
			if ($currentCoverpageOrder[$j]['type'] == 3) { // plugin
				$plugin = $currentCoverpageOrder[$j]['id']['plugin'];
				$handler = $currentCoverpageOrder[$j]['id']['handler'];
				include_once (ROOT . "/plugins/{$plugin}/index.php");
				if (function_exists($handler)) {
					$obj->coverpageModule[$j] = "[##_temp_coverpage_element_{$i}_{$j}_##]";
					$parameters = $currentCoverpageOrder[$j]['parameters'];
					$pluginURL = "{$service['path']}/plugins/{$plugin}";
					$pluginPath = ROOT . "/plugins/{$plugin}";
					$pluginName = $plugin;
					if( !empty( $configMappings[$plugin]['config'] ) ) 				
						$configVal = getCurrentSetting($plugin);
					else
						$configVal ='';
					
					if (function_exists($handler)) {
						// Loading locale resource
						$languageDomain = null;
						if(is_dir($pluginPath . '/locale/')) {
							$locale = Locales::getInstance();
							$languageDomain = $locale->domain;
							if(file_exists($pluginPath.'/locale/'.$locale->defaultLanguage.'.php')) {
								$locale->setDirectory($pluginPath.'/locale');
								$locale->set($locale->defaultLanguage, $plugin);
								$locale->domain = $plugin;
							}
						}												
						$obj->coverpageStorage["temp_coverpage_element_{$i}_{$j}"] = call_user_func($handler, $parameters);
						if(!is_null($languageDomain)) $locale->domain = $languageDomain;
						$pluginURL = $pluginPath = $pluginName = "";
					} else {
						$obj->coverpageStorage["temp_coverpage_element_{$i}_{$j}"] = "";
					}
				}
			} else {
				// WHAT?
			}
		}
	}
}

function handleDataSet( $plugin , $DATA ) {
	global $configMappings, $activePlugins, $service, $pluginURL, $pluginPath, $pluginName, $configMapping, $configVal;
	$xmls = new XMLStruct();
	if( ! $xmls->open($DATA) ) {
		unset($xmls);	
		return array('error' => '3' ,'customError' => '' ) ;
	}unset($xmls);	
	if( ! in_array($plugin, $activePlugins) ) 
		return array('error' => '9' , 'customError'=> _f('%1 : 플러그인이 활성화되어 있지 않아 설정을 저장하지 못했습니다.',$plugin));
	$reSetting = true;
	if( !empty( $configMappings[$plugin]['dataValHandler'] ) ) {
		$pluginURL = "{$service['path']}/plugins/{$plugin}";
		$pluginPath = ROOT . "/plugins/{$plugin}";
		$pluginName = $plugin;
		include_once (ROOT . "/plugins/{$plugin}/index.php");
		if( function_exists( $configMappings[$plugin]['dataValHandler'] ) ) {
			if( !empty( $configMappings[$plugin]['config'] ) ) 				
				$configVal = getCurrentSetting($plugin);
			else
				$configVal ='';
			// Loading locale resource
			$languageDomain = null;
			if(is_dir($pluginPath . '/locale/')) {
				$locale = Locales::getInstance();
				$languageDomain = $locale->domain;
				if(file_exists($pluginPath.'/locale/'.$locale->defaultLanguage.'.php')) {
					$locale->setDirectory($pluginPath.'/locale');
					$locale->set($locale->defaultLanguage, $pluginName);
					$locale->domain = $pluginName;
				}
			}
				
			$reSetting = call_user_func( $configMappings[$plugin]['dataValHandler'] , $DATA);
			$pluginURL = $pluginPath = $pluginName = "";
			if(!is_null($languageDomain)) $locale->domain = $languageDomain;	
		}
		if( true !== $reSetting )	
			return array( 'error' => '9', 'customError' => $reSetting)	;
	}
	$result = updatePluginConfig($plugin, $DATA);
	return array('error' => $result , 'customError'=> '' ) ;
}

function fetchConfigVal($DATA) {
	return Setting::fetchConfigVal($DATA);
}

function handleConfig($plugin) {
	global $service , $typeSchema, $pluginURL, $pluginPath, $pluginName, $configMappings, $configVal, $adminSkinSetting;
	$typeSchema = array(
		'text' 
	,	'textarea'
	,	'select'
	,	'checkbox'
	,	'radio'
	);
	$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
	$xmls = new XMLStruct();	
	$CDSPval = '';
	$i=0;
	$dfVal =  Setting::fetchConfigVal(getCurrentSetting($plugin));
	$name = '';
	$clientData ='[';
	
	$title = $plugin;
	
	if ($manifest && $xmls->open($manifest)) {
		$title = $xmls->getValue('/plugin/title[lang()]');
		//설정 핸들러가 존재시 바꿈
		$config = $xmls->selectNode('/plugin/binding/config[lang()]');
		unset( $xmls );
		if( !empty($config['.attributes']['manifestHandler']) ) {
			$handler = $config['.attributes']['manifestHandler'] ;
			$oldconfig = $config;
			$pluginURL = "{$service['path']}/plugins/{$plugin}";
			$pluginPath = ROOT . "/plugins/{$plugin}";
			$pluginName = $plugin;
			include_once (ROOT . "/plugins/$plugin/index.php");
			if (function_exists($handler)) {
				if( !empty( $configMappings[$plugin]['config'] ) ) 				
					$configVal = getCurrentSetting($plugin);
				else
					$configVal ='';
					
					
				$manifest = call_user_func( $handler , $plugin );
				if(!is_null($languageDomain)) $locale->domain = $languageDomain;		
			}
			$newXmls = new XMLStruct();
			if($newXmls->open( $manifest) ) {	 
				unset( $config );
				$config = $newXmls->selectNode('/config[lang()]');
			}
			unset( $newXmls);
		}
		if( is_null( $config['fieldset'] ) ) 
			return array( 'code' => _t('설정 값이 없습니다.') , 'script' => '[]' ) ;  	
		foreach ($config['fieldset'] as $fieldset) {
			$legend = !empty($fieldset['.attributes']['legend']) ? htmlspecialchars($fieldset['.attributes']['legend']) :'';
			$CDSPval .= CRLF.TAB."<fieldset>".CRLF.TAB.TAB."<legend><span class=\"text\">$legend</span></legend>".CRLF;
			if( !empty( $fieldset['field'] ) ) {
				foreach( $fieldset['field'] as $field ) {
					if( empty( $field['.attributes']['name'] ) ) continue;
					$name = $field['.attributes']['name'] ;
					$clientData .= getFieldName($field , $name) ;
					$CDSPval .=  TreatType( $field , $dfVal ,  $name) ;	
				}
			}
			$CDSPval .= TAB."</fieldset>".CRLF;
		}
	} else	$CDSPval = _t('설정 값이 없습니다.'); 	
	$clientData .= ']';
	return array( 'code' => $CDSPval , 'script' => $clientData, 'title' => $title ) ;
}

/***** Plugin configuration panel functions *****/

function getFieldName( $field , $name ) {
	if( 'checkbox' != $field['.attributes' ]['type'] ) return '"' . $name . '",';
	$tname ='';
	foreach( $field['op'] as $op ) {
		if( !empty( $op['.attributes']['name'] ) )
			$tname .= '"' . $op['.attributes']['name'] . '",';
	}
	return $tname;
}

function TreatType(  $cmd , $dfVal , $name ) {
	global $typeSchema;
	if( empty($cmd['.attributes']['type']) || !in_array($cmd['.attributes']['type'] , $typeSchema  ) ) return '';
	if( empty($cmd['.attributes']['title']) || empty($cmd['.attributes']['name'])) return '';
	$titleFw = empty($cmd['.attributes']['titledirection']) ? true : ($cmd['.attributes']['titledirection'] == 'bk' ? false : true);
	$fieldTitle = TAB.TAB.TAB.'<label class="fieldtitle" for="'.$name.'">'.htmlspecialchars($cmd['.attributes']['title']) . '</label>';
	$fieldControl = TAB.TAB.TAB.'<span class="fieldcontrol">' .CRLF.  call_user_func($cmd['.attributes']['type'].'Treat' , $cmd, $dfVal, $name) .TAB.TAB.TAB.'</span>';
	$caption = empty($cmd['caption'][0]) ? '':TAB.TAB.TAB.'<div class="fieldcaption">'. $cmd['caption'][0]['.value']  . '</div>'.CRLF;
	if( $titleFw) 
		return	TAB.TAB.'<div class="field" id="div_'.htmlspecialchars($cmd['.attributes']['name']).'">'.CRLF.$fieldTitle.CRLF.$fieldControl.CRLF.$caption.TAB.TAB."</div>\n";
	else
		return	TAB.TAB.'<div class="field" id="div_'.htmlspecialchars($cmd['.attributes']['name']).'">'.CRLF.$fieldControl.CRLF.$fieldTitle.CRLF.$caption.TAB.TAB."</div>\n";
}

function treatDefaultValue($value, $default, $allowZero = true) {
	if ($allowZero)
		return (!isset($value) || trim($value) == '' ? $default : $value);
	else
		return (empty($value) ? $default : $value);
}

function textTreat( $cmd , $dfVal , $name ) {
	$dfVal = ( !is_null( $dfVal[$name]  ) ) ? $dfVal[$name]  :  ((!isset($cmd['.attributes']['value']) || (empty($cmd['.attributes']['value']) && $cmd['.attributes']['value']!== 0))?null:$cmd['.attributes']['value']); 
	$DSP = TAB.TAB.TAB.TAB.'<input type="text" class="textcontrol" ';
	$DSP .= ' id="'.$name.'" ';
	$DSP .= empty( $cmd['.attributes']['size'] ) ? '' : 'size="'. $cmd['.attributes']['size'] . '"' ;
	$DSP .= is_null( $dfVal  ) ? '' : 'value="'. htmlspecialchars($dfVal). '"' ;
	$DSP .= ' />'.CRLF ;
	return $DSP;
}
function textareaTreat( $cmd, $dfVal , $name) {
	$dfVal = ( !is_null( $dfVal[$name]  ) ) ? $dfVal[$name] : ((!isset($cmd['.value']) || (empty($cmd['.value']) && $cmd['.value']!== 0))? null:$cmd['.value']);
	$DSP = TAB.TAB.TAB.TAB.'<textarea class="textareacontrol"';
	$DSP .= ' id="'.$name.'"';
	$DSP .= ' rows="'.((!isset($cmd['.attributes']['rows']) || (empty($cmd['.attributes']['rows']) && $cmd['.attributes']['rows']!== 0)) ? '2' : $cmd['.attributes']['rows']).'"';
	$DSP .= ' cols="'.((!isset($cmd['.attributes']['cols']) || (empty($cmd['.attributes']['cols']) && $cmd['.attributes']['cols']!== 0)) ? '23' : $cmd['.attributes']['cols']).'"';
	$DSP .= '>';
	$DSP .= is_null( $dfVal  )  ? '' : htmlspecialchars($dfVal);
	$DSP .= '</textarea>'.CRLF ;
	return $DSP;
}

function selectTreat( $cmd, $dfVal , $name) {
	$DSP = TAB.TAB.TAB.TAB.'<select id="'.$name.'" class="selectcontrol">'.CRLF;
	$df = ((!isset($dfVal[$name]) || (empty($dfVal[$name]) && $dfVal[$name]!== 0))?null:$dfVal[$name]);
	foreach( $cmd['op']  as $option ) {
		$ov = ((!isset($option['.attributes']['value']) || (empty($option['.attributes']['value']) && $option['.attributes']['value']!== 0))?null:$option['.attributes']['value']);
		$oc = ((!isset($option['.attributes']['checked']) || (empty($option['.attributes']['checked']) && $option['.attributes']['checked']!== 0))?null:$option['.attributes']['checked']);
		
		$DSP .= TAB.TAB.TAB.TAB.TAB.'<option ';
		$DSP .= !is_string( $ov ) ? '' : 'value="'.htmlspecialchars($ov).'" ';
		$DSP .= is_string( $oc ) && 'checked' == $oc && is_null($dfVal) ? 'selected="selected" ':'';
		$DSP .= is_string($df) && (!is_string( $ov ) ? false : $ov== $df ) ? 'selected="selected" ' : '';
		$DSP .= '>';
		$DSP .= $option['.value'];
		$DSP .= '</option>'.CRLF;
	}
	$DSP .= TAB.TAB.TAB.TAB.'</select>'.CRLF ;
	return $DSP;
}

function checkboxTreat( $cmd, $dfVal, $name) {
	$DSP = '';	
	foreach( $cmd['op']  as $option ) {
		if( empty($option['.attributes']['name']) || !is_string( $option['.attributes']['name'] ) ) continue;
		$df = ((!isset($dfVal[$option['.attributes']['name']]) || (empty($dfVal[$option['.attributes']['name']]) && $dfVal[$option['.attributes']['name']]!== 0))?null:$dfVal[$option['.attributes']['name']]);
		$oc = ((!isset($option['.attributes']['checked']) || (empty($option['.attributes']['checked']) && $option['.attributes']['checked']!== 0))?null:$option['.attributes']['checked']);		
		
		$checked = !is_string( $df ) ? 
				( is_string( $oc ) && 'checked' == $oc && is_null( $dfVal ) ? 'checked="checked" ' : ''   ) :
				( '' != $df ? 'checked="checked" ' : '');
		$DSP .= TAB.TAB.TAB.TAB.'<input type="checkbox" class="checkboxcontrol"';
		$DSP .= ' id="'.$option['.attributes']['name'].'" ';
		$DSP .= !is_string( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= $checked;
		$DSP .= ' />' ;
		$DSP .= "<label class='checkboxlabel' for=\"".$option['.attributes']['name']."\">{$option['.value']}</label>".CRLF;
	}
	return $DSP;
}

function radioTreat( $cmd, $dfVal, $name) {
	$DSP = '';
	$cnt = 0;
	$df = ((!isset($dfVal[$name]) || (empty($dfVal[$name]) && $dfVal[$name]!== 0))?null:$dfVal[$name]);
	foreach( $cmd['op']  as $option ) {
		$cnt++;
		$DSP .= TAB.TAB.TAB.TAB.'<input type="radio"  class="radiocontrol" ';
		$DSP .= ' name="'.$name.'" id="'.$name.$cnt.'" ';
		$oc = ((!isset($option['.attributes']['checked']) || (empty($option['.attributes']['checked']) && $option['.attributes']['checked']!== 0))?null:$option['.attributes']['checked']);
		$DSP .= !is_string( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= is_string( $oc ) && 'checked' == $oc && is_null($dfVal) ? 'checked="checked" ' : '';
		$DSP .= is_string($df) && (!is_string( $option['.attributes']['value'] ) ? false : $option['.attributes']['value']== $df ) ? 'checked="checked" ' : '';
		$DSP .= ' />' ;
		$DSP .= "<label class='radiolabel' for='".$name.$cnt."'>{$option['.value']}</label >".CRLF;
	}
	return $DSP;
}
?>
