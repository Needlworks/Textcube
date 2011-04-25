<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
		Setting::setServiceSetting('lowercaseTableNames',$dbCaseInsensitive,true);
	}
	if($dbCaseInsensitive == 1) $tablename = strtolower($tablename);
	if( in_array( $tablename, $tables ) ) {
		return true;
	}
	return false;
}

/* DBModel */
/* 1.4.1.20110323 */
class DBModel extends Singleton implements IModel {
	protected $_attributes, $_qualifiers, $_query;
	protected $_relations, $_glues, $_filters, $_order, $_limitation, $table, $id, $_querysetCount;
	protected $_reservedFields, $_isReserved, $param;
	
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
		$this->_glues = array();
		$this->_filters = array();
		$this->_order = array();	
		$this->_limit = array();
		$this->_isReserved = array();
		$this->param = array();	
		$this->_querysetCount = 0;
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
	
	public function setQualifier($name, $condition, $value = null, $escape = false, $autoquote = true) {
		$result = $this->getQualifierModel($name, $condition, $value, $escape, $autoquote);
		if($result) {
			list($this->_qualifiers[$name],$this->_relations[$name]) = $result;
		}
	}
	
	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
		unset($this->_relations[$name]);
	}

	public function setQualifierSet() {
		$nargs = func_num_args();
		if ($nargs % 2 != 1) return false;
		$args = func_get_args();
		$mqualifier = array();
		$mrelation = array();
		$mglue = array();
		for($i = 0; $i < $nargs; $i += 1) {
			if($i % 2 == 0) {
				$name = $args[$i][0];
				$condition = $args[$i][1];
				$value = $args[$i][2];
				if(isset($args[$i][3])) $escape = $args[$i][3];
				else $escape = null;
				list($qualifier, $relation) = $this->getQualifierModel($name, $condition, $value, $escape);
				$mqualifier[$name] = $qualifier;
				$mrelation[$name] = $relation;
			} else {
				$mglue[$name] = $args[$i];
			}
		}
		$this->_qualifiers['QualifierSet'.$this->_querysetCount] = $mqualifier; 
		$this->_relations['QualifierSet'.$this->_querysetCount] = $mrelation;
		$this->_glues['QualifierSet'.$this->_querysetCount] = $mglue;
		$this->_querysetCount += 1;
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
	
	public function doesExist($field = '*') {
		return POD::queryExistence('SELECT '. $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
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

	public function getCount($field = '*') { /// Returns the 'selection count'
		$field = $this->_treatReservedFields($field);
		return POD::queryCell('SELECT COUNT(' . $field . ') FROM ' . $this->table . $this->_makeWhereClause());
//		return POD::queryCount('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
		
	public function insert($option = null) {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$pairs = $attributes;
		foreach($pairs as $key => $value) if (is_null($value)) $pairs[$key] = 'NULL';
		
		$this->_query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $this->_capsulateFields(array_keys($attributes))) . ') VALUES (' . implode(',', $pairs) . ')';
		if($option == 'count') return POD::queryCount($this->_query);
		if (POD::query($this->_query)) {
//			$this->id = POD::insertId();
			return true;
		}
		return false;
	}
	
	public function update($option = null) {
		if (empty($this->table) || empty($this->_attributes))
			return false;
		$attributes = array();
		
		foreach ($this->_attributes as $name => $value)
			array_push($attributes, 
				(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) . '=' . 
				(is_null($value) ? ' NULL' : $value ));
		
		$this->_query = 'UPDATE ' . $this->table . ' SET ' . implode(',', $attributes) . $this->_makeWhereClause();
		if($option == 'count') return POD::queryCount($this->_query);
		if (POD::query($this->_query))
			return true;
		return false;
	}
	
	public function replace($option = null) {
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
			if($option == 'count') return POD::queryCount($this->_query);
			if (POD::query($this->_query)) {
				$this->id = POD::insertId();
				return true;
			}
			return false;
		} else {
			$this->_query = 'SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1';
			if(POD::queryExistence($this->_query)) {
				return $this->update($option);
			} else {
				return $this->insert($option);
			}
		}
	}
	
	public function delete($count = null, $option = null) {
		if (empty($this->table))
			return false;
		if(!is_null($count)) $this->setLimit($count);
		$this->_query = 'DELETE FROM ' . $this->table . $this->_makeWhereClause();
		if($option == 'count') return POD::queryCount($this->_query);
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
			if (strpos($name,'QualifierSet') === 0) {
				$clause .= (strlen($clause) ? ' AND (' : '(');
				foreach ($value as $qname => $qvalue) {
					list($qrelations, $qvalue) = $this->_canonicalWhereClause($this->_relations[$name][$qname],$qvalue);
					$clause .= (array_key_exists($qname, $this->_isReserved) ? '"'.$qname.'"' : $qname) .
					' '.(is_null($qvalue) ? ' IS NULL' : $qrelations . ' ' . $qvalue)
					.(isset($this->_glues[$name][$qname]) ? ' '.$this->_glues[$name][$qname].' ' : ''); 
				}
				
				$clause .= ')'; 
			} else {
				list($relations, $value) = $this->_canonicalWhereClause($this->_relations[$name],$value);
				$clause .= (strlen($clause) ? ' AND ' : '') . 
					(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) .
					' '.(is_null($value) ? ' IS NULL' : $relations . ' ' . $value);
			}
		}
		
		if(!empty($this->_order)) $clause .= ' ORDER BY '.$this->_treatReservedFields($this->_order['attribute']).' '.$this->_order['order'];
		if(!empty($this->_limit)) $clause .= ' LIMIT '.$this->_limit['count'].' OFFSET '.$this->_limit['offset'];
		return (strlen($clause) ? ' WHERE ' . $clause : '');
	}
	
	protected function _canonicalWhereClause($relations, $value) {
		if(in_array($relations,array('hasoneof','hasanyof','hasnoneof'))) {
			switch($relations) {
				case 'hasoneof':
					$relations = ' IN';
					break; 
				case 'hasanyof':
				case 'hasnoneof':
				default:
					$relations = ' NOT IN';
					break;
			}
			if(is_array($value)) {
				$value = implode(',',$value);
			}
			$value = '('.$value.')';	
		}
		return array($relations, $value);		
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
	
	protected function getQualifierModel($name, $condition, $value = null, $escape = false, $autoquote = true) {
	//OR, setQualifier(string(name_condition_value), $escape = null)     - Descriptive mode (NOT implemented)
		if (is_null($condition)) {
			$qualifiers = $relations = null;
		} else {
			switch(strtolower($condition)) {
				case 'equals':
				case 'eq':
					$relations = '=';
					break;
				case 'not':
				case 'neq':
					$relations = '<>';
					break;
				case 'bigger':
				case 'b':
				case '>':
					$relations = '>';
					break;
				case 'smaller':
				case 's':
				case '<':
					$relations = '<';
					break;
				case 'bigger or same':
				case 'beq':
				case '>=':
					$relations = '>=';
					break;
				case 'smaller or same':
				case 'seq':
				case '<=':
					$relations = '<=';
					break;
				case 'hasoneof':
				case 'hasanyof':
				case 'hasnoneof':
					$relations = strtolower($condition);
					break;
				case 'like':
				default:
					$relations = 'LIKE';
			}
			if(in_array($name,array('blogid','userid'))) {	// Legacy support for plugins (with string-type blogid)
				$qualifiers = intval($value);
			} else if (in_array(strtolower($condition),array('hasoneof','hasanyof','hasnoneof'))) {
				if($escape !== false) {
					$escapedCandidates = array();
					if(is_array($value)) {
						foreach ($value as $c) {
							array_push($escapedCandidates,'\''.POD::escapeString($c).'\'');
						}
					} else array_push($escapedCandidates,$value); 
					$value = $escapedCandidates;
				}
				$qualifiers = $value;
			} else {
				$qualifiers = ($escape === false && (!is_string($value) || in_array($value,$this->_reservedFunctions) || $autoquote == false) ? 
					$value : ($escape ? '\'' . 
						POD::escapeString(
							(($relations == 'LIKE') ? '%'.$value.'%' : $value)
						) . 
				'\'' : "'" . $value . "'"));
			}
		}
		return array($qualifiers, $relations);
	}
}
?>
