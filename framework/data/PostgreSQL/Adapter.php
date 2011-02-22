<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DBQuery version 1.7 for Postgresql

global $fileCachedResult;

class DBAdapter implements IAdapter {	
	static $dbProperties, $cachedResult, $lastQueryType;
	/*@static@*/
	public static function bind($database) {
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		if(!isset($database) || empty($database)) return false;
		$sql = "host=".$database['server'];
		if(isset($database['port'])) $sql .= " port=".$database['port'];
		$sql .= " user=".$database['username']." password=".$database['password'];
		if(isset($database['database'])) $sql .= " dbname=".$database['database'];
		$handle = @pg_connect($sql);
		if(!$handle) return false;
		
		@pg_set_client_encoding($handle, "UTF8");

		self::$dbProperties['charset'] = 'utf8';
		return true;
	}

	public static function unbind() {
		pg_close();
		return true;
	}

	public static function charset() {
		if (array_key_exists('charset', self::$dbProperties)) return self::$dbProperties['charset'];
		else return null;
	}

	public static function dbms() {
		return 'PostgreSQL';
	}

	public static function version($mode = 'server') {
		if (array_key_exists('version', self::$dbProperties)) return self::$dbProperties['version'];
		else {
			self::$dbProperties['version'] = pg_version();
			if($mode == 'server') return self::$dbProperties['version']['server'];
			else return self::$dbProperties['version']['client'];
		}
	}
	public static function tableList($condition = null) {
		if (!array_key_exists('tableList', self::$dbProperties)) { 
			self::$dbProperties['tableList'] = self::queryColumn("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
		}
		if(!is_null($condition)) {
			$result = array();
			foreach(self::$dbProperties['tableList'] as $item) {
				if(strpos($item, $condition) === 0) array_push($result, $item);
			}
			return $result;
		} else {
			return self::$dbProperties['tableList'];
		}
	}

	public static function setTimezone($time) {
		return self::query('SET TIME ZONE \'' . $time . '\'');
	}

	public static function reservedFieldNames() {
		return null;
	}

	/*@static@*/
	public static function reservedFunctionNames() {
		return array('UNIX_TIMESTAMP()');
	}

	public static function query($query, $compatiblity = true) {
		/// Bypassing compatiblitiy issue : will be replace to NAF2.
		if($compatibility) {
			$query = str_replace('UNIX_TIMESTAMP()',Timestamp::getUNIXtime(),$query); // compatibility issue.

			// CONCAT
			$ppos = -1;
			$length = strlen($query);
			do {
				$pos = strpos($query, '\'', $ppos + 1);
				if ($pos === false) {
					$pos = strlen($query);
				}

				while (true) {
					$concat = stripos($query, 'CONCAT', $ppos + 1);
					if ($concat === false || $concat >= $pos) {
						break;
					}

					$depth = 0;
					$quote = null;
					for ($i = $concat + 6; $i < $length; $i++) {
						if ($quote === null) {
							if ($query[$i] == '\'' || $query[$i] == '"') {
								$quote = $query[$i];
							} elseif ($query[$i] == ',') {
								$query = substr($query, 0, $i).' || '.substr($query, $i + 1);
							} elseif ($query[$i] == '(') {
								$depth++;
							} elseif ($query[$i] == ')') {
								if (--$depth == 0) {
									break;
								}
							}
						} else {
							if ($query[$i] == $quote && $query[$i - 1] != '\\') {
								$quote = null;
							}
						}
					}
					$query = substr($query, 0, $concat).substr($query, $concat + 6);

					$pos = strpos($query, '\'', $ppos + 1);
					$length = strlen($query);
				}

				$ppos = $pos;
				while ($ppos < $length) {
					$ppos = strpos($query, '\'', $ppos + 1);
					if ($query[$ppos - 1] != '\\') {
						break;
					}
				}
			} while ($ppos < $length);
		}		
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = pg_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = pg_query($query);
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
	
	/*@static@*/
	public static function queryExistence($query) {
		if ($result = self::query($query)) {
			if (pg_num_rows($result) > 0) {
				pg_free_result($result);
				return true;
			}
			pg_free_result($result);
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
					$count = pg_num_rows($result);
					pg_free_result($result);
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = pg_affected_rows($result);
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
			while ($row = pg_fetch_row($result))
				array_push($column, $row[0]);
			pg_free_result($result);
		}

		if( $useCache ) {
			self::$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	public static function queryAll($query, $type = 'both', $count = -1) {
		return self::queryAllWithCache($query, $type, $count);
	}

	public static function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = self::__queryType($type);
		if ($result = self::query($query)) {
			while ( ($count-- !=0) && $row = pg_fetch_array($result, null, $realtype))
				array_push($all, $row);
			pg_free_result($result);
			return $all;
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
	
	public static function insertId() {
		return null;
	}
	
	public static function escapeString($string, $link = null){
		return pg_escape_string($string);
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
		@self::commit();
	}

	public static function commit() { 
		return pg_query("commit");
//		return true; // Auto commit.
	}

	/* Raw public static functions (to easier adoptation) */
	/*@static@*/
	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return pg_num_rows($handle);
				break;
			default:
				return pg_affected_rows($handle);
				break;
		}
		return null;
	}
	
	/*@static@*/
	public static function free($handle = null) {
		pg_free_result($handle);
	}
	
	/*@static@*/
	public static function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return pg_fetch_array($handle); // Can I use mysql_fetch_row instead?
		else if ($type == 'row') return pg_fetch_row($handle);
		else return pg_fetch_assoc($handle);
	}
	
	/*@static@*/
	public static function error($err = null) {
		if($err === null) return pg_error();
		else return pg_error($err);
	}
	
	/*@static@*/
	public static function stat($stat = null) {
		if($stat === null) return pg_connection_status();
		else return pg_connection_status($stat);
	}
	
	/*@static@*/
	public static function __queryType($type) {
		switch(strtolower($type)) {
			case 'num':
				return PGSQL_NUM;
			case 'assoc':
				return PGSQL_ASSOC;				
			case 'both':
			default:
				return PGSQL_BOTH;
		}
	}
	
	public static function fieldType($abstractType) {
		if(isset($typeTable[$abstractType])) return $typeTable[$abstractType];
	}
	
	static $typeTable = array(
		"integer" => "integer",
		"float"	=> "float",
		"timestamp"	=> "integer",
		"mediumtext" => "varchar(512)",
		"text"	=> "text");
}
?>
