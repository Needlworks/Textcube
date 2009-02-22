<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// legacy functions to support character escaping depended on DBMS
function escapeSearchString($str) {
	return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', POD::escapeString($str, null))) : $str;
}

function doesExistTable($tablename) {
	requireModel('common.setting');

	global $database;
	static $tables = array();
	if( empty($tables) ) {
		$escapename = escapeSearchString($database['prefix']);
		$tables = POD::queryColumn( "SHOW TABLES LIKE '{$escapename}%'" );
	}
	
	$dbCaseInsensitive = getServiceSetting('lowercaseTableNames');
	if($dbCaseInsensitive === null) {
		$result = POD::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
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
