<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function activatePlugin($name) {
	global $database, $activePlugins;
	if (in_array($name, $activePlugins))
		return true;
	if (!ereg("^[[:alnum:] _\-]+$", $name))
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
	$name = mysql_tt_escape_string(mysql_lessen($name, 255));
	DBQuery::query("INSERT INTO {$database['prefix']}Plugins VALUES (".getBlogId().", '$name', null)");
	return (mysql_affected_rows() == 1);
}

function deactivatePlugin($name) {
	global $database, $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$name = mysql_tt_escape_string($name);
	DBQuery::query("DELETE FROM {$database['prefix']}Plugins 
			WHERE blogid = ".getBlogId()."
				AND name = '$name'");
	return true;
}

function getCurrentSetting($name){
	global $database, $activePlugins;
	if( !in_array( $name , $activePlugins))
		return false;
	static $pluginSettingCheck = array();
	static $pluginSettingValue = array();
	if( isset( $pluginSettingCheck[$name] ) ) {
		return $pluginSettingValue[$name];
	}

	$name = mysql_tt_escape_string( $name ) ;
	$result = DBQuery::query("SELECT settings 
			FROM {$database['prefix']}Plugins 
			WHERE blogid = ".getBlogId()."
				AND name = '$name'");
	if( false === $result ) 
		return false;
	$out = mysql_fetch_array($result); 
	$pluginSettingCheck[$name] = true;
	$pluginSettingValue[$name] = $out['settings'];
	return $out['settings'];
}
function updatePluginConfig( $name , $setVal){
	global $database,  $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$name = mysql_tt_escape_string( mysql_lessen($name, 255) ) ;
	$setVal = mysql_tt_escape_string( $setVal ) ;
	DBQuery::query(
		"UPDATE {$database['prefix']}Plugins 
			SET settings = '$setVal' 
			WHERE blogid = ".getBlogId()."
			AND name = '$name'"
		);
	if( mysql_affected_rows() == 1 )
		return '0';
	return (mysql_error() == '') ? '0' : '1';
}
function treatPluginTable($plugin, $name, $fields, $keys, $version){
	global $database;
	if(doesExistTable($database['prefix'] . $name)){
		$keyname = 'Database_' . $name;
		$value = $plugin;		
		$query = "SELECT value FROM {$database['prefix']}ServiceSettings WHERE name='{$keyname}'";
		$result = DBQuery::queryCell($query);
		if (is_null($result)) {
			$keyname = mysql_tt_escape_string(mysql_lessen($keyname, 32));
			$value = mysql_tt_escape_string(mysql_lessen($plugin . '/' . $version , 255));
			DBQuery::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
		} else {
			$keyname = mysql_tt_escape_string(mysql_lessen($keyname, 32));
			$value = mysql_tt_escape_string(mysql_lessen($plugin . '/' . $version , 255));
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
			if( strtolower($field['attribute']) == 'int' || strtolower($field['attribute']) == 'mediumint'  ){
				if($field['autoincrement'] == 1 && !$isaiExists){
					$ai = ' AUTO_INCREMENT ';
					$isaiExists = true;
					if(!in_array($field['name'], $keys))
						$index = ", KEY({$field['name']})";
				}
			}
			$isNull = ($field['isnull'] == 0) ? ' NOT NULL ' : ' NULL ';
			$defaultValue = is_null($field['default']) ? '' : " DEFAULT '" . mysql_tt_escape_string($field['default']) . "' ";
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
				$keyname = mysql_tt_escape_string(mysql_lessen('Database_' . $name, 32));
				$value = mysql_tt_escape_string(mysql_lessen($plugin . '/' . $version , 255));
				DBQuery::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
			return true;
		}
		else return false;
		
	}
	return true;
}

function clearPluginTable($name) {
	global $database;
	$name = mysql_tt_escape_string($name);
	DBQuery::query("DELETE FROM {$database['prefix']}{$name} WHERE blogid = ".getBlogId());
	return (mysql_affected_rows() == 1);
}

function deletePluginTable($name) {
	global $database;
	if(getBlogId() !== 0) return false;
	$name = mysql_tt_escape_string($name);
	DBQuery::query("DROP {$database['prefix']}{$name}");
	return true;
}

function getPluginTableName(){
	global $database;
	requireModel('common.setting');

	$likeEscape = array ( '/_/' , '/%/' );
	$likeReplace = array ( '\\_' , '\\%' );
	$escapename = preg_replace($likeEscape, $likeReplace, $database['prefix']);
	$query = "show tables like '{$escapename}%'";
	$dbtables = DBQuery::queryColumn($query);

	$result = DBQuery::queryRow("show variables like 'lower_case_table_names'");
	$dbCaseInsensitive = ($result['Value'] == 1) ? true : false;

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
