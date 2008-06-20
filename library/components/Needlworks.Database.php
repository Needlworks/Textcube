<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Common database I/O routine.
   Dependency : Needlworks.DBMS.{DBMS name}.php
   ROOT should be defined.
*/
if (!isset($service['dbms'])) $service['dbms'] = 'mysql';
//Database Binding
switch($service['dbms']) {
	case 'postgresql':
		require_once ROOT.'/library/components/Needlworks.DBMS.PostgreSQL.php';
		break;
	case 'mysql':
	default:
		require_once ROOT.'/library/components/Needlworks.DBMS.MySQL.php';
}

//if(!class_exists('POD')) require_once ROOT.'/components/POD.Core.php';
if(!class_exists('POD')) require_once ROOT.'/library/components/POD.Core.Legacy.php'; //1.6 Only uses legacy routine of POD. Will be changed from 1.6.1. (or later)

/* TableQuery */
// class TableQuery will be depreacted after 1.6 tree.
// (Will be replaced to POD Framework)

class TableQuery {
	function TableQuery($table = null) {
		$this->reset($table);
	}
	
	function reset($table = null) {
		$this->table = $table;
		$this->id = null;
		$this->_attributes = array();
		$this->_qualifiers = array();
	}
	
	function resetAttributes() {
		$this->_attributes = array();
	}
	
	function getAttributesCount() {
		return count($this->_attributes);
	}
	
	function hasAttribute($name) {
		return isset($this->_attributes[$name]);
	}
	
	function getAttribute($name) {
		return $this->_attributes[$name];
	}
	
	function setAttribute($name, $value, $escape = null) {
		if (is_null($value))
			$this->_attributes[$name] = 'NULL';
		else
			$this->_attributes[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
	}
	
	function unsetAttribute($name) {
		unset($this->_attributes[$name]);
	}
	
	function resetQualifiers() {
		$this->_qualifiers = array();
	}
	
	function getQualifiersCount() {
		return count($this->_qualifiers);
	}
	
	function hasQualifier($name) {
		return isset($this->_qualifiers[$name]);
	}
	
	function getQualifier($name) {
		return $this->_qualifiers[$name];
	}
	
	function setQualifier($name, $value, $escape = null) {
		if (is_null($value))
			$this->_qualifiers[$name] = 'NULL';
		else
			$this->_qualifiers[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
	}
	
	function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
	}
	
	function doesExist() {
		return POD::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getCell($field = '*') {
		return POD::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getRow($field = '*') {
		return POD::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function getColumn($field = '*') {
		return POD::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getAll($field = '*') {
		return POD::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function insert() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'INSERT INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (POD::query($this->_query)) {
			$this->id = POD::insertId();
			return true;
		}
		return false;
	}
	
	function update() {
		if (empty($this->table) || empty($this->_attributes))
			return false;
		$attributes = array();
		foreach ($this->_attributes as $name => $value)
			array_push($attributes, $name . '=' . $value);
		$this->_query = 'UPDATE ' . $this->table . ' SET ' . implode(',', $attributes) . $this->_makeWhereClause();
		if (POD::query($this->_query))
			return true;
		return false;
	}
	
	function replace() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'REPLACE INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (POD::query($this->_query)) {
			$this->id = POD::insertId();
			return true;
		}
		return false;
	}
	
	function delete() {
		if (empty($this->table))
			return false;
		$this->_query = 'DELETE FROM ' . $this->table . $this->_makeWhereClause();
		if (POD::query($this->_query))
			return true;
		return false;
	}
	
	function _makeWhereClause() {
		$clause = '';
		foreach ($this->_qualifiers as $name => $value)
			$clause .= (strlen($clause) ? ' AND ' : '') . $name . '=' . $value;
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}
}
?>
