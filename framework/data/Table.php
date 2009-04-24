<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Common database I/O routine.
   Dependency : Needlworks.DBMS.{DBMS name}.php
   ROOT should be defined.
*/

class Data_Table {
	private $_attributes, $_qualifiers, $_query;
	
	function __construct($table = null) {
		$this->reset($table);
	}
	
	public function reset($table = null) {
		$this->table = $table;
		$this->id = null;
		$this->_attributes = array();
		$this->_qualifiers = array();
	}
	
	public function resetAttributes() {
		$this->_attributes = array();
	}
	
	public function getAttributesCount() {
		return count($this->_attributes);
	}
	
	public function hasAttribute($name) {
		return isset($this->_attributes[$name]);
	}
	
	public function getAttribute($name) {
		return $this->_attributes[$name];
	}
	
	public function setAttribute($name, $value, $escape = null) {
		if (is_null($value))
			$this->_attributes[$name] = 'NULL';
		else
			$this->_attributes[$name] = (is_null($escape) ? $value : ($escape ? '\'' . Data_IAdapter::escapeString($value) . '\'' : "'" . $value . "'"));
	}
	
	public function unsetAttribute($name) {
		unset($this->_attributes[$name]);
	}
	
	public function resetQualifiers() {
		$this->_qualifiers = array();
	}
	
	public function getQualifiersCount() {
		return count($this->_qualifiers);
	}
	
	public function hasQualifier($name) {
		return isset($this->_qualifiers[$name]);
	}
	
	public function getQualifier($name) {
		return $this->_qualifiers[$name];
	}
	
	public function setQualifier($name, $value, $escape = null) {
		if (is_null($value))
			$this->_qualifiers[$name] = 'NULL';
		else
			$this->_qualifiers[$name] = (is_null($escape) ? $value : ($escape ? '\'' . Data_IAdapter::escapeString($value) . '\'' : "'" . $value . "'"));
	}
	
	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
	}
	
	public function doesExist() {
		return Data_IAdapter::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getCell($field = '*') {
		return Data_IAdapter::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getRow($field = '*') {
		return Data_IAdapter::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function getColumn($field = '*') {
		return Data_IAdapter::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getAll($field = '*') {
		return Data_IAdapter::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function insert() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'INSERT INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (Data_IAdapter::query($this->_query)) {
			$this->id = Data_IAdapter::insertId();
			return true;
		}
		return false;
	}
	
	public function update() {
		if (empty($this->table) || empty($this->_attributes))
			return false;
		$attributes = array();
		foreach ($this->_attributes as $name => $value)
			array_push($attributes, $name . '=' . $value);
		$this->_query = 'UPDATE ' . $this->table . ' SET ' . implode(',', $attributes) . $this->_makeWhereClause();
		if (Data_IAdapter::query($this->_query))
			return true;
		return false;
	}
	
	public function replace() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'REPLACE INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (Data_IAdapter::query($this->_query)) {
			$this->id = Data_IAdapter::insertId();
			return true;
		}
		return false;
	}
	
	public function delete() {
		if (empty($this->table))
			return false;
		$this->_query = 'DELETE FROM ' . $this->table . $this->_makeWhereClause();
		if (Data_IAdapter::query($this->_query))
			return true;
		return false;
	}
	
	private function _makeWhereClause() {
		$clause = '';
		foreach ($this->_qualifiers as $name => $value)
			$clause .= (strlen($clause) ? ' AND ' : '') . $name . '=' . $value;
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}
}
?>
