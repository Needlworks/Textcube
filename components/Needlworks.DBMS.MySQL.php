<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $cachedResult;
global $fileCachedResult;
global $__gEscapeTag;
$cachedResult = array();
$__gEscapeTag = null;

class DBQuery {	
	/*@static@*/
	function bind() {
		// Connects DB and set environment variables
		// $database['utf8'] should be validated.
		global $database;
		if(!isset($database) || empty($database)) return false;
		mysql_connect($database['server'], $database['username'], $database['password']);
		mysql_select_db($database['database']);

		if (DBQuery::query('SET CHARACTER SET utf8'))
			$database['utf8'] = true;
		else
			$database['utf8'] = false;
		@DBQuery::query('SET SESSION collation_connection = \'utf8_general_ci\'');
	}
	function unbind() {
		mysql_close();
		return true;
	}
	
	/*@static@*/
	function queryExistence($query) {
		if ($result = DBQuery::query($query)) {
			if (mysql_num_rows($result) > 0) {
				mysql_free_result($result);
				return true;
			}
			mysql_free_result($result);
		}
		return false;
	}
	
	/*@static@*/
	function queryCount($query) {
		$count = 0;
		$query = trim($query);
		if ($result = DBQuery::query($query)) {
			$operation = strtolower(substr($query, 0, strpos($query, ' ')));
			switch ($operation) {
			case 'select':
				$count = mysql_num_rows($result);
				mysql_free_result($result);
				break;
			case 'insert':
			case 'update':
			case 'delete':
			case 'replace':
				$count = mysql_affected_rows();
			}
		}
		return $count;
	}

	/*@static@*/
	function queryCell($query, $field = 0, $useCache=true) {
		$type = MYSQL_BOTH;
		if (is_numeric($field)) {
			$type = MYSQL_NUM;
		} else {
			$type = MYSQL_ASSOC;
		}

		if( $useCache ) {
			$result = DBQuery::queryAllWithCache($query, $type);
		} else {
			$result = DBQuery::queryAll($query, $type);
		}
		if( $result == null ) {
			return null;
		}
		return $result[0][$field];
	}
	
	/*@static@*/
	function queryRow($query, $type = MYSQL_BOTH, $useCache=true) {
		if( $useCache ) {
			$result = DBQuery::queryAllWithCache($query, $type, 1);
		} else {
			$result = DBQuery::queryAll($query, $type, 1);
		}
		if( $result == null ) {
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
			while ($row = mysql_fetch_row($result))
				array_push($column, $row[0]);
			mysql_free_result($result);
		}

		if( $useCache ) {
			$cachedResult[$cacheKey] = array( 1, $column );
		}
		return $column;
	}
	
	/*@static@*/
	function queryAll($query, $type = MYSQL_BOTH, $count = -1) {
		$all = array();
		if ($result = DBQuery::query($query)) {
			while ( ($count-- !=0) && $row = mysql_fetch_array($result, $type))
				array_push($all, $row);
			mysql_free_result($result);
			return $all;
		}
		return null;
	}
	
	function queryAllWithCache($query, $type = MYSQL_BOTH, $count = -1) {
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
		$all = DBQuery::queryAll($query,$type,$count);
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
	function queryPostProcessing($query) {
		global $service;
		return (isset($service['useLegacySupport']) && $service['useLegacySupport'] == true ? preg_replace(array("/ owner/","/.owner/"),array(" blogid",".blogid"),$query) : $query);

	}

	/*@static@*/
	function query($query) {
		$query = DBQuery::queryPostProcessing($query);
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = mysql_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = mysql_query($query);
		}
		if( stristr($query, 'update ') ||
			stristr($query, 'insert ') ||
			stristr($query, 'delete ') ||
			stristr($query, 'replace ') ) {
			DBQuery::clearCache();
		}
		return $result;
	}
	
	function insertId() {
		return mysql_insert_id();
	}
	
	function escapeString($string, $link = null){
		global $__gEscapeTag;
		if($__gEscapeTag == null) {
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
}

DBQuery::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );

?>
