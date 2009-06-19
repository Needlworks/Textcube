<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'REQUEST' => array(
		'name' => array('string'),
		'type' => array('int', 'min' => 1, 'max' => 2),
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if ($blogid != 1) {
	Utils_Respond::ResultPage(1);
	exit;
}

requireModel('common.setting');
$definedTables = getDefinedTableNames();

if ($_REQUEST['type'] == 1) { // type 1, plugin

	$values = explode('/', $_REQUEST['name'], 2);
	$pluginname = $values[0];
	$version = $values[1];

	$query = "select name from {$database['prefix']}ServiceSettings WHERE value = '" . Data_IAdapter::escapeString($_REQUEST['name']) . "'";
	
	$plugintablesraw = Data_IAdapter::queryColumn($query);
	if (count($plugintablesraw) <= 0) {
		Utils_Respond::ResultPage(1);
		exit;
	}
	$plugintables = array();
	foreach($plugintablesraw as $dbname) {
		$origname = $dbname;
		$dbname = $database['prefix'] . substr($dbname, 9);
		if (!Data_IAdapter::doesExistTable($dbname)) {
			Utils_Respond::ResultPage(1);
			exit;
		}
		if (in_array($dbname , $definedTables)) {
			Utils_Respond::ResultPage(1);
			exit;
		}
		array_push($plugintables, $dbname);
		$query = "delete from {$database['prefix']}ServiceSettings WHERE name = '$origname' AND value = '" . Data_IAdapter::escapeString($_REQUEST['name']) . "'";
		Data_IAdapter::execute($query);
	}
	$result = true;
	foreach($plugintables as $dbname) {
		$query = "DROP TABLE {$dbname}";
		$result = Data_IAdapter::execute($query) && $result;
	}
	
	deactivatePlugin($pluginname);
	
	if ($result == false) {
		Utils_Respond::ResultPage(1);
		exit;
	}
} else { // type 2, table

	$dbname = $_REQUEST['name'];
	if (strncmp($dbname, $database['prefix'], strlen($database['prefix'])) != 0) {
		Utils_Respond::ResultPage(1);
		exit;
	}
	if (in_array($dbname , $definedTables)) {
		Utils_Respond::ResultPage(1);
		exit;
	}
	if (!Data_IAdapter::doesExistTable($dbname)) {
		Utils_Respond::ResultPage(1);
		exit;
	}
	
	$query = "DROP TABLE {$dbname}";
	Data_IAdapter::execute($query);
}

Utils_Respond::ResultPage(0);
?>
