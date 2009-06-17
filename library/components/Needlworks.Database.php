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

//if(!class_exists('POD')) requireComponent('POD.Core.Legacy'); //1.6 Only uses legacy routine of POD. Will be changed from 1.8. (or later)

/* TableQuery */
// class TableQuery will be depreacted after 1.8 tree.
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
		$this->_relations = array();
		$this->_filters = array();
		$this->_order = array();	
		$this->_limitation = array();	
		$this->_reservedFields = POD::reservedFieldNames();
		if(!empty($this->_reservedFields)) {
			foreach($this->_reservedFields as $reserved) {
				$this->_isReserved[$reserved] = true;
			}
		} else {
			$this->_isReserved = array();
		}
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
		$this->_relations = array();
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
	
	public function setQualifier($name, $condition, $value = null, $escape = null) {
	//OR, setQualifier($name, $value, $escape = null) - Legacy mode 
	//OR, setQualifier(string(name_condition_value), $escape = null)     - Descriptive mode (NOT implemented)
	//OR, setQualifier($name, NULL)
		if (is_null($condition)) {
			$this->_qualifiers[$name] = 'NULL';
		} else {
/*			if(!is_null($escape) && in_array(strtolower($condition), array('equals','not','like'))) { 	// Legacy mode
				$escape = $value;
				$value = $condition;
				$condition = null;
			}*/
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
	
	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
		unset($this->_relations[$name]);
	}

	public function setOrder($standard, $order = 'ASC') {
		$this->_order['attribute'] = $standard;
		if(!in_array(strtoupper($order, array('ASC','DESC')))) $order = 'ASC';
		$this->_order['order'] = $order;
	}

	public function unsetOrder() {
		$this->_order = array();	
	}
	
	public function setLimit($count, $offset = 0) {
		$this->_limit['count'] = $count;
		$this->_limit['offset'] = $offset;
	}

	public function unsetLimit() {
		$this->_limit = array();	
	}
	
	public function doesExist() {
		return POD::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getCell($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getRow($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function getColumn($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	public function getAll($field = '*') {
		$field = $this->_treatReservedFields($field);
		return POD::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	public function insert() {
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
	
	public function update() {
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
	
	public function replace() {
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
		foreach ($this->_qualifiers as $name => $value) {
			$clause .= (strlen($clause) ? ' AND ' : '') . 
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) .
				' '.$this->_relations[$name] . ' ' . $value;
		}
		if(!empty($this->_order)) $clause .= ' ORDER BY '._treatReservedFields($this->_order['standard']).' '.$this->_order['order'];
		if(!empty($this->_limit)) $clause .= ' LIMIT '.$this->_limit['count'].' OFFSET '.$this->_limit['offset'];
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
