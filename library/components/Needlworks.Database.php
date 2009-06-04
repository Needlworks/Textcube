<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Common database I/O routine.
   Dependency : Needlworks.DBMS.{DBMS name}.php
   ROOT should be defined.
*/

/* OLD DBMS HANDLER LOADING PART. WILL BE DELETED SOON.
   
global $service, $database;
if (!isset($database['dbms'])) $database['dbms'] = 'MySQL';
//if (!isset($database['dbms'])) $database['dbms'] = 'PostgreSQL';

//Database Binding
switch($database['dbms']) {
	case 'PostgreSQL':
		requireComponent('Needlworks.DBMS.PostgreSQL'); break;
	case 'mysqli':
		requireComponent('Needlworks.DBMS.MySQLi');     break;
	case 'Cubrid':
		require_once ROOT.'/library/components/Needlworks.DBMS.Cubrid.php';
		break;
	case 'MySQL':
	default:
		requireComponent('Needlworks.DBMS.MySQL');
}*/

//if(!class_exists('POD')) requireComponent('POD.Core.Legacy'); //1.6 Only uses legacy routine of POD. Will be changed from 1.6.1. (or later)

/* TableQuery */
// class TableQuery will be depreacted after 1.6 tree.
// (Will be replaced to POD Framework)

class TableQuery {
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
			$this->_attributes[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
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
			$this->_qualifiers[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
	}
	
	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
	}
	
	public function doesExist() {
		return POD::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getCell($field = '*') {
		return POD::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getRow($field = '*') {
		return POD::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function getColumn($field = '*') {
		return POD::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getAll($field = '*') {
		return POD::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function insert() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'INSERT INTO ' . $this->table . ' (' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (POD::query($this->_query)) {
//			$this->id = POD::insertId();
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
		if (POD::query($this->_query))
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
		if (in_array(POD::dbms(), array('MySQL','MySQLi'))) { 
			$this->_query = 'REPLACE INTO ' . $this->table . ' (' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
			if (POD::query($this->_query)) {
				$this->id = POD::insertId();
				return true;
			}
			return false;
		} else {
			$this->_query = 'SELECT * FROM ' . $this->table . $this->_makeWhereClause();
			if(POD::queryCount($this->_query) > 0) {
				return $this->update();
			} else {
				return $this->insert();
			}
		}
	}
	
	public function delete() {
		if (empty($this->table))
			return false;
		$this->_query = 'DELETE FROM ' . $this->table . $this->_makeWhereClause();
		if (POD::query($this->_query))
			return true;
		return false;
	}
	
	private function _makeWhereClause() {
		$clause = '';
		foreach ($this->_qualifiers as $name => $value)
			$clause .= (strlen($clause) ? ' AND ' : '') . ''. $name . '=' . $value;
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}
}
?>
