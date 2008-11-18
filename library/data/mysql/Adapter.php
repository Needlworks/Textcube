<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// DBQuery version 1.7 for MySQL

global $cachedResult;
global $fileCachedResult;
global $__gEscapeTag;
global $__dbProperties;
global $__gLastQueryType;
$cachedResult = $__dbProperties = array();
$__gEscapeTag = null;

class DBQuery {	
	public static function bind($database) {
		global $__dbProperties;
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		if(!isset($database) || empty($database)) return false;
		$handle = @mysql_connect($database['server'], $database['username'], $database['password']);
		if(!$handle) return false;
		$handle = @mysql_select_db($database['database']);
		if(!$handle) return false;

		if (self::query('SET CHARACTER SET utf8'))
			$__dbProperties['charset'] = 'utf8';
		else
			$__dbProperties['charset'] = 'default';
		@self::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		return true;
	}
	
	public static function unbind() {
		mysql_close();
		return true;
	}

	public static function charset() {
		global $__dbProperties;
		if (array_key_exists('charset', $__dbProperties)) return $__dbProperties['charset'];
		else return null;
	}

	public static function dbms() {
		return 'MySQL';
	}

	public static function version() {
		global $__dbProperties;
		if (array_key_exists('version', $__dbProperties)) return $__dbProperties['version'];
		else {
			$__dbProperties['version'] = DBQuery::queryCell("SHOW VARIABLES LIKE 'version'");
			return $__dbProperties['version'];
		}
	}

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
	
	public static function queryCount($query) {
		global $__gLastQueryType;
		$count = 0;
		$query = trim($query);
		if ($result = self::query($query)) {
			$operation = strtolower(substr($query, 0,6));
			$__gLastQueryType = $operation;
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

	public static function queryCell($query, $field = 0, $useCache=true) {
		$type = MYSQL_BOTH;
		if (is_numeric($field)) {
			$type = MYSQL_NUM;
		} else {
			$type = MYSQL_ASSOC;
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
	
	public static function queryRow($query, $type = MYSQL_BOTH, $useCache=true) {
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
	
	public static function queryColumn($query, $useCache=true) {
		global $cachedResult;
		$cacheKey = "{$query}_queryColumn";
		if( $useCache && isset( $cachedResult[$cacheKey] ) ) {
			if( function_exists( '__tcSqlLogBegin' ) ) {
				__tcSqlLogBegin($query);
				__tcSqlLogEnd(null,1);
			}
			$cachedResult[$cacheKey][0]++;
			return $cachedResult[$cacheKey][1];
		}

		$column = null;
		if ($result = self::query($query)) {
			$column = array();
			while ($row = mysql_fetch_row($result))
				array_push($column, $row[0]);
			mysql_free_result($result);
		}

		if( $useCache ) {
			$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	public static function queryAll ($query, $type = MYSQL_BOTH, $count = -1) {
		if($type == 'assoc') $type = MYSQL_ASSOC;
		else if ($type == 'num') $type = MYSQL_NUM;
		return self::queryAllWithCache($query, $type, $count);
		//return DBQuery::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	public static function queryAllWithoutCache($query, $type = MYSQL_BOTH, $count = -1) {
		$all = array();
		if ($result = self::query($query)) {
			while ( ($count-- !=0) && $row = mysql_fetch_array($result, $type))
				array_push($all, $row);
			mysql_free_result($result);
			return $all;
		}
		return null;
	}
	
	public static function queryAllWithCache($query, $type = MYSQL_BOTH, $count = -1) {
		global $cachedResult;
		$cacheKey = "{$query}_{$type}_{$count}";
		if( isset( $cachedResult[$cacheKey] ) ) {
			if( function_exists( '__tcSqlLogBegin' ) ) {
				__tcSqlLogBegin($query);
				__tcSqlLogEnd(null,1);
			}
			$cachedResult[$cacheKey][0]++;
			return $cachedResult[$cacheKey][1];
		}
		$all = self::queryAllWithoutCache($query,$type,$count);
		$cachedResult[$cacheKey] = array( 1, $all );
		return $all;
	}
	
	public static function execute($query) {
		return self::query($query) ? true : false;
	}

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

	public static function query($query) {
		global $__gLastQueryType;
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = mysql_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = mysql_query($query);
		}
		$__gLastQueryType = strtolower(substr($query, 0,6));
		if( stristr($query, 'update ') ||
			stristr($query, 'insert ') ||
			stristr($query, 'delete ') ||
			stristr($query, 'replace ') ) {
			DBQuery::clearCache();
		}
		return $result;
	}
	
	public static function escapeString($string, $link = null){
		global $__gEscapeTag;
		if(is_null($__gEscapeTag)) {
			if (function_exists('mysql_real_escape_string') && (mysql_real_escape_string('ㅋ') == 'ㅋ')) {
				$__gEscapeTag = 'real';
			} else {
				$__gEscapeTag = 'none';
			}
		}
		if($__gEscapeTag == 'real') {
			return is_null($link) ? mysql_real_escape_string($string) : mysql_real_escape_string($string, $link);
		} else {
			return mysql_escape_string($string);
		}
	}

/*** Instant cache functions ***/
	public static function clearCache() {
		global $cachedResult;
		$cachedResult = array();
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
	
/*** Raw functions (to easier adoptation from traditional queries) ***/
	public static function insertId() {
		return mysql_insert_id();
	}
	
	public static function num_rows($handle = null) {
		global $__gLastQueryType;
		switch($__gLastQueryType) {
			case 'select':
				return mysql_num_rows($handle);
				break;
			default:
				return mysql_affected_rows($handle);
				break;
		}
		return null;
	}
	
	public static function free($handle = null) {
		mysql_free_result($handle);
	}
	
	public static function fetch($handle = null, $type = 'assoc') {
		if ($handle == null) return null;
		if($type == 'array') return mysql_fetch_array($handle); // Can I use mysql_fetch_row instead?
		else if ($type == 'row') return mysql_fetch_row($handle);
		else return mysql_fetch_assoc($handle);
	}
	
	public static function error($err = null) {
		if($err === null) return mysql_error();
		return mysql_error($err);
	}
	
	public static function stat($stat = null) {
		if($stat === null) return mysql_stat();
		else return mysql_stat($stat);
	}
}

DBQuery::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );
?>
