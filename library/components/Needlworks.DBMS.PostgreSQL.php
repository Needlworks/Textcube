<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// DBQuery version 1.7 for Postgresql

global $cachedResult;
global $fileCachedResult;
global $__gEscapeTag;
global $__dbProperties;
global $__gLastQueryType;
$cachedResult = $__dbProperties = array();
$__gEscapeTag = null;

class DBQuery {	
	/*@static@*/
	function bind($database) {
		global $__dbProperties;
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

		$__dbProperties['charset'] = 'utf8';
		return true;
	}

	function unbind() {
		pg_close();
		return true;
	}

	function charset() {
		global $__dbProperties;
		if (array_key_exists('charset', $__dbProperties)) return $__dbProperties['charset'];
		else return null;
	}

	function dbms() {
		return 'PostgreSQL';
	}

	function version($mode = 'server') {
		global $__dbProperties;
		if (array_key_exists('version', $__dbProperties)) return $__dbProperties['version'];
		else {
			$__dbProperties['version'] = pg_version();
			if($mode == 'server') return $__dbProperties['version']['server'];
			else return $__dbProperties['version']['client'];
		}
	}
	function tableList($condition = null) {
		global $__dbProperties;
		if (!array_key_exists('tableList', $__dbProperties)) { 
			$__dbProperties['tableList'] = DBQuery::queryAll("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
		}
		if(!is_null($condition)) {
			foreach($__dbProperties['tableList'] as $item) {
				if(strpos($item, $condition) === 0) array_push($result, $item);
			}
			return $item;
		} else {
			return $__dbProperties['tableList'];
		}
	}

	function setTimezone($time) {
		return DBQuery::query('SET TIME ZONE \'' . $time . '\'');
	}

	/*@static@*/
	function query($query) {
		global $__gLastQueryType;
		/// Bypassing compartiblitiy issue : will be replace to NAF2.
		$query = str_replace('UNIX_TIMESTAMP()',Timestamp::getUNIXtime(),$query); // compartibility issue.
		$caseSensiviveReservedWords = array(
			"isFiltered","entriesInLogin","siteId", "isNew", "remoteId", "entryTitle",
			"entryUrl","commentId","sendStatus","checkDate",
			"contentFormatter","contentEditor","acceptTrackback","acceptComment", // Entry
			"groupId",
			"updateCycle","feedLife","loadImage", "allowScript","newWindow", // Feed
			"xmlURL","blogURL","firstLogin","lastLogin","loginCount");
		foreach ($caseSensiviveReservedWords as $word) {
			$query = str_replace($word, "\"".$word."\"", $query);
		}
		
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = pg_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = pg_query($query);
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
	
	/*@static@*/
	function queryExistence($query) {
		if ($result = DBQuery::query($query)) {
			if (pg_num_rows($result) > 0) {
				pg_free_result($result);
				return true;
			}
			pg_free_result($result);
		}
		return false;
	}

	/*@static@*/
	function queryCount($query) {
		global $__gLastQueryType;
		$count = 0;
		$query = trim($query);
		if ($result = DBQuery::query($query)) {
			$operation = strtolower(substr($query, 0,6));
			$__gLastQueryType = $operation;
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
	function queryCell($query, $field = 0, $useCache=true) {
		$type = 'both';
		if (is_numeric($field)) {
			$type = 'num';
		} else {
			$type = 'assoc';
		}

		if( $useCache ) {
			$result = POD::queryAllWithCache($query, $type);
		} else {
			$result = DBQuery::queryAllWithoutCache($query, $type);
		}
		if( empty($result) ) {
			return null;
		}
		return $result[0][$field];
	}
	
	/*@static@*/
	function queryRow($query, $type = 'both', $useCache=true) {
		if( $useCache ) {
			$result = POD::queryAllWithCache($query, $type, 1);
		} else {
			$result = DBQuery::queryAllWithoutCache($query, $type, 1);
		}
		if( empty($result) ) {
			return null;
		}
		return $result[0];
	}
	
	/*@static@*/
	function queryColumn($query, $useCache=true) {
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
		if ($result = DBQuery::query($query)) {
			$column = array();
			while ($row = pg_fetch_row($result))
				array_push($column, $row[0]);
			pg_free_result($result);
		}

		if( $useCache ) {
			$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	function queryAll($query, $type = 'both', $count = -1) {
		return DBQuery::queryAllWithCache($query, $type, $count);
		//return DBQuery::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = DBQuery::__queryType($type);
		if ($result = DBQuery::query($query)) {
			while ( ($count-- !=0) && $row = pg_fetch_array($result, null, $realtype))
				array_push($all, $row);
			pg_free_result($result);
			return $all;
		}
		return null;
	}
		
	function queryAllWithCache($query, $type = 'both', $count = -1) {
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
		$all = DBQuery::queryAllWithoutCache($query,$type,$count);
		$cachedResult[$cacheKey] = array( 1, $all );
		return $all;
	}
	
	/*@static@*/
	function execute($query) {
		return DBQuery::query($query) ? true : false;
	}
	
	/*@static@*/
	function multiQuery() {
		$result = false;
		foreach (func_get_args() as $query) {
			if (is_array($query)) {
				foreach ($query as $subquery)
					if (($result = DBQuery::query($subquery)) === false)
						return false;
			} else if (($result = DBQuery::query($query)) === false)
				return false;
		}
		return $result;
	}
	
	function insertId() {
		return null;
	}
	
	function escapeString($string, $link = null){
		return pg_escape_string($string);
	}
	
	function clearCache() {
		global $cachedResult;
		$cachedResult = array();
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin("Cache cleared");
			__tcSqlLogEnd(null,2);
		}
	}

	function cacheLoad() {
		global $fileCachedResult;
	}
	function cacheSave() {
		global $fileCachedResult;
	}

	/* Raw functions (to easier adoptation) */
	/*@static@*/
	function num_rows($handle = null) {
		global $__gLastQueryType;
		switch($__gLastQueryType) {
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
	function free($handle = null) {
		pg_free_result($handle);
	}
	
	/*@static@*/
	function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return pg_fetch_array($handle); // Can I use mysql_fetch_row instead?
		else if ($type == 'row') return pg_fetch_row($handle);
		else return pg_fetch_assoc($handle);
	}
	
	/*@static@*/
	function error($err = null) {
		if($err === null) return pg_error();
		else return pg_error($err);
	}
	
	/*@static@*/
	function stat($stat = null) {
		if($stat === null) return mysql_stat();
		else return mysql_stat($stat);
	}
	
	/*@static@*/
	function __queryType($type) {
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
}

DBQuery::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );

?>
