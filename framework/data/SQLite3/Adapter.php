<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DBQuery version 1.8 for SQLite3.

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
		if(!defined('__TEXTCUBE_DATA_DIR__')){
			define('__TEXTCUBE_DATA_DIR__',__TEXTCUBE_CACHE_DIR__);
		}
		if(!file_exists(__TEXTCUBE_DATA_DIR__)) {
			@mkdir(__TEXTCUBE_DATA_DIR__);
		}
		self::$db = new SQLite3(__TEXTCUBE_DATA_DIR__.'/'.$database['database'].'.sqlite',SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

		if(!self::$db) return false;

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
		return 'SQLite3';
	}

	public static function version($mode = "server") {
		if (array_key_exists('version', self::$dbProperties)) return self::$dbProperties['version'];
		else {
			$version = self::$db->version();
			self::$dbProperties['version'] = $version['versionString'];
			return self::$dbProperties['version'];
		}
	}

	public static function tableList($condition = null) {
		if (!array_key_exists('tableList', self::$dbProperties)) {
			$tableData = self::queryAll('SELECT name from sqlite_master WHERE type="table"');
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
		return true;
#		return self::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');
	}

	public static function reservedFieldNames() {
		return null;
	}

	public static function reservedFunctionNames() {
		return array('UNIX_TIMESTAMP()');
	}

	public static function queryExistence($query) {
		if ($result = self::query($query)) {
			if (self::$db->changes() > 0) {
				$result->finalize();
				return true;
			}
			$result->finalize();
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
					while ($buf = $result->fetchArray()) {
						$count++;
					}
					$result->finalize();
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = self::$db->changes();
					//mysqli_free_result();
					break;
			}
		}
		return $count;
	}

	public static function queryCell($query, $field = 0, $useCache=true) {
		$type = SQLITE3_BOTH;
		if (is_numeric($field)) {
			$type = SQLITE3_NUM;
		} else {
			$type = SQLITE3_ASSOC;
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

			while ($row = $result->fetchArray())
				array_push($column, $row[0]);
			$result->finalize();
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
			while ( ($count-- !=0) && $row = $result->fetchArray($realtype))
				array_push($all, $row);
			$result->finalize();
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
		return self::$db->exec($query);
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

	public static function query($query, $compatibility = true) {
//		var_dump($query);
		if($compatibility) {
			$query = str_replace('UNIX_TIMESTAMP()',Timestamp::getUNIXtime(),$query); // compatibility issue.
			$query = str_replace('RAND()','RANDOM()',$query); // compatibility issue.
			$origPagingInst = array(
				'/CHAR_LENGTH(.*) /si',
				'/IF\(([A-Za-z0-9]+),([A-Za-z0-9]+),([A-Za-z0-9]+)\)/si'
			);
			$descPagingInst = array(
				'LENGTH($1) ',
				'CASE WHEN $1 THEN $2 ELSE $3 END'
			);
			$query = preg_replace($origPagingInst, $descPagingInst,$query);
		}
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
		return self::$db->escapeString($string);
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
		return self::$db->lastInsertRowID();
	}

	public static function num_rows($handle = null) {
		switch(self::$lastQueryType) {
			case 'select':
				return sqlite_num_rows($handle);
				break;
			default:
				return self::$db->changes();
				break;
		}
		return null;
	}

	public static function free($handle = null) {
		sqlite_free_result($handle);
	}

	public static function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return sqlite_fetch_array($handle); // Can I use mysqli_fetch_row instead?
		else if ($type == 'row') return sqlite_fetch_row($handle);
		else return sqlite_fetch_assoc($handle);
	}

	public static function error($err = null) {
		return sqlite_error($err);
	}

	public static function stat($stat = null) {
		if($stat === null) return self::$db->stat();
		else return self::$db->stat($stat);
	}

	public static function __queryType($type) {
		switch(strtolower($type)) {
			case 'num':
				return SQLITE3_NUM;
			case 'assoc':
				return SQLITE3_ASSOC;
			case 'both':
			default:
				return SQLITE3_BOTH;
		}
	}

	public static function fieldType($abstractType) {
		if(isset($typeTable[$abstractType])) return $typeTable[$abstractType];
	}

    public static function structure($tableName) {
        $result = self::queryAll("DESCRIBE ".$tableName);
        $structure = array();
        foreach ($result as $r) {
            $structure[$r['Field']] = array();
            preg_match('/(.*)\((\d+)\)/si',$r['Type'], $match);
            switch (count($match)) {
                case 2:
                    $type = array_search($match[1],self::$typeTable);
                    break;
                case 3:
                    $type = array_search($match[1],self::$typeTable);
                    $structure[$r['Field']]['length'] = $match[2];
                    break;
            }
            $structure[$r['Field']]['type'] = $type;
            if ($r['Null'] == 'NO') {
                $structure[$r['Field']]['isNull'] = false;
            } else {
                $structure[$r['Field']]['isNull'] = true;
            }
            if ($r['Key'] == 'PRI') {
                if (!isset($this->option['primary'])) {
                    $this->option['primary'] = array();
                }
                array_push($this->option['primary'], $r['Field']);
            } elseif ($r['Key'] == 'MUL') {
                $structure[$r['Field']]['index'] = true;
            }
            if ($r['Default'] != 'NULL') {
                $structure[$r['Field']]['default'] = $r['Default'];
            }
        }
        return $structure;
    }

    static $typeTable = array(
		"integer" => "int",
		"int" => "int",
		"float"	=> "float",
		"double"	=> "float",
		"timestamp"	=> "int",
		"mediumtext" => "mediumtext",
		"varchar" => "varchar",
		"text"	=> "text");
}
?>
