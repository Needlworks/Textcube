<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// MMCache is instant memory cache as table type data.
// Supports same methods as POD raw mode.
class Cache_memory{
	/*var $variable;*/
	
	//Variable must be the table form. (2-dimensional recursive structure)
	function queryRow($var, $key, $value) {
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) return $row;
		}
		return false;
	}
	function queryAll($var, $key, $value) {
		$result = array();
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) array_push($result, $row);
		}
		return $result;
	}
	function queryColumn($var, $key, $value, $column){
		$result = array();
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) array_push($result, $row[$column]);
		}
		return $result;
	}
}
?>