<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DBQuery version 1.8 for Cubrid

global $fileCachedResult;

class DBAdapter implements IAdapter {	
	static $dbProperties, $cachedResult,$lastQueryType;
	/*@static@*/
	public static function bind($database) {
		// Connects DB and set environment variables
		// $database array should contain 'server','username','password'.
		if(!isset($database) || empty($database)) return false;
		$handle = @cubrid_connect($database['server'], $database['port'], $database['database'], $database['username'], $database['password']);
		if(!$handle) return false;
		self::$dbProperties['handle'] = $handle;	// Keeping handle
		self::$dbProperties['charset'] = 'utf8';
		return true;
	}
	
	public static function unbind() {
		@cubrid_commit(self::$dbProperties['handle']);
		cubrid_disconnect(self::$dbProperties['handle']);
		return true;
	}

	public static function charset() {
		if (array_key_exists('charset', self::$dbProperties)) return self::$dbProperties['charset'];
		else return null;
	}
	public static function dbms() {
		return 'Cubrid';
	}

	public static function version($mode = 'server') {
		if (array_key_exists('version', self::$dbProperties)) return self::$dbProperties['version'];
		else {
			self::$dbProperties['version'] = cubrid_version();
			return self::$dbProperties['version'];
		}
	}

	public static function tableList($condition = null) {
		if (!array_key_exists('tableList', self::$dbProperties)) { 
			self::$dbProperties['tableList'] = self::queryColumn("SELECT class_name FROM db_class WHERE is_system_class = 'NO'");
		}
		if(!is_null($condition)) {
			$result = array();
			foreach(self::$dbProperties['tableList'] as $item) {
				if(strpos($item, $condition) === 0) {array_push($result, $item);}
			}
			return $result;
		} else {
			return self::$dbProperties['tableList'];
		}
	}

	public static function reservedFieldNames() {
		return array('date','value','data','count','year','month', 'type', 'size');
	}
	
	public static function reservedFunctionNames() {
		return array('UNIX_TIMESTAMP()');
	}
	
	public static function setTimezone($time) {
		return true;
		return self::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');
	}

	/*@static@*/
	public static function query($query, $compatibility = true) {
		/// Bypassing compatiblitiy issue : will be replace to NAF2.
		if($compatibility) {
			$query = str_replace('UNIX_TIMESTAMP()',Timestamp::getUNIXtime(),$query); // compatibility issue.
			if(stripos($query, "ORDER BY")!==false) {
				$origPagingInst = array(
					'/(ASC|DESC) LIMIT ([0-9]+) OFFSET 0/si',
					'/(ASC|DESC) LIMIT ([0-9]+) OFFSET ([0-9]+)/si',
					'/(ASC|DESC) LIMIT 1(^[0-9])/si',
					'/(ASC|DESC) LIMIT ([0-9]+)/si',
					'/RAND\(\) LIMIT ([0-9]+)/si'
				);
				$descPagingInst = array(
					'$1 FOR ORDERBY_NUM() BETWEEN 1 AND $2',
					'$1 FOR ORDERBY_NUM() BETWEEN ($3+1) AND ($2+$3)',
					'$1 FOR ORDERBY_NUM() = 1',
					'$1 FOR ORDERBY_NUM() BETWEEN 1 AND $2',
					'RANDOM() FOR ORDERBY_NUM() BETWEEN 1 AND $1'
				);
			} else if(stripos($query, "GROUP BY")!==false) {
				$origPagingInst = array(
					'/GROUP BY(.*)(ORDER BY)(.*)(ASC|DESC) LIMIT ([0-9]+) OFFSET 0/si',
					'/GROUP BY(.*)(ORDER BY)(.*)(ASC|DESC) LIMIT ([0-9]+) OFFSET ([0-9]+)/si',
					'/GROUP BY(.*)(ORDER BY)(.*)(ASC|DESC) LIMIT 1(^[0-9])/si',
					'/GROUP BY(.*)(ORDER BY)(.*)(ASC|DESC) LIMIT ([0-9]+)/si',
					'/GROUP BY(.*)(ORDER BY)(.*)RAND\(\) LIMIT ([0-9]+)/si'
				);
				$descPagingInst = array(
					'GROUP BY $1 HAVING GROUPBY_NUM() = $5 $2 $3 $4',
					'GROUP BY $1 HAVING GROUPBY_NUM() BETWEEN ($6+1) AND $5 $2 $3 $4',
					'GROUP BY $1 HAVING GROUPBY_NUM() = 1 $2 $3 $4',
					'GROUP BY $1 HAVING GROUPBY_NUM() BETWEEN 1 AND $5 $2 $3 $4',
					'GROUP BY $1 HAVING GROUPBY_NUM() BETWEEN 1 AND $4 $2 RANDOM() $3'
				);
			} else {
				$origPagingInst = array(
					'/WHERE(.*)LIMIT ([0-9]+) OFFSET 0/si',
					'/WHERE(.*)LIMIT ([0-9]+) OFFSET ([0-9]+)/si',
					'/WHERE(.*)LIMIT 1(^[0-9])/si',
					'/WHERE(.*)LIMIT ([0-9]+)/si',
					'/SUM\((size|value)\)/si'
					);
				$descPagingInst = array(
					'WHERE ROWNUM BETWEEN 1 AND $2 AND $1',	
					'WHERE ROWNUM BETWEEN ($3+1) AND ($2+$3) AND $1',
					'WHERE ROWNUM = 1 AND $1',
					'WHERE ROWNUM BETWEEN 1 AND $2 AND $1',
					'SUM("$1")'
					);
			}
			$query = preg_replace($origPagingInst, $descPagingInst,$query);

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
			$result = cubrid_execute(self::$dbProperties['handle'],$query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = cubrid_execute(self::$dbProperties['handle'],$query);
		}
		self::$lastQueryType = strtolower(substr($query, 0,6));
		if( in_array(self::$lastQueryType, array('insert','update','delete','replac'))) {
			self::commit();
			self::clearCache();
		}
		return $result;
	}

	/*@static@*/
	public static function queryExistence($query) {
		if ($result = self::query($query)) {
			if (cubrid_num_rows($result) > 0) {
				cubrid_close_request($result);
				return true;
			}
			cubrid_close_request($result);
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
					$count = cubrid_num_rows($result);
					cubrid_close_request($result);
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = cubrid_affected_rows($result);
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
			while ($row = cubrid_fetch($result))
				array_push($column, $row[0]);
			cubrid_close_request($result);
		}

		if( $useCache ) {
			self::$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	public static function queryAll($query, $type = 'both', $count = -1) {
		return self::queryAllWithCache($query, $type, $count);
		//return self::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	public static function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = self::__queryType($type);
		if ($result = self::query($query)) {
			while ( ($count-- !=0) && $row = cubrid_fetch($result, $realtype))
				array_push($all, $row);
			cubrid_close_request($result);
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
		return cubrid_insert_id();
	}
	
	public static function escapeString($string, $link = null){
		return preg_replace("/'/","''",$string);
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
		@cubrid_commit(self::$dbProperties['handle']);
	}

	/* Raw public static functions (to easier adoptation) */
	/*@static@*/
	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return cubrid_num_rows($handle);
				break;
			default:
				return cubrid_affected_rows($handle);
				break;
		}
		return null;
	}
	/*@static@*/
	public static function free($handle = null) {
		cubrid_close_request($handle);
	}
	
	/*@static@*/
	public static function fetch($handle = null, $type = 'assoc') {
		$realtype = self::__queryType($type);
		return cubrid_fetch($handle,$realtype);
	}
	
	/*@static@*/
	public static function error($err = null) {
		if($err === null) return cubrid_error();
		else return cubrid_error($err);
	}
	
	/*@static@*/
	public static function stat($stat = null) {
		if($stat === null) return cubrid_stat();
		else return cubrid_stat($stat);
	}
	
	/*@static@*/
	public static function __queryType($type) {
		switch(strtolower($type)) {
			case 'num':
				return CUBRID_NUM;
			case 'assoc':
				return CUBRID_ASSOC;				
			case 'both':
			default:
				return CUBRID_BOTH;
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
