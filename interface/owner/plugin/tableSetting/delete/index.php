<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'REQUEST' => array(
		'name' => array('string'),
		'type' => array('int', 'min' => 1, 'max' => 2),
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if ($blogid != 1) {
	Respond::ResultPage(1);
	exit;
}

requireModel('common.setting');
$definedTables = getDefinedTableNames();

if ($_REQUEST['type'] == 1) { // type 1, plugin

	$values = explode('/', $_REQUEST['name'], 2);
	$pluginname = $values[0];
	$version = $values[1];

	$query = "select name from {$database['prefix']}ServiceSettings WHERE value = '" . POD::escapeString($_REQUEST['name']) . "'";
	
	$plugintablesraw = POD::queryColumn($query);
	if (count($plugintablesraw) <= 0) {
		Respond::ResultPage(1);
		exit;
	}
	$plugintables = array();
	foreach($plugintablesraw as $dbname) {
		$origname = $dbname;
		$dbname = $database['prefix'] . substr($dbname, 9);
		if (!doesExistTable($dbname)) {
			Respond::ResultPage(1);
			exit;
		}
		if (in_array($dbname , $definedTables)) {
			Respond::ResultPage(1);
			exit;
		}
		array_push($plugintables, $dbname);
		$query = "delete from {$database['prefix']}ServiceSettings WHERE name = '$origname' AND value = '" . POD::escapeString($_REQUEST['name']) . "'";
		POD::execute($query);
	}
	$result = true;
	foreach($plugintables as $dbname) {
		$query = "DROP TABLE {$dbname}";
		$result = POD::execute($query) && $result;
	}
	
	deactivatePlugin($pluginname);
	
	if ($result == false) {
		Respond::ResultPage(1);
		exit;
	}
} else { // type 2, table

	$dbname = $_REQUEST['name'];
	if (strncmp($dbname, $database['prefix'], strlen($database['prefix'])) != 0) {
		Respond::ResultPage(1);
		exit;
	}
	if (in_array($dbname , $definedTables)) {
		Respond::ResultPage(1);
		exit;
	}
	if (!doesExistTable($dbname)) {
		Respond::ResultPage(1);
		exit;
	}
	
	$query = "DROP TABLE {$dbname}";
	POD::execute($query);
}

Respond::ResultPage(0);
?>
