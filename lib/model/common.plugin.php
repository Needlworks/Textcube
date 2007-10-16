<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $pluginSetting;
$pluginSetting = array();
function clearPluginSettingCache()
{
	global $pluginSetting;
	if( !empty($pluginSetting) ) {
		$pluginSetting = array();
	}
}

function activatePlugin($name) {
	global $database, $activePlugins;
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
		
		if (!is_null($requiredTattertoolsVersion) && !is_null($requiredTextcubeVersion)) {
			if ($currentTextcubeVersion < $requiredTattertoolsVersion && $currentTextcubeVersion < $requiredTextcubeVersion)
				return false;
		} else if (!is_null($requiredTattertoolsVersion) && is_null($requiredTextcubeVersion)) {
			if ($currentTextcubeVersion < $requiredTattertoolsVersion)
				return false;
		} else if (is_null($requiredTattertoolsVersion) && !is_null($requiredTextcubeVersion)) {
			if ($currentTextcubeVersion < $requiredTextcubeVersion)
				return false;
		}
	} else {
		return false;
	}
	$pluginName = $name;
	$name = tc_escape_string(UTF8::lessenAsEncoding($name, 255));
	DBQuery::query("INSERT INTO {$database['prefix']}Plugins VALUES (".getBlogId().", '$name', null)");
	$result = mysql_affected_rows();
	clearPluginSettingCache();
	CacheControl::flushItemsByPlugin($pluginName);
	return ($result == 1);
}

function deactivatePlugin($name) {
	global $database, $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$pluginName = $name;
	$name = tc_escape_string($name);
	DBQuery::query("DELETE FROM {$database['prefix']}Plugins 
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
		$settings = DBQuery::queryAllWithCache("SELECT name, settings 
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
function updatePluginConfig( $name , $setVal) {
	global $database,  $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$pluginName = $name;
	$name = tc_escape_string( UTF8::lessenAsEncoding($name, 255) ) ;
	$setVal = tc_escape_string( $setVal ) ;
	DBQuery::query(
		"UPDATE {$database['prefix']}Plugins 
			SET settings = '$setVal' 
			WHERE blogid = ".getBlogId()."
			AND name = '$name'"
		);
	if( mysql_affected_rows() == 1 )
		$result = '0';
	clearPluginSettingCache();
	CacheControl::flushItemsByPlugin($pluginName);
	if(isset($result) && $result = '0') return $result;
	return (mysql_error() == '') ? '0' : '1';
}

function getPluginInformation($plugin) {
	$xmls = new XMLStruct();
	// Error checking routine
	if (!preg_match('@^[A-Za-z0-9 _-]+$@', $plugin))
		return false;
	if (!file_exists(ROOT . "/plugins/$plugin/index.xml"))
		return false;
	if (!$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml"))) {
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
			$keyname = tc_escape_string(UTF8::lessenAsEncoding($keyname, 32));
			$value = tc_escape_string(UTF8::lessenAsEncoding($plugin . '/' . $version , 255));
			DBQuery::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
		} else {
			$keyname = tc_escape_string(UTF8::lessenAsEncoding($keyname, 32));
			$value = tc_escape_string(UTF8::lessenAsEncoding($plugin . '/' . $version , 255));
			$values = explode('/', $result, 2);
			if (strcmp($plugin, $values[0]) != 0) { // diff plugin
				return false; // nothing can be done
			} else if (strcmp($version, $values[1]) != 0) {
				DBQuery::execute("UPDATE {$database['prefix']}ServiceSettings SET value ='$value' WHERE name='$keyname'");
				$eventName = 'UpdateDB_' . $name;
				fireEvent($eventName, $values[1]);
			}
		}
		return true;
	} else {
		$query = "CREATE TABLE {$database['prefix']}{$name} (blogid int(11) NOT NULL default '0',";
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
			$defaultValue = is_null($field['default']) ? '' : " DEFAULT '" . tc_escape_string($field['default']) . "' ";
			$fieldLength = ($field['length'] >= 0) ? "(".$field['length'].")" : '';
			$sentence = $field['name'] . " " . $field['attribute'] . $fieldLength . $isNull . $defaultValue . $ai . ",";
			$query .= $sentence;
		}
		
		array_unshift($keys, 'blogid');
		$query .= " PRIMARY KEY (" . implode(',',$keys) . ")";
		$query .= $index;
		$query .= ") TYPE=MyISAM ";
		$query .= ($database['utf8'] == true) ? 'DEFAULT CHARSET=utf8' : '';
		if (DBQuery::execute($query)) {
				$keyname = tc_escape_string(UTF8::lessenAsEncoding('Database_' . $name, 32));
				$value = tc_escape_string(UTF8::lessenAsEncoding($plugin . '/' . $version , 255));
				DBQuery::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
			return true;
		}
		else return false;
		
	}
	return true;
}

function clearPluginTable($name) {
	global $database;
	$name = tc_escape_string($name);
	DBQuery::query("DELETE FROM {$database['prefix']}{$name} WHERE blogid = ".getBlogId());
	return (mysql_affected_rows() == 1);
}

function deletePluginTable($name) {
	global $database;
	if(getBlogId() !== 0) return false;
	$name = tc_escape_string($name);
	DBQuery::query("DROP {$database['prefix']}{$name}");
	return true;
}

function getPluginTableName() {
	requireModel('common.setting');

	global $database;
	
	$likeEscape = array ( '/_/' , '/%/' );
	$likeReplace = array ( '\\_' , '\\%' );
	$escapename = preg_replace($likeEscape, $likeReplace, $database['prefix']);
	$query = "SHOW TABLES LIKE '{$escapename}%'";
	$dbtables = DBQuery::queryColumn($query);

	$dbCaseInsensitive = getServiceSetting('lowercaseTableNames');
	if($dbCaseInsensitive == null) {
		$result = DBQuery::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
		$dbCaseInsensitive = ($result['Value'] == 1) ? true : false;
		setServiceSetting('lowercaseTableNames',$dbCaseInsensitive);
	}

	$definedTables = getDefinedTableNames();

	$dbtables = array_values(array_diff($dbtables, $definedTables));
	if ($dbCaseInsensitive == true) {
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
?>
