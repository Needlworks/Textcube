<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Model_Category {
	function __construct() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->parent =
		$this->label =
		$this->name =
		$this->priority =
		$this->posts =
		$this->exposedPosts =
			null;
	}
	
	/*@polymorphous(bool $parentOnly, $fields, $sort)@*/
	/*@polymorphous(numeric $id, $fields, $sort)@*/
	function open($filter = true, $fields = '*', $sort = 'priority') {
		global $database;
		if (is_numeric($filter)) {
			$filter = 'AND id = ' . $filter;
		} else if (is_bool($filter)) {
			if ($filter)
				$filter = 'AND parent IS NULL';
		} else if (!empty($filter)) {
			$filter = 'AND ' . $filter;
		}
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = Data_IAdapter::query("SELECT $fields FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." $filter $sort");
		if ($this->_result)
			$this->_count = Data_IAdapter::num_rows($this->_result);
		return $this->shift();
	}
	
	function close() {
		if (isset($this->_result)) {
			Data_IAdapter::free($this->_result);
			unset($this->_result);
		}
		$this->_count = 0;
		$this->reset();
	}
	
	function shift() {
		$this->reset();
		if ($this->_result && ($row = Data_IAdapter::fetch($this->_result))) {
			foreach ($row as $name => $value) {
				if ($name == 'blogid')
					continue;
				switch ($name) {
					case 'entries':
						$name = 'exposedPosts';
						break;
					case 'entriesInLogin':
						$name = 'posts';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database;
		if($this->id != 0) $this->id = null;
		if (isset($this->parent) && !is_numeric($this->parent))
			return $this->_error('parent');
		$this->name = UTF8::lessenAsEncoding(trim($this->name), 127);
		if (empty($this->name))
			return $this->_error('name');
		
		$query = new Data_Table($database['prefix'] . 'Categories');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->parent)) {
			if (is_null($parentLabel = Category::getLabel($this->parent)))
				return $this->_error('parent');
			$query->setQualifier('parent', $this->parent);
			$query->setAttribute('label', UTF8::lessenAsEncoding($parentLabel . '/' . $this->name, 255), true);
		} else {
			$query->setQualifier('parent', null);
			$query->setAttribute('label', $this->name, true);
		}
		$query->setQualifier('name', $this->name, true);

		if (isset($this->priority)) {
			if (!is_numeric($this->priority))
				return $this->_error('priority');
			$query->setAttribute('priority', $this->priority);
		}
		
		if ($query->doesExist()) {
			$this->id = $query->getCell('id');
			if ($query->update())
				return true;
			else
				return $this->_error('update');
		}

		if (!isset($this->id)) {
			$this->id = $this->getNextCategoryId();
			$query->setQualifier('id', $this->id);
		}

		if (!$query->insert())
			return $this->_error('insert');
		return true;
	}

	function getNextCategoryId($id = 0) {
		global $database;
		$maxId = Data_IAdapter::queryCell("SELECT MAX(id) FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()); 
		if($id==0)
			return $maxId + 1;
		else
			return ($maxId > $id ? $maxId : $id);
	}

	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getChildren() {
		if (!$this->id)
			return null;
		$category = new Category();
		if ($category->open('parent = ' . $this->id))
			return $category;
	}

	function escape($escape = true) {
		$this->name = Validator::escapeXML(@$this->name, $escape);
		$this->label = Validator::escapeXML(@$this->label, $escape);
	}

	/*@static@*/
	function doesExist($id) {
		global $database;
		if (!Validator::number($id, 0))
			return false;
		if ($id == 0) return true; // not specified case
		return Data_IAdapter::queryExistence("SELECT id FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." AND id = $id");
	}
	
	/*@static@*/
	function getId($label) {
		global $database;
		if (empty($label))
			return null;
		return Data_IAdapter::queryCell("SELECT id FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." AND label = '" . Data_IAdapter::escapeString($label) . "'");
	}
	
	/*@static@*/
	function getLabel($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return Data_IAdapter::queryCell("SELECT label FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." AND id = $id");
	}

	/*@static@*/
	function getParent($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return Data_IAdapter::queryCell("SELECT parent FROM {$database['prefix']}Categories WHERE blogid = ".getBlogId()." AND id = $id");
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
