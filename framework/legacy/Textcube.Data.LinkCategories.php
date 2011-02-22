<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class LinkCategories {
	function LinkCategories() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->pid =
		$this->name =
		$this->priority =
		$this->visibility =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}LinkCategories WHERE blogid = ".getBlogId()." $filter $sort");
		if ($this->_result)
			$this->_count = POD::num_rows($this->_result);
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
		if ($this->_result && ($row = POD::fetch($this->_result))) {
			foreach ($row as $name => $value) {
				if ($name == 'blogid')
					continue;
				switch ($name) {
					case 'name':
						$name = 'name';
						break;
					case 'priority':
						$name = 'priority';
						break;
					case 'visibility':
						$name = 'visibility';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}

	function add() {
		$this->id = $this->getNextLinkCategoryId();
		$this->pid = $this->getNextLinkCategoryPid();

		if (!isset($this->name))
			return $this->_error('name');
		if (!isset($this->priority))
			return $this->_error('priority');
		if (!isset($this->visibility))
			$this->visibility = 2;
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->insert())
			return $this->_error('insert');
//		$this->id = $query->id;
		return true;
	}
	
	function update() {
		if (!isset($this->id))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		
		if (!$query->update())
			return $this->_error('update');
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	/*@static@*/
	function getId($name) {
		global $database;
		if (empty($name))
			return null;
		return POD::queryCell("SELECT id FROM {$database['prefix']}LinkCategories WHERE blogid = ".getBlogId()." AND name = '" . POD::escapeString($name) . "'");
	}
	
	/*@static@*/
	function getName($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return POD::queryCell("SELECT name FROM {$database['prefix']}LinkCategories WHERE blogid = ".getBlogId()." AND id = $id");
	}

	function getNextLinkCategoryId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}LinkCategories WHERE blogid = ".getBlogId()); 
		if($id==0)
			return $maxId + 1;
		else
			return ($maxId > $id ? $maxId + 1: $id);
	}
	function getNextLinkCategoryPid($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT MAX(pid) FROM {$database['prefix']}LinkCategories"); 
		if($id==0)
			return $maxId + 1;
		else
			return ($maxId > $id ? $maxId + 1: $id);
	}
	
	function _buildQuery() {
		global $database;
		$query = DBModel::getInstance();
		$query->reset('LinkCategories');
		$query->setQualifier('blogid', 'equals', getBlogId());
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', 'equals', $this->id);
		}
		if (isset($this->pid)) {
			if (!Validator::number($this->pid, 1))
				return $this->_error('pid');
			$query->setQualifier('pid', 'equals', $this->pid);
		}
		if (isset($this->priority)) {
			if (intval($this->priority)<0) 
				return $this->_error('category');
			$query->setQualifier('priority', 'equals', $this->priority);
		}
		if (isset($this->name)) {
			$this->url = UTF8::lessenAsEncoding(trim($this->name), 255);
			if (empty($this->name))
				return $this->_error('name');
			$query->setQualifier('name', 'equals', $this->name, true);
		}
		if (isset($this->visibility)) {
			if (intval($this->visibility)<0) 
				return $this->_error('visibility');
			$query->setQualifier('visibility', 'equals', $this->visibility);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
