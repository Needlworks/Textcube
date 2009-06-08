<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Common database I/O routine.
   Dependency : Needlworks.DBMS.{DBMS name}.php
   ROOT should be defined.
*/
global $service, $database;
if (!isset($database['dbms'])) $database['dbms'] = 'MySQL';
//if (!isset($database['dbms'])) $database['dbms'] = 'PostgreSQL';
//Database Binding
switch($database['dbms']) {
	case 'PostgreSQL':
		require_once ROOT.'/library/components/Needlworks.DBMS.PostgreSQL.php';
		break;
	case 'Cubrid':
		require_once ROOT.'/library/components/Needlworks.DBMS.Cubrid.php';
		break;
	case 'MySQL':
	default:
		require_once ROOT.'/library/components/Needlworks.DBMS.MySQL.php';
}

//if(!class_exists('POD')) require_once ROOT.'/components/POD.Core.php';
if(!class_exists('POD')) require_once ROOT.'/library/components/POD.Core.Legacy.php'; //1.6 Only uses legacy routine of POD. Will be changed from 1.8 (or later)

/* TableQuery */
// class TableQuery will be depreacted after 1.8 tree.
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
		$this->_relations = array();
		$this->_filters = array();
		$this->_reservedFields = POD::reservedFieldNames();
		if(!empty($this->_reservedFields)) {
			foreach($this->_reservedFields as $reserved) {
				$this->_isReserved[$reserved] = true;
			}
		} else {
			$this->_isReserved = array();
		}
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
		$this->_relations = array();
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
	
	function setQualifier($name, $condition, $value = null, $escape = null) {
	//OR, setQualifier($name, $value, $escape = null) - Legacy mode 
	//OR, setQualifier(string(name_condition_value), $escape = null)     - Descriptive mode (NOT implemented)
	//OR, setQualifier($name, NULL)
		if (is_null($condition)) {
			$this->_qualifiers[$name] = 'NULL';
		} else {
			if(!is_null($escape) && in_array(strtolower($condition), array('equals','not','like'))) { 	// Legacy mode
				$escape = $value;
				$value = $condition;
				$condition = null;
			}
			if(is_null($condition)) {
				$this->_qualifiers[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
				$this->_relations[$name] = '=';
			} else {
				switch(strtolower($condition)) {
					case 'equals':
						$this->_relations[$name] = '=';
						break;
					case 'not':
						$this->_relations[$name] = 'NOT';
						break;
					case 'like':
					default:
						$this->_relations[$name] = 'LIKE';
				}
				$this->_qualifiers[$name] = (is_null($escape) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
			}
		}
	}
	
	function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
		unset($this->_relations[$name]);
	}
	
	function doesExist() {
		return POD::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getCell($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getRow($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function getColumn($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getAll($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function insert() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $this->_capsulateFields(array_keys($attributes))) . ') VALUES(' . implode(',', $attributes) . ')';
		if (POD::query($this->_query)) {
//			$this->id = POD::insertId();
			return true;
		}
		return false;
	}
	
	function update() {
		if (empty($this->table) || empty($this->_attributes))
			return false;
		$attributes = array();
		foreach ($this->_attributes as $name => $value)
			array_push($attributes, 
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) . '=' . $value);
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
		$attributeFields = $this->_capsulateFields(array_keys($attributes));
		if (in_array(POD::dbms(), array('MySQL','MySQLi'))) { // Those supports 'REPLACE'
			$this->_query = 'REPLACE INTO ' . $this->table . ' (' . implode(',', $attributeFields) . ') VALUES(' . implode(',', $attributes) . ')';
			if (POD::query($this->_query)) {
				$this->id = POD::insertId();
				return true;
			}
			return false;
		} else {
			$this->_query = 'SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1';
			if(POD::queryExistence($this->_query)) {
				return $this->update();
			} else {
				return $this->insert();
			}
		}
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
			$clause .= (strlen($clause) ? ' AND ' : '') . 
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) .
				' '.$this->_relations[$name] . ' ' . $value;
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}

	function _treatReservedFields($fields) {
		if(empty($this->_reservedFields)) return $fields;
		else {
			$requestedFields = explode(',',str_replace(' ','',$fields));
			return implode(',',$this->_capsulateFields($requestedFields));
		}
	}

	function _capsulateFields($requestedFieldArray) {
		$escapedFields = array();
		foreach ($requestedFieldArray as $req) {
			if(array_key_exists($req,$this->_isReserved)) {
				array_push($escapedFields, '"'.$req.'"');
			} else {
				array_push($escapedFields,$req);
			}
		}
		return $escapedFields;
	}
}
?>
