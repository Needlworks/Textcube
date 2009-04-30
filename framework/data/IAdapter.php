<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

interface IAdapter {
	/** Connection */
	public static function connect($server, $userid, $password, $dbname = null, $options = null); 
	public static function disconnect();
	public static function select_db($dbname);
	/** Information */
	public static function charset();
	public static function dbms();
	public static function version();
	/** Basic queries */
	public static function queryExistence($query);
	public static function queryCount($query); 
	public static function queryCell($query, $field = 0, $useCache=true); 
	public static function queryRow($query, $type = 'both', $useCache=true);
	public static function queryColumn($query, $useCache=true); 
	public static function queryAll ($query, $type = 'both', $count = -1); 
	public static function queryAllWithoutCache($query, $type = 'both', $count = -1); 
	public static function queryAllWithCache($query, $type = 'both', $count = -1); 
	public static function execute($query); 
	public static function multiQuery();
	public static function query($query);
	/** Transaction */
	/** String manipulation */
	public static function escapeString($string, $link = null);
	/** Instant cache functions */
	public static function clearCache(); 
	public static function cacheLoad();
	public static function cacheSave();
	/** Raw functions (to easier adoptation from traditional queries) */
	public static function insertId();
	public static function num_rows($handle = null);
	public static function free($handle = null);
	public static function fetch($handle = null, $type = 'assoc');
	public static function error($err = null);
	public static function stat($stat = null);
}

/** Load DBMS Adapter */
$config = Model_Config::getInstance();
require_once ROOT.'/framework/data/'.$config->backend_name.'/Adapter.php';

/** Debug module */
if($config->service['debugmode'] == true) {
	require_once ROOT.'/framework/data/'.$config->backend_name.'/Debug.php';
}

/** Extend DBMS Adapter with DBMS-independent methods */
class Data_IAdapter extends DBAdapter {
	public static function queryWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new Cache_Query($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = self::query($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryAllWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new Cache_Query($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = self::queryAllWithCache($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryRowWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new Cache_Query($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = self::queryRow($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryColumnWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new Cache_Query($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = self::queryColumn($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	/** Miscellany functions */
	public static function escapeSearchString($str) {
		return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', self::escapeString($str, null))) : $str;
	}
	
	public static function doesExistTable($tablename) {
		requireModel('common.setting');
	
		global $database;
		static $tables = array();
		if( empty($tables) ) {
			$escapename = self::escapeSearchString($database['prefix']);
			$tables = Data_IAdapter::queryColumnWithDBCache( "SHOW TABLES LIKE '{$escapename}%'" );
		}
		
		$dbCaseInsensitive = getServiceSetting('lowercaseTableNames');
		if($dbCaseInsensitive === null) {
			$result = self::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
			$dbCaseInsensitive = ($result['Value'] == 1) ? 1 : 0;
			setServiceSetting('lowercaseTableNames',$dbCaseInsensitive);
		}
		if($dbCaseInsensitive == 1) $tablename = strtolower($tablename);
		if( in_array( $tablename, $tables ) ) {
			return true;
		}
		return false;
	}	
}
?>
