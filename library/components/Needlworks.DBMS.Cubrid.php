<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// DBQuery version 1.7 for Cubrid

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
		$handle = @cubrid_connect($database['server'], $database['port'], $database['database'], $database['username'], $database['password']);
		if(!$handle) return false;
//		$handle = @cubrid_select_db($database['database']);
//		if(!$handle) return false;
		$__dbProperties['handle'] = $handle;	// Keeping handle
		if (DBQuery::query('SET CHARACTER SET utf8'))
			$__dbProperties['charset'] = 'utf8';
		else
			$__dbProperties['charset'] = 'default';
		@DBQuery::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		return true;
	}
	
	function unbind() {
		cubrid_disconnect();
		return true;
	}

	function charset() {
		global $__dbProperties;
		if (array_key_exists('charset', $__dbProperties)) return $__dbProperties['charset'];
		else return null;
	}
	function dbms() {
		return 'MySQL';
	}

	function version($mode = 'server') {
		global $__dbProperties;
		if (array_key_exists('version', $__dbProperties)) return $__dbProperties['version'];
		else {
			$__dbProperties['version'] = cubrid_version();
			return $__dbProperties['version'];
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
		return DBQuery::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');
	}

	/*@static@*/
	function queryExistence($query) {
		if ($result = DBQuery::query($query)) {
			if (cubrid_num_rows($result) > 0) {
				cubrid_free_result($result);
				return true;
			}
			cubrid_free_result($result);
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
					$count = cubrid_num_rows($result);
					cubrid_free_result($result);
					break;
				case 'insert':
				case 'update':
				case 'delete':
				case 'replac':
				default:
					$count = cubrid_affected_rows();
					//cubrid_free_result();
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
			$result = DBQuery::queryAllWithCache($query, $type);
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
			$result = DBQuery::queryAllWithCache($query, $type, 1);
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
			while ($row = cubrid_fetch_row($result))
				array_push($column, $row[0]);
			cubrid_free_result($result);
		}

		if( $useCache ) {
			$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	function queryAll ($query, $type = 'both', $count = -1) {
		return DBQuery::queryAllWithCache($query, $type, $count);
		//return DBQuery::queryAllWithoutCache($query, $type, $count);  // Your choice. :)
	}

	function queryAllWithoutCache($query, $type = 'both', $count = -1) {
		$all = array();
		$realtype = DBQuery::__queryType($type);
		if ($result = DBQuery::query($query)) {
			while ( ($count-- !=0) && $row = cubrid_fetch($result, $realtype))
				array_push($all, $row);
			cubrid_free_result($result);
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

	/*@static@*/
	function query($query) {
		global $__gLastQueryType;
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = cubrid_execute($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = cubrid_execute($query);
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
	
	function insertId() {
		return cubrid_insert_id();
	}
	
	function escapeString($string, $link = null){
		global $__gEscapeTag;
		if(is_null($__gEscapeTag)) {
			if (function_exists('cubrid_real_escape_string') && (cubrid_real_escape_string('ㅋ') == 'ㅋ')) {
				$__gEscapeTag = 'real';
			} else {
				$__gEscapeTag = 'none';
			}
		}
		if($__gEscapeTag == 'real') {
			return is_null($link) ? cubrid_real_escape_string($string) : cubrid_real_escape_string($string, $link);
		} else {
			return cubrid_escape_string($string);
		}
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
				return cubrid_num_rows($handle);
				break;
			default:
				return cubrid_affected_rows($handle);
				break;
		}
		return null;
	}
	/*@static@*/
	function free($handle = null) {
		cubrid_free_result($handle);
	}
	
	/*@static@*/
	function fetch($handle = null, $type = 'assoc') {
		if($type == 'array') return cubrid_fetch_array($handle); // Can I use cubrid_fetch_row instead?
		else if ($type == 'row') return cubrid_fetch_row($handle);
		else return cubrid_fetch_assoc($handle);
	}
	
	/*@static@*/
	function error($err = null) {
		if($err === null) return cubrid_error();
		else return cubrid_error($err);
	}
	
	/*@static@*/
	function stat($stat = null) {
		if($stat === null) return cubrid_stat();
		else return cubrid_stat($stat);
	}
	
	/*@static@*/
	function __queryType($type) {
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
}

DBQuery::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );

?>
