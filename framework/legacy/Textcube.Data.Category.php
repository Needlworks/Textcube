<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


class Category {
	function __construct() {
		$this->reset();
		$this->pointer = null;
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
		$context = Model_Context::getInstance();
		$pool = DBModel::getInstance();
		
		$pool->reset('Categories');		
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);
		if (is_numeric($filter)) {
			$pool->setQualifier('id','equals',$filter);
		} else if (is_bool($filter)) {
			if ($filter)
				$pool->setQualifier('parent',null);
		} else if (!empty($filter)) {
			$condition = array_map(create_function('$s','return trim($s);'), explode('=',$filter));
			$pool->setQualifier($condition[0],'equals',$condition[1]);
		}
		if (!empty($sort))
			$pool->setOrder($sort);
		$this->close();
		$this->_result = $pool->getAll($fields);
		if ($this->_result) {
			$this->_count = count($this->_result);
			$this->pointer = 0;
		}
		return $this->shift();
	}
	
	function close() {
		if (isset($this->_result)) {
			POD::free($this->_result);
			unset($this->_result);
		}
		$this->_count = 0;
		$this->reset();
	}
	
	function shift() {
		$this->reset();
		if($this->_result && !empty($this->_result[$this->pointer])) {
			foreach ($this->_result[$this->pointer] as $name => $value) {
				if ($name == 'blogid')
					continue;
				switch ($name) {
					case 'entries':
						$name = 'exposedPosts';
						break;
					case 'entriesinlogin':
						$name = 'posts';
						break;
				}
				$this->$name = $value;
			}
			$this->pointer++;
			return true;
		}
		return false;
	}
	
	function add() {
		if($this->id != 0) $this->id = null;
		
		if (isset($this->parent) && !is_numeric($this->parent))
			return $this->_error('parent');
		
		$this->name = UTF8::lessenAsEncoding(trim($this->name), 127);

		if (empty($this->name))
			return $this->_error('name');
		
		$query = DBModel::getInstance();
		$query->reset('Categories');
		
		$query->setQualifier('blogid', 'equals', getBlogId());
		
		if (isset($this->parent)) {
			if (is_null($parentLabel = Category::getLabel($this->parent))) {
				return $this->_error('parent');
			}
			$query->setQualifier('parent', 'equals', $this->parent);
			$query->setAttribute('label', UTF8::lessenAsEncoding($parentLabel . '/' . $this->name, 255), true);
		} else {
			$query->setQualifier('parent', null);
			$query->setAttribute('label', $this->name, true);
		}
		$query->setQualifier('name', 'equals', $this->name, true);

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
			$query->setQualifier('id', 'equals', $this->id);
		}

		if (!$query->insert())
			return $this->_error('insert');
		return true;
	}

	function getNextCategoryId($id = 0) {
		$context = Model_Context::getInstance();
		$pool    = new DBModel();
		
		$pool->reset('Categories');
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);		
		
		$maxId = $pool->getCell('MAX(id)');

		if($id==0)
			return $maxId + 1;
		else
			return ($maxId > $id ? $maxId + 1 : $id);
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

	static function doesExist($id) {
		if (!Validator::number($id, 0))
			return false;
		if ($id == 0) return true; // not specified case

		$context = Model_Context::getInstance();
		$pool    = new DBModel();
		
		$pool->reset('Categories');
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);
		$pool->setQualifier('id','equals',$id);		
	
		return $pool->doesExist('id');
	}
	
	static function getId($label) {
		if (empty($label))
			return null;

		$context = Model_Context::getInstance();
		$pool    = new DBModel();
		
		$pool->reset('Categories');
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);
		$pool->setQualifier('label','equals',$label,true);
		return $pool->getCell('id');
	}
	
	static function getLabel($id) {
		if (!Validator::number($id, 1))
			return null;

		$context = Model_Context::getInstance();
		$pool    = new DBModel();
		
		$pool->reset('Categories');
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);
		$pool->setQualifier('id','equals',$id);
		return $pool->getCell('label');
	}

	/*@static@*/
	static function getParent($id) {
		if (!Validator::number($id, 1))
			return null;
			
		$context = Model_Context::getInstance();
		$pool    = new DBModel();
		
		$pool->reset('Categories');
		$blogid = intval($context->getProperty('blog.id'));
		$pool->setQualifier('blogid','equals',$blogid);
		$pool->setQualifier('id','equals',$id);
		return $pool->getCell('parent');
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
