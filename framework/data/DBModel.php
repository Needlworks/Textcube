<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DB-related function. will be merged into DBMS unification function.
function escapeSearchString($str) {
	return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', POD::escapeString($str, null))) : $str;
}

function doesExistTable($tablename) {
	global $database;
	static $tables = array();
	if( empty($tables) ) {
		$tables = POD::tableList($database['prefix']);
	}
	
	$dbCaseInsensitive = Setting::getServiceSetting('lowercaseTableNames',null,'global');
	if($dbCaseInsensitive === null) {
		if(in_array(POD::dbms(),array('MySQL','MySQLi'))) {
			$result = POD::queryRow("SHOW VARIABLES LIKE 'lower_case_table_names'");
			$dbCaseInsensitive = ($result['Value'] == 1) ? 1 : 0;
		} else $dbCaseInsensitive = 1;
		setServiceSetting('lowercaseTableNames',$dbCaseInsensitive);
	}
	if($dbCaseInsensitive == 1) $tablename = strtolower($tablename);
	if( in_array( $tablename, $tables ) ) {
		return true;
	}
	return false;
}

/* DBModel */

class DBModel extends Singleton implements IModel {
	protected $_attributes, $_qualifiers, $_query;
	protected $_relations, $_filters, $_order, $_limitation, $table, $id, $_reservedFields, $_isReserved, $param;
			
	function __construct($table = null) {
		$this->context = Model_Context::getInstance();
		$this->reset($table);
	}
	
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
		
	public function reset($table = null, $param = null) {
		if(!is_null($table)) $this->table = $this->context->getProperty('database.prefix').$table;
		else $this->table = null;
		$this->id = null;
		$this->_attributes = array();
		$this->_qualifiers = array();
		$this->_relations = array();
		$this->_filters = array();
		$this->_order = array();	
		$this->_limit = array();
		$this->_isReserved = array();
		$this->param = array();	
		$this->_reservedFields    = POD::reservedFieldNames();
		$this->_reservedFunctions = POD::reservedFunctionNames();
		if(!empty($this->_reservedFields)) {
			foreach($this->_reservedFields as $reserved) {
				$this->_isReserved[$reserved] = true;
			}
		}
		if(!empty($param)) $this->param = $param;
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
	
	public function setAttribute($name, $value, $escape = false) {
		if (is_null($value))
			$this->_attributes[$name] = null;
//			$this->_attributes[$name] = 'NULL';
		else
			$this->_attributes[$name] = ($escape === false && (!is_string($value) || in_array($value,$this->_reservedFunctions)) ? $value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
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
	
	public function setQualifier($name, $condition, $value = null, $escape = false) {
	//OR, setQualifier(string(name_condition_value), $escape = null)     - Descriptive mode (NOT implemented)
		if (is_null($condition)) {
			$this->_qualifiers[$name] = null;
//			$this->_qualifiers[$name] = 'NULL';
		} else {
			switch(strtolower($condition)) {
				case 'equals':
				case 'eq':
					$this->_relations[$name] = '=';
					break;
				case 'not':
				case 'neq':
					$this->_relations[$name] = '<>';
					break;
				case 'bigger':
				case 'b':
				case '>':
					$this->_relations[$name] = '>';
					break;
				case 'smaller':
				case 's':
				case '<':
					$this->_relations[$name] = '<';
					break;
				case 'bigger or same':
				case 'beq':
				case '>=':
					$this->_relations[$name] = '>=';
					break;
				case 'smaller or same':
				case 'seq':
				case '<=':
					$this->_relations[$name] = '<=';
					break;
				case 'like':
				default:
					$this->_relations[$name] = 'LIKE';
			}
			if($name == 'blogid') {	// Legacy support for plugins (with string-type blogid)
				$this->_qualifiers[$name] = intval($value);
			} else {
				$this->_qualifiers[$name] = ($escape === false && (!is_string($value) || in_array($value,$this->_reservedFunctions)) ? 
					$value : ($escape ? '\'' . 
						POD::escapeString(
							(($this->_relations[$name] == 'LIKE') ? '%'.$value.'%' : $value)
						) . 
				'\'' : "'" . $value . "'"));
			}
		}
	}
	
	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
		unset($this->_relations[$name]);
	}

	public function setOrder($standard, $order = 'ASC') {
		$this->_order['attribute'] = $standard;
		if(!in_array(strtoupper($order), array('ASC','DESC'))) $order = 'ASC';
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
		return POD::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
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
		$pairs = $attributes;
		foreach($pairs as $key => $value) if (is_null($value)) $pairs[$key] = 'NULL';
		
		$this->_query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $this->_capsulateFields(array_keys($attributes))) . ') VALUES (' . implode(',', $pairs) . ')';

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
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) . '=' . 
				(is_null($value) ? ' NULL' : $value ));
		
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
		$pairs = $attributes;
		foreach($pairs as $key => $value) if (is_null($value)) $pairs[$key] = 'NULL';
		$attributeFields = $this->_capsulateFields(array_keys($attributes));
		if (in_array(POD::dbms(), array('MySQL','MySQLi'))) { // Those supports 'REPLACE'
			$this->_query = 'REPLACE INTO ' . $this->table . ' (' . implode(',', $attributeFields) . ') VALUES(' . implode(',', $pairs) . ')';
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

	/// To use create() method, $this->structure variable must be defined.
	public function create() {
		if(!isset($this->structure) || empty($this->structure) || !is_array($this->structure)) return false;
		/// TO DO : implementing create method by structure
		$sql = "CREATE ".$this->table." (".CRLF;	
	
		foreach($this->structure as $field => $attributes) {
			$sql .= $field;
			$type = $length = $isNull = $default = "";
			foreach($attributes as $attr => $value) {
				if($attr == "type") {	// Type casting
					$type = POD::fieldType($type);
				}
				if($attr == "isNull") {
					$isNull = $value;	
				}
				if($attr == "default") {
					$default = $value;	
				}
			}
			$sql .= ' '.$type.(!empty($length) ? "(".$length.")" : "")
				.' '.($default ? 'DEFAULT '.(in_array($type, array("integer","timestamp","float")) ? $default : '"'.$default.'"') : "")
				.' '.($isNull ? "NULL" : "NOT NULL")
				.CRLF;
		}
		$sql .= ")";
		return POD::execute($sql);
	}
	
	public function discard() {
		$this->_query = 'DROP '. $this->table;
		if(POD::query($this->_query))
			return true;
		return false;	
	}
	
	protected function _makeWhereClause() {
		$clause = '';
		
		foreach ($this->_qualifiers as $name => $value) {
			$clause .= (strlen($clause) ? ' AND ' : '') . 
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) .
				' '.(is_null($value) ? ' IS NULL' : $this->_relations[$name] . ' ' . $value);
		}
		if(!empty($this->_order)) $clause .= ' ORDER BY '.$this->_treatReservedFields($this->_order['attribute']).' '.$this->_order['order'];
		if(!empty($this->_limit)) $clause .= ' LIMIT '.$this->_limit['count'].' OFFSET '.$this->_limit['offset'];
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}

	protected function _treatReservedFields($fields) {
		if(empty($this->_reservedFields)) return $fields;
		else {
			$requestedFields = explode(',',str_replace(' ','',$fields));
			return implode(',',$this->_capsulateFields($requestedFields));
		}
	}

	protected function _capsulateFields($requestedFieldArray) {
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
