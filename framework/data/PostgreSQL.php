<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
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
		$sql = "host=".$database['server'];
		if(isset($database['port'])) $sql .= " port=".$database['port'];
		$sql .= "user=".$database['username']." password=".$database['password']." dbname=".$database['database'];
		
		pg_connect($sql);

		if (Data_IAdapter::query('SET CHARACTER SET utf8'))
			$database['utf8'] = true;
		else
			$database['utf8'] = false;
		@Data_IAdapter::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		return true;
	}
	
	/*@static@*/
	function queryExistence($query) {
		if ($result = Data_IAdapter::query($query)) {
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
		$count = 0;
		if ($result = Data_IAdapter::query($query)) {
			$count = pg_num_rows($result);
			pg_free_result($result);
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
			$result = Data_IAdapter::queryAllWithCache($query, $type);
		} else {
			$result = Data_IAdapter::queryAll($query, $type);
		}
		if( empty($result) ) {
			return null;
		}
		return $result[0][$field];
	}
	
	/*@static@*/
	function queryRow($query, $type = MYSQL_BOTH, $useCache=true) {
		if( $useCache ) {
			$result = Data_IAdapter::queryAllWithCache($query, $type, 1);
		} else {
			$result = Data_IAdapter::queryAll($query, $type, 1);
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
		if ($result = Data_IAdapter::query($query)) {
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
	function queryAll($query, $type = MYSQL_BOTH, $count = -1) {
		$all = array();
		if ($result = Data_IAdapter::query($query)) {
			while ( ($count-- !=0) && $row = pg_fetch_array($result, $type))
				array_push($all, $row);
			pg_free_result($result);
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
		$all = Data_IAdapter::queryAll($query,$type,$count);
		$cachedResult[$cacheKey] = array( 1, $all );
		return $all;
	}
	
	/*@static@*/
	function execute($query) {
		return Data_IAdapter::query($query) ? true : false;
	}

	/*@static@*/
	function queryPostProcessing($query) {
		global $service;
		return (isset($service['useLegacySupport']) && $service['useLegacySupport'] == true ? preg_replace(array("/ owner/","/.owner/"),array(" blogid",".blogid"),$query) : $query);

	}

	/*@static@*/
	function query($query) {
		$query = Data_IAdapter::queryPostProcessing($query);
		if( function_exists( '__tcSqlLogBegin' ) ) {
			__tcSqlLogBegin($query);
			$result = pg_query($query);
			__tcSqlLogEnd($result,0);
		} else {
			$result = pg_query($query);
		}
		if( stristr($query, 'update ') ||
			stristr($query, 'insert ') ||
			stristr($query, 'delete ') ||
			stristr($query, 'replace ') ) {
			Data_IAdapter::clearCache();
		}
		return $result;
	}
	
	function insertId() {
		return pg_insert_id();
	}
	
	function escapeString($string, $link = null){
		global $__gEscapeTag;
		if(is_null($__gEscapeTag)) {
			if (function_exists('mysql_real_escape_string') && (mysql_real_escape_string('ㅋ') == 'ㅋ')) {
				$__gEscapeTag = 'real';
			} else {
				$__gEscapeTag = 'none';
			}
		} else {
			if($__gEscapeTag == 'real') {
				return is_null($link) ? mysql_real_escape_string($string) : mysql_real_escape_string($string, $link);
			} else {
				return mysql_escape_string($string);
			}
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

Data_IAdapter::cacheLoad();
register_shutdown_function( array('DBQuery','cacheSave') );

?>
