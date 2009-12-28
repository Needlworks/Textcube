<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DBQuery version 1.8 for MySQL improved.

global $fileCachedResult;

class DBAdapter implements IAdapter {	
	static $db;
	static $cachedResult, $dbProperties, $escapeTag, $lastQueryType;
	public static function bind($database) {
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		self::$cachedResult = self::$dbProperties = array();
		if(!isset($database) || empty($database)) return false;
		if(!isset($database['port']) && strpos($database['server'],':')) {
			$port = explode(":",$database['server']);
			$database['server'] = $port[0];
			$database['port'] = $port[1];
		}
		if(isset($database['port'])) {
			self::$db = new mysqli($database['server'], $database['username'], $database['password'], $database['database'],$database['port']);
		} else {
			self::$db = new mysqli($database['server'], $database['username'], $database['password'], $database['database']);
		}
		if(!self::$db) return false; 
		if(!self::$db->select_db($database['database']))
			die("Connection error :".self::$db->errorno." - ".self::$db->error);
		//self::$db->autocommit(false);	// Turns off autocommit.
		self::$db->autocommit(true);	// Turns off autocommit.
		if (self::$db->set_charset("utf8"))
			self::$dbProperties['charset'] = 'utf8';
		else
			self::$dbProperties['charset'] = 'default';
		@self::query('SET SESSION collation_connection = \'utf8_general_ci\'');
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

	public static function version($mode = "server") {
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
	
	public static function queryAll ($query, $type = 'both', $count = -1) {
		return self::queryAllWithCache($query, $type, $count);
		//return self::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	public static function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = self::__queryType($type);
		if ($result = self::query($query)) {
			while ( ($count-- !=0) && $row = $result->fetch_array($realtype))
				array_push($all, $row);
			$result->free();
			return $all;
		}
		return null;
	}
	
	public static function queryAllWithCache($query, $type = 'both', $count = -1) {
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
	public static function rollback() {
		return self::$db->rollback();
	}
	public static function commit() { 
		self::$db->commit(); // Auto commit.
		return true;
	}
		
/*** Raw functions (to easier adoptation from traditional queries) ***/
	public static function insertId() {
		return self::$db->insert_id;
	}
	
	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return mysqli_num_rows($handle);
				break;
			default:
				return self::$db->affected_rows;
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
		if($stat === null) return self::$db->stat();
		else return self::$db->stat($stat);
	}
	
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
