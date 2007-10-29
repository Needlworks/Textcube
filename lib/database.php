<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $service;
if (!isset($service['dbms'])) {
	$service['dbms'] = 'mysql';
}
//Database Binding
switch($service['dbms']) {
	case 'postgresql':
		require_once ROOT.'/components/Needlworks.DBMS.PostgreSQL.php';
		break;
	case 'mysql':
	default:
		require_once ROOT.'/components/Needlworks.DBMS.MySQL.php';
}

DBQuery::bind($database);

// legacy.
function tc_escape_string($string, $link = null) {
	return DBQuery::escapeString($string, $link);
}

function escapeSearchString($str) {
	return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', tc_escape_string($str))) : $str;
}

function doesExistTable($tablename)
{
	requireModel('common.setting');

	global $database;
	static $tables = array();
	if( empty($tables) ) {
		$escapename = escapeSearchString($database['prefix']);
		$tables = DBQuery::queryColumn( "SHOW TABLES LIKE '{$escapename}%'" );
	}
	
	$dbCaseInsensitive = getServiceSetting('lowercaseTableNames');
	if($dbCaseInsensitive == null) {
		$result = DBQuery::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
		$dbCaseInsensitive = ($result['Value'] == 1) ? 1 : 0;
		setServiceSetting('lowercaseTableNames',$dbCaseInsensitive);
	}
	if($dbCaseInsensitive == 1) $tablename = strtolower($tablename);
	if( in_array( $tablename, $tables ) ) {
		return true;
	}
	return false;
}
?>
