<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// DBQuery version 1.8 for MySQL improved.

global $fileCachedResult;

class DBQuery {	
	static $db;
	static $cachedResult, $dbProperties, $escapeTag, $lastQueryType;
	public static function bind($database) {
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		if(!isset($database) || empty($database)) return false;
		self::$db = new mysqli($database['server'], $database['username'], $database['password'], $database['database'],8889);
		if(!self::$db) return false; 
		if(!self::$db->select_db($database['database']))
			die("Connection error :".self::$db->errorno." - ".self::$db->error);

		if (self::query('SET CHARACTER SET utf8'))
			self::$dbProperties['charset'] = 'utf8';
		else
			self::$dbProperties['charset'] = 'default';
		@self::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		self::$cachedResult = self::$dbProperties = array();
		return true;
	}
	
	public static function unbind() {
		self::$db->close();
		return true;
	}

	public static function charset() {
		if (array_key_exists('charset', self::$dbProperties)) return self::$dbProperties['charset'];
		else return null;
	}

	public static function dbms() {
		return 'MySQLi';
	}

	public static function version() {
		if (array_key_exists('version', self::$dbProperties)) return self::$dbProperties['version'];
		else {
			self::$dbProperties['version'] = self::queryCell("SHOW VARIABLES LIKE 'version'");
			return self::$dbProperties['version'];
		}
	}

	public static function queryExistence($query) {
		if ($result = self::query($query)) {
			if ($result->num_rows > 0) {
				$result->free();
				return true;
			}
			$result->free();
		}
		return false;
	}
	
	public static function queryCount($query) {
		$count = 0;
		$query = trim($query);
		if ($result = self::query($query)) {
			$operation = strtolower(substr($query, 0,6));
			self::$lastQueryType = $operation;
			switch ($operation) {
				case 'select':
					$count = $result->num_rows;
					$result->free();
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = self::$db->affected_rows;
					//mysqli_free_result();
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
		$cacheKey = "{$query}_queryColumn";
		if( $useCache && isset( self::$cachedResult[$cacheKey] ) ) {
			if( function_exists( '__tcSqlLogBegin' ) ) {
				__tcSqlLogBegin($query);
				__tcSqlLogEnd(null,1);
			}
			self::$cachedResult[$cacheKey][0]++;
			return self::$cachedResult[$cacheKey][1];
		}

		$column = null;
		if ($result = self::query($query)) {
			$column = array();
			
			while ($row = $result->fetch_row())
				array_push($column, $row[0]);
			$result->free();
		}

		if( $useCache ) {
			self::$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	public static function queryAll ($query, $type = MYSQL_BOTH, $count = -1) {
		if($type == 'assoc') $type = MYSQL_ASSOC;
		else if ($type == 'num') $type = MYSQL_NUM;
		return self::queryAllWithCache($query, $type, $count);
		//return self::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	public static function queryAllWithoutCache($query, $type = MYSQL_BOTH, $count = -1) {
		$all = array();
		if ($result = self::query($query)) {
			while ( ($count-- !=0) && $row = $result->fetch_array($type))
				array_push($all, $row);
			$result->free();
			return $all;
		}
		return null;
	}
	
	public static function queryAllWithCache($query, $type = MYSQL_BOTH, $count = -1) {
		$cacheKey = "{$query}_{$type}_{$count}";
		if( isset( $cachedResult[$cacheKey] ) ) {
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
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = self::$db->query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = self::$db->query($query);
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
	
	public static function escapeString($string, $link = null){
		if(is_null(self::$escapeTag)) {
			if (self::$db->real_escape_string('ㅋ') == 'ㅋ') {
				self::$escapeTag = 'real';
			} else {
				self::$escapeTag = 'none';
			}
		}
		if(self::$escapeTag == 'real') {
			return self::$db->real_escape_string($string);;
		} else {
			return self::$db->escape_string($string);
		}
	}

/*** Instant cache functions ***/
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
	
/*** Raw functions (to easier adoptation from traditional queries) ***/
	public static function insertId() {
		return mysqli_insert_id();
	}
	
	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return mysqli_num_rows($handle);
				break;
			default:
				return mysqli_affected_rows($handle);
				break;
		}
		return null;
	}
	
	public static function free($handle = null) {
		mysqli_free_result($handle);
	}
	
	public static function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return mysqli_fetch_array($handle); // Can I use mysqli_fetch_row instead?
		else if ($type == 'row') return mysqli_fetch_row($handle);
		else return mysqli_fetch_assoc($handle);
	}
	
	public static function error($err = null) {
		return mysqli_error($err);
	}
	
	public static function stat($stat = null) {
		if($stat === null) return mysqli_stat();
		else return mysqli_stat($stat);
	}
}

DBQuery::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );
?>
