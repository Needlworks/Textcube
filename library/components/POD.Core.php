<?php
// POD : PHP Ontology(or Object-Oriented)-based Data model/framework
// Version 0.19a
// By Jeongkyu Shin (jkshin@ncsl.postech.ac.kr)
// Created       : 2007.11.30
// Last modified : 2007.12.21

// (C) 2007 Jeongkyu Shin. All rights reserved. 
// Licensed under the GPL.
// See the GNU General Public License for more details. (/LICENSE, /COPYRIGHT)
// For more information, visit http://pod.nubimaru.com

require_once 'Needlworks.Database.php';

// Bypass variables are supported. ($_pod_setting);
class POD extends DBQuery {
	/** Pre-definition **/
	// 'var' is deprecated from PHP5.
/*	var $_domain;
	var $_prototype;
	var $_type;
	var $_version;
	var $_isProducedBy;
	var $_tablePrefix;
	var $_numberOfClasses;
	var $_classPointer;
	var $_foreignKeyId;
	var $_isCheckout;
	var $_DBMS;*/

	/** Initialization **/
	function POD($domain = null, $type = null, $prefix = '') {
		global $_pod_setting;
		if($domain != null) $this->_domain = $domain;
		else if(isset($this->domain)) $this->_domain = $this->domain;
		if($type != null) {$this->_prototype = $this->_type = $type;}
		else if(isset($this->prototype)) {$this->_prototype = $this->_type = $this->prototype;}
		if(!empty($prefix)) $this->_tablePrefix = $prefix;
		else if(isset($_pod_setting['tablePrefix'])) $this->_tablePrefix = $_pod_setting['tablePrefix'];
		else $this->_tablePrefix = '';
		if(isset($_pod_setting['DBMS'])) $this->_DBMS = $_pod_setting['DBMS'];
		$this->reset();
		// Should check bind state!
	}
	function construct() {
	}
	function destruct() {
		unset($this);
	}

	/** Definition **/
	function define($rewriteTreeOnly = false) {
		/*** TO DO : Upgrade current table schemes into newer one.***/
		if(empty($this->_version) || empty($this->_prototype)) return $this->_false();
		$clause = 'CREATE TABLE '.$this->_tablePrefix.$this->_prototype.' (';
		$clause .= 'pid integer NOT NULL,';
		foreach($this->_queue[$this->domain()][$this->type()]['instances'] as $instance => $options) {
			$clause .= $instance;
			if($options['attribute'] != null) $clause .= ' '.$options['attribute'];
			if($options['additional'] != null) $clause .= ' '.$options['additional'];
			$clause .= ',';
		}
		$clause .= 'PRIMARY KEY (pid)';
		if(!empty($this->_queue[$this->domain()][$this->type()]['indexes'])) {
			foreach($this->_queue[$this->domain()][$this->type()]['indexes'] as $indexName => $indexes) {
				$clause .= ', KEY '.$indexName.' ('.implode(',',$indexes).')';
			}
		}

		$clause = rtrim($clause, ',').')'.$this->_setCharset();
		var_dump($clause);
		if($rewriteTreeOnly != false) {
			$this->_structure[$this->domain()] = $this->_queue[$this->domain()];
			$this->_saveStructure();
			return true;
		} else if($this->execute($clause)) {
			$this->_structure[$this->domain()] = $this->_queue[$this->domain()];
			$this->_saveStructure();
			return true;
		}
		else return $this->_false();
	}
	
	function purge() {
		if(empty($this->_prototype)) return $this->_false();
		$this->execute("DROP TABLE ".$this->_tablePrefix.$this->_prototype);
		
		if(isset($this->_structure[$this->domain()][$this->type()]['subclasses'])) {
			foreach($this->_structure[$this->domain()][$this->type()]['subclasses'] as $subClass) {
				if($this->execute("DROP TABLE ".$this->_tablePrefix.$subClass)) {
					unset($this->_structure[$this->domain()][$subClass]);
				}
			}
		}
		if(isset($this->_structure[$this->domain()][$this->type()]['superclasses'])) {
			foreach($this->_structure[$this->domain()][$this->type()]['superclasses'] as $superClass) {
				$items = $this->_structure[$this->domain()][$superClass]['subclasses'];
				unset($items[$this->type()]);
				$this->_structure[$this->domain()][$superClass]['subclasses'] = $items;
			}
		}
		unset($this->_structure[$this->domain()][$this->type()]);
		$this->_saveStructure();
	}
	
	function alter() {
		return _alterStructure();
	}
	
	function reset() {
		$this->_structure = $this->_queue = $this->_loadStructure();
		$this->_instances =
		$this->_conditions =
		$this->_references =
		$this->_mergeWith =
		$this->_repository =
		$this->_aliases = array();
		$this->_numberOfClasses = $this->_classPointer = 0;
		$this->_foreignKeyId = null;
		$this->_isCheckout = false;
	}
	
	function structure() {
		return $this->_structure[$this->domain()][$this->type()];
	}
	
	function superClass() {
		if($this->type()==null) return null;
		else return $this->_structure[$this->domain()][$this->type()]['superclasses'];
	}
	
	function subClass() {
		if($this->type()==null) return null;
		else return $this->_structure[$this->domain()][$this->type()]['subclasses'];
	}
	
	function documentation() {
	}
	
	/** Attributes **/
	function domain($domain = null) {
		if(!empty($domain)) {
			$this->_domain = $domain;
			$this->reset();
		}
		if(empty($this->_domain)) return 'default';
		else return $this->_domain;
	}
	
	function type($type = null) {
		if(!empty($type)) {
			$this->_type = $type;
			$this->reset();
		}
		if(empty($this->_type)) return null;
		else return $this->_type;
	}
	
	function version($version = null) {
		if(!empty($version)) $this->_version = $version;
		if($this->type()==null) return null;
		else return $this->_structure[$this->domain()][$this->type()]['version'];
	}
	
	function setInstance($instance, $attribute = null, $additional = null) {
		$this->_queue[$this->domain()][$this->type()]['instances'][$instance] = array();
		if($attribute!=null) $this->_queue[$this->domain()][$this->type()]['instances'][$instance]['attribute'] = $attribute;
		if($additional!=null) $this->_queue[$this->domain()][$this->type()]['instances'][$instance]['additional'] = $additional;
	}

	function isProducedBy() {
	}
	
	function subClassOf($type) {
		if($this->_queue[$this->domain()][$this->type()]['superclasses'] == null)
			$this->_queue[$this->domain()][$this->type()]['superclasses'] = array();
		if($this->_queue[$this->domain()][$type]['subclasses'] == null)
			$this->_queue[$this->domain()][$type]['subclasses'] = array();
		if(array_search($type, $this->_queue[$this->domain()][$this->type()]['superclasses']) === false) {
			$item = $this->_queue[$this->domain()][$this->type()]['superclasses'];
			array_push($item, $type);
			$this->_queue[$this->domain()][$this->type()]['superclasses'] = $item;
		}
		if(array_search($type, $this->_queue[$this->domain()][$type]['subclasses']) === false) {
			$item = $this->_queue[$this->domain()][$type]['subclasses'];
			array_push($item, $this->type());
			$this->_queue[$this->domain()][$type]['subclasses'] = $item;
		}
		return true;
	}
	
	function superClassOf($type) {
		if($this->_queue[$this->domain()][$this->type()]['subclasses'] == null)
			$this->_queue[$this->domain()][$this->type()]['subclasses'] = array();
		if($this->_queue[$this->domain()][$type]['superclasses'] == null)
			$this->_queue[$this->domain()][$type]['superclasses'] = array();
		
		if(!array_search($type, $this->_queue[$this->domain()][$this->type()]['subclasses'])) {
			$item = $this->_queue[$this->domain()][$this->type()]['subclasses'];
			array_push($item, $type);
			$this->_queue[$this->domain()][$this->type()]['subclasses'] = $item;
		}
		
		if(!array_search($type, $this->_queue[$this->domain()][$type]['superclasses'])) {
			$item = $this->_queue[$this->domain()][$type]['superclasses'];
			array_push($item, $this->type());
			$this->_queue[$this->domain()][$type]['superclasses'] = $item;
		}
		return true;
	}
	
	function relation($type, $field, $matches) {
		// Every elements should exist before marking relation.
		if($this->_queue[$this->domain()][$type] == null) return $this->_false();
		if($this->_queue[$this->domain()][$this->type()] == null) return $this->_false();
		if($this->_queue[$this->domain()][$type]['instances'][$field] === null) return $this->_false();
		if($this->_queue[$this->domain()][$this->type()]['instances'][$matches] == null) return $this->_false();
		if($this->_queue[$this->domain()][$this->type()]['relations'] == null)
			$this->_queue[$this->domain()][$this->type()]['relations'] = array();
		if($this->_queue[$this->domain()][$type]['relations'] == null)
			$this->_queue[$this->domain()][$type]['relations'] = array();
		if(!isset($this->_queue[$this->domain()][$this->type()]['relations'][$type]))
			$this->_queue[$this->domain()][$this->type()]['relations'][$type] = array();
		$this->_queue[$this->domain()][$this->type()]['relations'][$type][$field] = $matches;
		if(!isset($this->_queue[$this->domain()][$type]['relations'][$this->type()]))
			$this->_queue[$this->domain()][$type]['relations'][$this->type()] = array();
		$this->_queue[$this->domain()][$type]['relations'][$this->type()][$matches] = $field;
		return true;
	}

	/** Attribute / View **/
	function instance($instances) {
		if(func_num_args() == 1) {
			$values = func_get_arg(0);
			if(array_key_exists($values, $this->_instances)) return $this->_instances[$values];
			else return null;
		}
		if(func_num_args() % 2 != 0) return $this->_false();
		for($i = 0; $i < func_num_args(); $i += 2) {
			$values = (is_numeric(func_get_arg($i+1)) ? func_get_arg($i+1) : '\''.$this->escapeString(func_get_arg($i+1)).'\'');
			$this->_instances[func_get_arg($i)] = $values;
		}
		return true;
	}
	
	function allInstances($instances = null) {
		if($instances != null) {
			$instance = explode(',',$instances);
			$column = array();
			foreach($instance as $value) {
				$column[$value] = $this->_instances[$value];
			}
			return $column;
		}
		return $this->_instances;
	}
	
	function valueType($instance) {
	}
	
	function valueCardinality($instance) {
		if(empty($this->_repository) || empty($this->_repository[0][$instance])) return null;
		if($this->_numberOfClasses == 0) return $this->_false();
		$column = array();
		foreach($this->_repository as $class) {
			array_push($column, $class[$instance]);
		}
		return count(array_count_values($column));
	}
	
	function cardinality() {
		return $this->_numberOfClasses;
	}
	
	function allValues($instance = null){
		if($instance == null) return $this->_repository;
		if(!$this->_validateInstance($instance)) return $this->_false();
		$column = array();
		foreach($this->_repository as $repos) {
			if($repos[$instance] == null && $this->aliases[$instance] != null)
				array_push($column, $repos[$this->aliases[$instance]]);
			else array_push($column, $repos[$instance]);
		}
		return $column;
	}
	
	function hasValue($instances) {
	}
	
	function hasAttribute() {
	}
	
	function hasOneOf() {
	}
	
	function onto() {
	}
	
	function totalOn() {
	}

	function setType() {
	}
	
	function commit() {
		if(empty($this->_prototype)) return $this->_false();
		if($this->_isCheckout == true) {	// Update case.
			$clause = "UPDATE ".$this->_tablePrefix.$this->_prototype.$this->_updateClause();
			var_dump($clause);
			return $this->execute($clause);
		}
		// If DBMS does not support foreign key, simulate it.
		if(isset($this->_structure[$this->domain()][$this->type()]['superclasses'])) {
			$superclass = $this->_structure[$this->domain()][$this->type()]['superclasses'][0];
			$relations = $this->_structure[$this->domain()][$this->type()]['relations'][$superclass];
			$clause = "SELECT pid FROM ".$this->_tablePrefix.$superclass." WHERE ";
			foreach($relations as $super => $myself) {
				$clause .= $super.'='.$this->_instances[$myself].' AND ';
			}
			$clause = rtrim($clause,'AND ');
			$this->_foreignKeyId = $this->queryCell($clause);
		}
		$clause = "INSERT INTO ".$this->_tablePrefix.$this->_prototype.$this->_insertClause();
		var_dump($clause);
		return $this->execute($clause);
	}
	
	function checkout($instances = '*') {
		if(empty($this->_prototype)) return $this->_false();
		if($instances != '*') {
			$fields = explode(',',$instances);
			$column = array();
			foreach($fields as $field) {
				if($this->_aliases[$field] != null) {
					switch($this->_DBMS) {
					case 'MySQL' :
						array_push($column, $field.' AS '.$this->_aliases[$field]);
						break;
					default :
						array_push($column, $field.' '.$this->_aliases[$field]);
						break;
					}
				} else {
					array_push($column, $field);
				}
			}
			$instances = implode(',',$column);
			unset($column);
		}

		$clause = "SELECT ".$instances." FROM ".$this->_tablePrefix.$this->_prototype.' '.$this->_prototype;
		if(!empty($this->_mergeWith)) {
			foreach($this->_mergeWith as $name => $condition) {
				$clause .=','.$this->_tablePrefix.$name.' '.$name;
			}
		}
		if(isset($this->_references)) {
			foreach($this->_references as $name => $condition) {
				if(!empty($condition)) {
					$clause .=' LEFT JOIN '.$this->_tablePrefix.$name.' '.$name.' ON '.implode(' AND ',$condition);
				}
			}
		}
		$clause .= " WHERE ".$this->_clause();
		var_dump($clause);
		switch($this->_DBMS) {
		case "PostgreSQL":
			$this->_repository = $this->queryAllWithCache($clause,'assoc');
			break;
		case "MySQL":
			$this->_repository = $this->queryAllWithCache($clause,'assoc');
			break;
		default:
	    	$this->_repository = $this->queryAllWithCache($clause);
		}
		$this->_numberOfClasses = count($this->_repository);
		if($this->_numberOfClasses > 0) {
			foreach($this->_repository[0] as $instance => $value) {
				$this->_instances[$instance] = $value;
			}
			$this->_classPointer = 0;
			$this->_isCheckout = true;
			$this->_foreignKeyId = $this->_instances['pid'];
			return true;
		}
		return $this->_false();
	}
	
	function remove() {
		if(empty($this->_prototype)) return $this->_false();
		// Find field pairs.
		$clause = "SELECT pid FROM ".$this->_tablePrefix.$this->_prototype." WHERE ".$this->_clause();
		$targetFid = $this->queryCell($clause);
		$clause = "DELETE FROM ".$this->_tablePrefix.$this->_prototype;
		$clause .= " WHERE ".$this->_clause();
		var_dump($clause);
		if($this->execute($clause)) {
			if($this->_structure[$this->domain()][$this->type()]['subclasses']) {
				foreach($this->_structure[$this->domain()][$this->type()]['subclasses'] as $subclass) {
					$this->execute("DELETE FROM ".$this->_tablePrefix.$subclass." WHERE pid = ".$targetFid);
				}
			}
			return true;
		}
		return $this->_false();
	}
		
	
	/** Relations **/
	function rangeOf($instance, $range) {
		array_push($this->_conditions, $instance.$range);
		return true;
	}
	
	function relatedTo($instance1, $instance2, $relation = '=') {
		switch(strtolower($relation)) {
			case 'req': $relation = '=<'; break;
			case 'leq': $relation = '>='; break;
			case 'eq': $relation = '='; break;
			default: break;
		}
		array_push($this->_conditions, $instance1.$relation.$instance2);
		return true;
	}
	
	function slotCardinality() {
	}
	
	function classPartition() {
	}
	
	function sameSlotValues() {
	}

	/** Extensions **/
	function merge($type) {
		if(!$this->_validateType($type)) return $this->_false();
		$this->_mergeWith[$type] = true; // TO DO : Need to validate $relation.
		return true;
	}
	
	function refer($type, $condition = null) {
		if(!$this->_validateType($type)) return $this->_false();
		$this->_references[$type] = true;
		if($condition != null) $this->restrict($type, $condition);
		return true;
	}
	
	function duplicate() {
	}
	
	function restrict($type, $condition = null) {
		if(!$this->_validateType($type)) return $this->_false();
		if($this->_references[$type] == null) return $this->_false();
		if($condition == null) {$this->_references[$type] = true; return $this->_false();}
		if($this->_references[$type] === true || empty($this->_references[$type])) $item = array();
		else $item = $this->_references[$type];
		foreach(explode(',',$condition) as $cond) { array_push($item, $cond);}
		$this->_references[$type] = $item;
		return true;
	}
	
	function projection() {
	}
	
	function composition() {
	}
	
	function shift() {
		if(($this->_classPointer+1) >= $this->_numberOfClasses) {
			return $this->_false(); // End of pointer.
		}
		$this->_classPointer += 1;
		foreach($this->_repository[$this->_classPointer] as $instance => $value) {
			$this->_instances[$instance] = $value;
		}
		return true;
	}
	
	function unshift() {
		if(($this->_classPointer-1) < 0) return $this->_false(); // End of pointer.
		$this->_classPointer -= 1;
		foreach($this->_repository[$this->_classPointer] as $instance => $value) {
			$this->_instances[$instance] = $value;
		}
		return true;
	}

	function reverse() {
	}
	
	function sort() {
	}

	function range() {
	}
	function alias($instance, $alias) {
//		if(!$this->_validateInstance($instance)) return $this->_false();
		$this->_aliases[$instance] = $alias;
		return true;
	}

	/** subroutine part **/
	/** They should be 'private', however PHP4 does not support private.
	    To support PHP4, I left them as public with prefix '_' **/

	function _loadStructure() {
		if(empty($this->_structure)) {
			$structure = $this->queryCell("SELECT value FROM ".$this->_tablePrefix."opd WHERE name = 'structure'");
			if(!empty($structure)) $this->_structure = unserialize($structure);
			else {
				$this->_structure = array();
			}
		}
		return $this->_structure;
	}

	function _saveStructure() {
		if($this->execute("REPLACE INTO ".$this->_tablePrefix."opd SET name = 'structure', value = '".serialize($this->_structure)."'") == false) {
			$this->execute("CREATE TABLE ".$this->_tablePrefix."opd (name varchar(32) NOT NULL DEFAULT '', value text NOT NULL, PRIMARY KEY (name)) TYPE=MyISAM");
			return $this->execute("REPLACE INTO ".$this->_tablePrefix."opd SET name = 'structure', value = '".serialize($this->_structure)."'");
		} else return true;
	}

	function _clause() {
		$condition = '';
		$baseMarkup = '';
		if(!empty($this->_mergeWith) || !empty($this->_references)) $baseMarkup = $this->type().'.'; 
		// Value restriction
		if(!empty($this->_instances)) {
			foreach($this->_instances as $instance => $value) {
				if(strpos($instance,'.')) {
					$condition .= (strlen($condition) ? ' AND ' : '') . $instance . '=' . $value;
				} else {
					$condition .= (strlen($condition) ? ' AND ' : '') . $baseMarkup.$instance . '=' . $value;
				}
			}
		}
		// Relation restriction
		if(!empty($this->_conditions)) {
			foreach($this->_conditions as $value) {
				$condition .= (strlen($condition) ? ' AND ' : '') . $baseMarkup.$value;
			}
		}
		return $condition;
	}

	function _insertClause() {
		$condition = '';
		if(isset($this->foreignKeyId)) {
			$this->_instances['pid'] = $this->foreignKeyId;
		} else {
			$this->_instances['pid'] = $this->_getNewKeyId();
		}
		$condition .= ' ('.implode(',',array_keys($this->_instances)).') VALUES ('.implode(',',$this->_instances).')';
		return $condition;
	}
	
	function _updateClause() {
		$condition = ' SET ';
		if(!isset($this->_foreignKeyId)) {
			return $this->_false();
		}
		foreach($this->_instances as $instance => $value) {
			$condition .= "\"".$instance."\" = ".$value.",";
		}
		$condition = rtrim($condition,',');
		$condition .= " WHERE pid = ".$this->_foreignKeyId;
		return $condition;
	}
	
	function _setCharset() {
		if($this->charset())
			return ' DEFAULT CHARSET='.$this->charset();
		else return '';
	}
	
	function setIndex($indexName, $instances = null) {
		if(empty($instances)) $instances = $indexName;
		$indexSet = explode(',',$instances);
		$this->_queue[$this->domain()][$this->type()]['indexes'][$indexName] = $indexSet;
	}
	
	function _validateInstance($instance) {
		if($this->_structure[$this->domain()][$this->type()]['instances'][$instance] == null) return $this->_false();
		if($this->_repository[0][$instance] == null) return $this->_false();
		return true;
	}
	
	function _validateType($type) {
		if($this->_structure[$this->domain()][$type] == null) return $this->_false();
		return true;
	}
	
	function _alterStructure() {
		if($this->_version <= $this->_structure[$this->domain()][$this->type()]['version']) return $this->_false();
		if(empty($this->_queue)) return $this->_false();
		$addition = array();
		$alternation = array();
		if(!empty($this->_queue[$this->domain()][$this->type()]['instances'])) {
			foreach($this->_queue[$this->domain()][$this->type()]['instances'] as $instance => $options) {
				if(empty($this->_structure[$this->domain()][$this->type()]['instances'][$instance])) {
					$addition[$instance] = $options;
				} else { 
					if($options['attribute'] != $this->_structure[$this->domain()][$this->type()]['instances'][$instance]['attribute'])
						$alternation[$instance]['attribute'] = $options['attribute'];
					if($options['additional'] != $this->_structure[$this->domain()][$this->type()]['instances'][$instance]['additional'])
						$alternation[$instance]['additional'] = $options['additional'];
				}
			}
		}

		$clause = 'ALTER TABLE '.$this->_tablePrefix.$this->_prototype.' ';
		if(!empty($addition)) {
			foreach($addition as $instance => $options) {
				$clause .= 'ADD '.$instance.' '.$options['attribute'].(isset($options['additional']) ? ' '.$options['additional'] : '').',';
			}
		}
		if(!empty($alternation)) {
			foreach($alternation as $instance => $options) {
				$clause .= 'CHANGE '.$instance.' '.$instance.' '.$options['attribute'].(isset($options['additional']) ? ' '.$options['additional'] : '').',';
			}
		}
		if(!empty($this->_queue[$this->domain()][$this->type()]['indexes'])) {
			foreach($this->_queue[$this->domain()][$this->type()]['indexes'] as $indexName => $indexes) {
				$clause .= 'ADD INDEX '.$indexName.' ('.implode(',',$indexes).'),';
			}
		}
		$clause = rtrim($clause,',');
		// Remapping structure tree.
		if($this->execute($clause)) {
			$this->_structure[$this->domain()][$this->type()]['version'] = $this->_version; 
			if(isset($addition)) {
				foreach($addition as $instance => $options)
					$this->_structure[$this->domain()][$this->type()]['instances'][$instance] = $options;
			}
			if(isset($alternation)) {
				foreach($alternation as $instance => $options)
					$this->_structure[$this->domain()][$this->type()]['instances'][$instance] = $options;
			}
			if(!empty($this->_queue[$this->domain()][$this->type()]['indexes'])) {
				$this->_structure[$this->domain()][$this->type()]['indexes'] = $this->_queue[$this->domain()][$this->type()]['indexes'];
			}
			$this->_saveStructure();
			return true;
		}
		return $this->_false();	
	}
	
	function _getNewKeyId() {
		return $this->queryCell("SELECT max(pid) FROM ".$this->_tablePrefix.$this->type()) + 1;
	}
	
	function _false($err = null) {
		return false;
	}
	
	/** Additional features for Textcube **/
	/** NOTICE : PARTS BELOW EXTENDS DBQuery Class WHICH IS THE BASE OF POD
	             AND WORKS ONLY WITH 'PageCache' Component in Textcube **/
	function queryWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
//		requireComponent('Needlworks.Cache.PageCache');
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::query($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	function queryAllWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
//		requireComponent('Needlworks.Cache.PageCache');
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryAllWithCache($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	function queryRowWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
//		requireComponent('Needlworks.Cache.PageCache');
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryRow($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	function queryColumnWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
//		requireComponent('Needlworks.Cache.PageCache');
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryColumn($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
}
?>
