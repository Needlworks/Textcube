<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DBQuery version 1.8 for MySQL

global $fileCachedResult;

class DBAdapter implements IAdapter {
	static $db;
	static $cachedResult, $dbProperties, $escapeTag, $lastQueryType;
		
	/*@static@*/
	public static function bind($database) {
		self::$cachedResult = self::$dbProperties = array();
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		if(!isset($database) || empty($database)) return false;
		self::$db = @mysql_connect($database['server'].(isset($database['port']) ? ':'.$database['port'] : ''), $database['username'], $database['password']);
		if(!self::$db) return false;
		self::$db = @mysql_select_db($database['database']);
		if(!self::$db) return false;

		if (self::query('SET CHARACTER SET utf8'))
			self::$dbProperties['charset'] = 'utf8';
		else
			self::$dbProperties['charset'] = 'default';
		@self::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		return true;
	}
	
	public static function unbind() {
		mysql_close();
		return true;
	}

	public static function charset() {
		if (array_key_exists('charset', self::$dbProperties)) return self::$dbProperties['charset'];
		else return null;
	}
	public static function dbms() {
		return 'MySQL';
	}

	public static function version($mode = 'server') {
		if (array_key_exists('version', self::$dbProperties)) return self::$dbProperties['version'];
		else {
			self::$dbProperties['version'] = self::queryCell("SHOW VARIABLES LIKE 'version'");
			return self::$dbProperties['version'];
		}
	}
	
	public static function tableList($condition = null) {
		if (!array_key_exists('tableList', self::$dbProperties)) { 
			$tableData = self::queryAll('SHOW TABLES');
			self::$dbProperties['tableList'] = array();
			foreach($tableData as $tbl) {
				array_push(self::$dbProperties['tableList'], $tbl[0]);
			}
		}
		$result = array();
		if(!is_null($condition)) {
			$result = array();
			foreach(self::$dbProperties['tableList'] as $item) {
				if(strpos($item, $condition) === 0) {
					array_push($result, $item);
				}
			}
			return $result;
		} else {
			return self::$dbProperties['tableList'];
		}
	}
	
	public static function setTimezone($time) {
		return self::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');
	}
	public static function reservedFieldNames() {
		return null;
	}
	public static function reservedFunctionNames() {
		return array('UNIX_TIMESTAMP()');
	}
	/*@static@*/
	public static function queryExistence($query) {
		if ($result = self::query($query)) {
			if (mysql_num_rows($result) > 0) {
				mysql_free_result($result);
				return true;
			}
			mysql_free_result($result);
		}
		return false;
	}
	
	/*@static@*/
	public static function queryCount($query) {
		$count = 0;
		$query = trim($query);
		if ($result = self::query($query)) {
			$operation = strtolower(substr($query, 0,6));
			self::$lastQueryType = $operation;
			switch ($operation) {
				case 'select':
					$count = mysql_num_rows($result);
					mysql_free_result($result);
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = mysql_affected_rows();
					//mysql_free_result();
					break;
			}
		}
		return $count;
	}

	/*@static@*/
	public static function queryCell($query, $field = 0, $useCache=true) {
		$type = 'both';
		if (is_numeric($field)) {
			$type = 'num';
		} else {
			$type = 'assoc';
		}

		if( $useCache ) {
			$result = self::queryAllWithCache($query, $type);
		} else {
			$result = self::queryAllWithoutCache($query, $type);
		}
		if( empty($result) ) {
			return null;
		}
		return $result[0][$field];
	}
	
	/*@static@*/
	public static function queryRow($query, $type = 'both', $useCache=true) {
		if( $useCache ) {
			$result = self::queryAllWithCache($query, $type, 1);
		} else {
			$result = self::queryAllWithoutCache($query, $type, 1);
		}
		if( empty($result) ) {
			return null;
		}
		return $result[0];
	}
	
	/*@static@*/
	public static function queryColumn($query, $useCache=true) {
		$cacheKey = "{$query}_queryColumn";
		if( $useCache && isset( self::$cachedResult[$cacheKey] ) ) {
			if(function_exists( '__tcSqlLogBegin' ) ) {
				__tcSqlLogBegin($query);
				__tcSqlLogEnd(null,1);
			}
			self::$cachedResult[$cacheKey][0]++;
			return self::$cachedResult[$cacheKey][1];
		}

		$column = null;
		if ($result = self::query($query)) {
			$column = array();
			while ($row = mysql_fetch_row($result))
				array_push($column, $row[0]);
			mysql_free_result($result);
		}

		if( $useCache ) {
			self::$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	public static function queryAll ($query, $type = 'both', $count = -1) {
		return self::queryAllWithCache($query, $type, $count);
		//return self::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	public static function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = self::__queryType($type);
		if ($result = self::query($query)) {
			if (is_resource($result)) {
				while ( ($count-- !=0) && $row = mysql_fetch_array($result, $realtype))
					array_push($all, $row);
				mysql_free_result($result);
				return $all;
			} else {
				return $result;
			}
		}
		return null;
	}
	
	public static function queryAllWithCache($query, $type = 'both', $count = -1) {
		$cacheKey = "{$query}_{$type}_{$count}";
		if( isset( self::$cachedResult[$cacheKey] ) ) {
			if( function_exists( '__tcSqlLogBegin' ) ) {
				__tcSqlLogBegin($query);
				__tcSqlLogEnd(null,1);
			}
			self::$cachedResult[$cacheKey][0]++;
			return self::$cachedResult[$cacheKey][1];
		}
		$all = self::queryAllWithoutCache($query,$type,$count);
		self::$cachedResult[$cacheKey] = array( 1, $all );
		return $all;
	}
	
	/*@static@*/
	public static function execute($query) {
		return self::query($query) ? true : false;
	}

	/*@static@*/
	public static function multiQuery() {
		$result = false;
		foreach (func_get_args() as $query) {
			if (is_array($query)) {
				foreach ($query as $subquery)
					if (($result = self::query($subquery)) === false)
						return false;
			} else if (($result = self::query($query)) === false)
				return false;
		}
		return $result;
	}

	/*@static@*/
	public static function query($query) {
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = mysql_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = mysql_query($query);
		}
		self::$lastQueryType = strtolower(substr($query, 0,6));
		if( stristr($query, 'update ') ||
			stristr($query, 'insert ') ||
			stristr($query, 'delete ') ||
			stristr($query, 'replace ') ) {
			self::clearCache();
		}
		return $result;
	}
	
	public static function insertId() {
		return mysql_insert_id();
	}
	
	public static function escapeString($string, $link = null){
		if(is_null(self::$escapeTag)) {
			if ( function_exists('mysql_real_escape_string') && (mysql_real_escape_string('ㅋ') == 'ㅋ')) {
				self::$escapeTag = 'real';
			} else {
				self::$escapeTag = 'none';
			}
		}
		if(self::$escapeTag == 'real') {
			return is_null($link) ? mysql_real_escape_string($string) : mysql_real_escape_string($string, $link);
		} else {
			return mysql_escape_string($string);
		}
	}
	
	public static function clearCache() {
		self::$cachedResult = array();
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin("Cache cleared");
			__tcSqlLogEnd(null,2);
		}
	}

	public static function cacheLoad() {
		global $fileCachedResult;
	}
	public static function cacheSave() {
		global $fileCachedResult;
	}
	public static function commit() { 
		return true; // Auto commit.
	}
	/* Raw public static functions (to easier adoptation) */
	/*@static@*/
	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return mysql_num_rows($handle);
				break;
			default:
				return mysql_affected_rows($handle);
				break;
		}
		return null;
	}
	/*@static@*/
	public static function free($handle = null) {
		mysql_free_result($handle);
	}
	
	/*@static@*/
	public static function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return mysql_fetch_array($handle); // Can I use mysql_fetch_row instead?
		else if ($type == 'row') return mysql_fetch_row($handle);
		else return mysql_fetch_assoc($handle);
	}
	
	/*@static@*/
	public static function error($err = null) {
		if($err === null) return mysql_error();
		else return mysql_error($err);
	}
	
	/*@static@*/
	public static function stat($stat = null) {
		if($stat === null) return mysql_stat();
		else return mysql_stat($stat);
	}
	
	/*@static@*/
	public static function __queryType($type) {
		switch(strtolower($type)) {
			case 'num':
				return MYSQL_NUM;
			case 'assoc':
				return MYSQL_ASSOC;				
			case 'both':
			default:
				return MYSQL_BOTH;
		}
	}
	
	public static function fieldType($abstractType) {
		if(isset($typeTable[$abstractType])) return $typeTable[$abstractType];
	}
	
	static $typeTable = array(
		"integer" => "int",
		"float"	=> "float",
		"timestamp"	=> "int",
		"mediumtext" => "mediumtext",
		"text"	=> "text");
				
}
?>
