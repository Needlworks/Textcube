<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// DB-related function. will be merged into DBMS unification function.
function escapeSearchString($str) {
	return is_string($str) ? str_replace('_', '\_', str_replace('%', '\%', POD::escapeString($str, null))) : $str;
}

function doesExistTable($tablename) {
	static $tables = array();
	if( empty($tables) ) {
		$ctx = Model_Context::getInstance();
		$likeEscape = array ( '/_/' , '/%/' );
		$likeReplace = array ( '\\_' , '\\%' );
		$escapename = preg_replace($likeEscape, $likeReplace, $ctx->getProperty('database.prefix'));
		$tables = POD::tableList($escapename);
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
/* 2.3.1.20150206 */
class DBModel extends Singleton implements IModel {
	protected $_attributes, $_qualifiers, $_projections, $_query;
	protected $_relations, $_glues, $_filters, $_order, $_limit, $_statements, $_group, $table, $id, $_querysetCount;
	protected $_reservedFields, $_isReserved, $param, $_options;
	protected $_extended_objects, $_object_aliases;

	public $structure, $option;

	function __construct($table = null) {
		$this->context = Model_Context::getInstance();
		$this->reset($table);
	}

	public function reset($table = null, $param = null) {
		if(!is_null($table)) $this->table = $table;
		else $this->table = null;
		$this->id = null;
		$this->_attributes = array();
		$this->_qualifiers = array();
		$this->_relations = array();
		$this->_projections = array();
		$this->_glues = array();
		$this->_statements = array();
		$this->_filters = array();
		$this->_order = array();
		$this->_limit = array();
		$this->_group = array();
		$this->_options = array();
		$this->_isReserved = array();
		$this->_extended_objects = array();
		$this->_object_aliases = array();
		$this->param = array();
		$this->_querysetCount = 0;
		$this->_reservedFields    = POD::reservedFieldNames();
		$this->_reservedFunctions = POD::reservedFunctionNames();
		$this->structure = null;
		$this->option = null;
		if(!empty($this->_reservedFields)) {
			foreach($this->_reservedFields as $reserved) {
				$this->_isReserved[$reserved] = true;
			}
		}
		if(!empty($param)) $this->param = $param;
		return $this;
	}

	public function init($table = null, $param = null) {
		return $this->reset($table, $param);
	}

	/// Attributes
	public function resetAttributes() {
		$this->_attributes = array();
		return $this;
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
		return $this;
	}

	/// Projections
	public function resetProjections() {
		$this->_projections = array();
		return $this;
	}

	public function setProjection() {
		$nargs = func_num_args();
		$args = func_get_args();
		if ($nargs == 1 && gettype($args[0]) == 'array' ) {
			$this->_projections = $args[0];
		} else {
			for ($i = 0; $i < $nargs; $i++) {
				if (!in_array($args[$i],$this->_projections)) {
					array_push($this->_projections, $args[$i]);
				}
			}
		}
		return $this;
	}

	public function getProjection() {
		return $this->_projections;
	}

	public function hasProjection($name) {
		return in_array($name,$this->_projections);
	}

	/// Qualifiers
	public function resetQualifiers() {
		$this->_qualifiers = array();
		$this->_relations = array();
		return $this;
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
			if (!isset($this->_qualifiers[$name])) {
				$this->_qualifiers[$name] = array();
				$this->_relations[$name] = array();
			}
			$index = count($this->_qualifiers[$name]);
			$this->_qualifiers[$name][$index] = $result[0];
			$this->_relations[$name][$index] = $result[1];
		}
		return $this;
	}

	public function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
		unset($this->_relations[$name]);
		return $this;
	}

	public function setQualifierSet() {
		$nargs = func_num_args();
		if ($nargs % 2 != 1) return false;
		$args = func_get_args();
		if ($nargs == 1 && is_array($args[0])) {
			$args = $args[0];
		}
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
		return $this;
	}

	/// Manipulators
	public function getOrder() {
		return $this->_order;
	}

	public function setOrder($standard, $order = 'ASC') {
		$this->_order['attribute'] = $standard;
		if(!in_array(strtoupper($order), array('ASC','DESC'))) $order = 'ASC';
		$this->_order['order'] = strtoupper($order);
		return $this;
	}

	public function unsetOrder() {
		$this->_order = array();
		return $this;
	}

	public function setLimit($count, $offset = 0) {
		$this->_limit['count'] = $count;
		$this->_limit['offset'] = $offset;
		return $this;
	}

	public function unsetLimit() {
		$this->_limit = array();
		return $this;
	}

	public function setGroup() {
		$nargs = func_num_args();
		$args = func_get_args();
		if ($nargs == 1 && gettype($args[0]) == 'array' ) {
			foreach($args[0] as $a) {
				$treatedArg = $this->_treatReservedFields($a);
				if (!in_array($treatedArg,$this->_group)) {
					array_push($this->_group, $treatedArg);
				}
			}
		} else {
			for ($i = 0; $i < $nargs; $i++) {
				$treatedArg = $this->_treatReservedFields($args[$i]);
				if (!in_array($treatedArg,$this->_group)) {
					array_push($this->_group, $treatedArg);
				}
			}
		}
		return $this;
	}

	public function unsetGroup() {
		$this->_group = array();
	}
	/// Extenders
	public function setAlias($table, $alias) {
		$this->_object_aliases[$table] = $alias;
		return $this;
	}

	public function getAlias($table) {
		if (array_key_exists($table,$this->_object_aliases)) {
			return $this->_object_aliases[$table];
		}
		return null;
	}

	public function join($table, $type, $relations = null) {
		$this->_extended_objects[$table] = array();
		if(!in_array(strtolower($type),array('left','inner','outer','equal','left outer','right outer'))) return false;
		$this->_extended_objects[$table]['type'] = $type;
		$args = $relations;
		$glues = array();
		if (!is_null($relations)) {
			foreach($relations as $rel) {
				$attribute1 = $rel[0];
				$condition = $rel[1];
				$attribute2 = $rel[2];
				list($dummy, $condition) = $this->getQualifierModel($attribute1, $condition, $attribute2, false, false);
				array_push($glues, $attribute1.' '.$condition.' '.$attribute2);
			}
			$this->_extended_objects[$table]['relations'] = implode(' AND ',$glues);
		}
		return $this;
	}

	public function extend($table, $type, $relation = null) {
		return $this->join($table, $type, $relation);
	}
	public function setOption($options) {
		if (is_array($options)) {
			$this->_options = $options;
			return true;
		}
		return false;
	}

	/* Selects */
	public function doesExist($field = '*', $options = null) {
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		return POD::queryExistence('SELECT '. $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause() . ' LIMIT 1');
	}

	public function getCell($field = '*', $options = null) {
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		if ($options['usedbcache'] == true) {
			return POD::queryCellWithDBCache('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause() . ' LIMIT 1',$options['cacheprefix']);
		}
		return POD::queryCell('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause() . ' LIMIT 1');
	}

	public function getRow($field = '*', $options = null) {
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		if ($options['usedbcache'] == true) {
			return POD::queryRowWithDBCache('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause(),$options['cacheprefix']);
		}
		return POD::queryRow('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause());
	}

	public function getColumn($field = '*', $options = null) {
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		if ($options['usedbcache'] == true) {
			return POD::queryColumnWithDBCache('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause(),$options['cacheprefix']);
		}
		return POD::queryColumn('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause());
	}

	public function getAll($field = '*', $options = null) {
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		if ($options['usedbcache'] == true) {
			return POD::queryAllWithDBCache('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause(),$options['cacheprefix']);
		}
		return POD::queryAll('SELECT ' . $options['filter'] . $field . ' FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause());
	}

	public function getCount($field = '*', $options = null) { /// Returns the 'selection count'
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		return POD::queryCell('SELECT '  . $options['filter'] . ' COUNT(' . $field . ') FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause());
	}

	public function getSize($field = '*', $options = null) { /// Returns the table size
		$field = $this->_treatReservedFields($field);
		$options = $this->_treatOptions($options);
		return POD::queryCell('SELECT '  . $options['filter'] . ' COUNT(*) FROM ' . $this->_getTableName() . $this->_extendClause() . $this->_makeWhereClause());
	}

	/* CRUDs */
	public function insert($option = null) {
		$this->id = null;
		if (empty($this->table))
			return false;
		// Use first qualifiers when multiple conditions exist.
		$qualifiers = array();
		if(!empty($this->_qualifiers)) {
			foreach($this->_qualifiers as $key => $index) {
				$qualifiers[$key] = reset($index);
			}
		}
		$attributes = array_merge($qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$pairs = $attributes;
		foreach($pairs as $key => $value) if (is_null($value)) $pairs[$key] = 'NULL';

		$this->_query = 'INSERT INTO ' . $this->_getTableName() . ' (' . implode(',', $this->_capsulateFields(array_keys($attributes))) . ') VALUES (' . implode(',', $pairs) . ')';
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

		$this->_query = 'UPDATE ' . $this->_getTableName() . ' SET ' . implode(',', $attributes) . $this->_makeWhereClause();
		if($option == 'count') return POD::queryCount($this->_query);
		if (POD::query($this->_query))
			return true;
		return false;
	}

	public function replace($option = null) {
		$this->id = null;
		if (empty($this->table))
			return false;
		// Use first qualifiers when multiple conditions exist.
		$qualifiers = array();
		if(!empty($this->_qualifiers)) {
			foreach($this->_qualifiers as $key=>$index) {
				$qualifiers[$key] = reset($index);
			}
		}
		$attributes = array_merge($qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$pairs = $attributes;
		foreach($pairs as $key => $value) if (is_null($value)) $pairs[$key] = 'NULL';
		$attributeFields = $this->_capsulateFields(array_keys($attributes));
		if (in_array(POD::dbms(), array('MySQL','MySQLi','SQLite3'))) { // Those supports 'REPLACE'
			$this->_query = 'REPLACE INTO ' . $this->_getTableName() . ' (' . implode(',', $attributeFields) . ') VALUES(' . implode(',', $pairs) . ')';
			if($option == 'count') return POD::queryCount($this->_query);
			if (POD::query($this->_query)) {
				$this->id = POD::insertId();
				return true;
			}
			return false;
		} else {
			$this->_query = 'SELECT * FROM ' . $this->_getTableName() . $this->_makeWhereClause() . ' LIMIT 1';
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
		$this->_query = 'DELETE FROM ' . $this->_getTableName() . $this->_makeWhereClause();
		if($option == 'count') return POD::queryCount($this->_query);
		if (POD::query($this->_query))
			return true;
		return false;
	}

	/// To use create() method, $this->structure variable must be defined.
	public function create() {
		if(!isset($this->structure) || empty($this->structure) || !is_array($this->structure)) return false;
		/// TO DO : implementing create method by structure
		$sql = "CREATE TABLE ".$this->_getTableName()." (".CRLF;
		$keys = array();
		foreach($this->structure as $field => $attributes) {
			$type = $length = $isNull = $default = "";
			foreach($attributes as $attr => $value) {
				if($attr == "type") {	// Type casting
					$type = POD::fieldType($value);
				}
				if($attr == "isNull") {
					$isNull = $value;
				}
				if($attr == "default") {
					$default = $value;
				}
				if($attr == "length") {
					$length = intval($value);
				}
				if($attr == "autoincrement") {
					$ai = $value;
				}
				if($attr == "index" && $value == true) {
					array_push($keys, $field);
				}
			}
			$sql .= $field;
			$sql .= ' '.$type.(!empty($length) ? "(".$length.")" : "")
				.' '.($default ? 'DEFAULT '.(in_array($type, array("integer","timestamp","float")) ? $default : '"'.POD::escapeString($default).'"') : "")
				.' '.($isNull ? "NULL" : "NOT NULL")
				.((isset($ai) && $ai == true) ? ' AUTO INCREMENT' : '')
				.',';
		}
		$sql = rtrim($sql,",");
		if (is_array($this->option) && array_key_exists('primary', $this->option)) {
			$sql .= ', PRIMARY KEY ('.implode(',',$this->option['primary']) .')';
		}
		foreach ($keys as $key) {
			$sql .= ', KEY ('.POD::escapeString($key).')';
		}
		$sql .= ")";
		return POD::execute($sql);
	}

	public function discard() {
		$this->_query = 'DROP '. $this->_getTableName();
		if(POD::query($this->_query))
			return true;
		return false;
	}

	protected function _getTableName($table = null) {
		if (is_null($table)) $table = $this->table;
		if (array_key_exists($table, $this->_object_aliases)) {
			return $this->context->getProperty('database.prefix').POD::escapeString($table).' '.$this->_object_aliases[$table];
		} else {
			return $this->context->getProperty('database.prefix').POD::escapeString($table);
		}
	}

	protected function _treatOptions($options) {
		$acceptedOptions = array('filter'=>'','usedbcache'=>false,'cacheprefix'=>'');
		if (empty($options)) return $acceptedOptions;
		foreach(array_keys($options) as $o) {
			if (array_key_exists(strtolower($o),$this->_options)) {
				$acceptedOptions[strtolower($o)] = $this->_options[$o];
			}
			if (array_key_exists(strtolower($o),$options)) { // Overwrite options
				$acceptedOptions[strtolower($o)] = $options[$o];
			}
		}
		return $acceptedOptions;
	}

	protected function _makeWhereClause() {
		$clause = '';
		if (count($this->_qualifiers) == 0) {
			$clause = '1';
		} else {
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
					foreach ($value as $index => $qualifier) {
						list($relations, $qvalue) = $this->_canonicalWhereClause($this->_relations[$name][$index],$qualifier);
						$clause .= (strlen($clause) ? ' AND ' : '') .
							(array_key_exists($name, $this->_isReserved) ? '"'.$name.'"' : $name) .
							' '.(is_null($qvalue) ? ' IS NULL' : $relations . ' ' . $qvalue);
					}
				}
			}
		}
		if(!empty($this->_group)) $clause .= ' GROUP BY '. implode(",",$this->_group);
		if(!empty($this->_order)) $clause .= ' ORDER BY '.$this->_treatReservedFields($this->_order['attribute']).' '.$this->_order['order'];
		if(!empty($this->_limit)) {
			$clause .= ' LIMIT '.$this->_limit['count'];
			if ($this->_limit['offset'] != 0) {
				$clause .= ' OFFSET '.$this->_limit['offset'];
			}
		}
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

	protected function _extendClause() {
		$clause = '';
		if (!empty($this->_extended_objects)) {
			foreach ($this->_extended_objects as $table => $property) {
				if ($property['type'] == 'flat') {
					$clause .= ', '.$this->context->getProperty('database.prefix').$table.' ';
				} else {
					$clause .= strtoupper($property['type']).' JOIN '.$this->context->getProperty('database.prefix').$table.' ';
				}
				if (array_key_exists($table, $this->_object_aliases) && strpos($table,' ')===false) { // When same table is attached, second table should have blank with its alias. e.g. 'example e'
					$clause .= $this->_object_aliases[$table].' ';
				}
				if (array_key_exists('relations',$property)) {
					$clause .= 'ON '.$property['relations'].' ';
				}
			}
		}
		return (strlen($clause) ? ' ' . $clause : '');
	}

	protected function _treatReservedFields($fields) {
		if (!empty($this->_projections) && $fields == '*') $fields = implode(',',$this->_projections);
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
				if($relations == 'LIKE') {
					if (substr($value,-1) != '%' && substr($value,0) != '%') {
						$value = '%'.$value.'%';
					}
				}
				$qualifiers = ($escape === false && (!is_string($value) || in_array($value,$this->_reservedFunctions) || $autoquote == false) ?
					$value : ($escape ? '\'' . POD::escapeString($value) . '\'' : "'" . $value . "'"));
			}
		}
		return array($qualifiers, $relations);
	}
}
?>
