<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class RefererStatistics {
	function RefererStatistics() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->host =
		$this->count =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'count DESC') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}RefererStatistics WHERE blogid = ".getBlogId()." $filter $sort");
		if ($this->_result) {
			if ($this->_count = POD::num_rows($this->_result))
				return $this->shift();
			else
				POD::free($this->_result);
		}
		unset($this->_result);
		return false;
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
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		if (!isset($this->count))
			$this->count = 1;
		
		if (!$query = $this->_buildQuery())
			return false;

		if ($query->doesExist()) {
			$query->setAttribute('count', "count + {$this->count}");
			if (!$query->update())
				return $this->_error('update');
		} else if (!$query->insert()) {
			return $this->_error('insert');
		}
		return true;
	}
	
	function update() {
		if (!isset($this->count))
			$this->count = 1;
		
		if (!$query = $this->_buildQuery())
			return false;

		if ($query->doesExist()) {
			if (!$query->update())
				return $this->_error('update');
		} else if (!$query->insert()) {
			return $this->_error('insert');
		}
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	/*@static@*/
	function compile($host) {
		$instance = new RefererStatistics();
		$instance->host = $host;
		$instance->count = 1;
		return $instance->update();
	}
	
	function _buildQuery() {
		global $database;
		$this->host = UTF8::lessenAsEncoding(trim($this->host), 64);
		if (empty($this->host))
			return $this->_error('host');
		$query = DBModel::getInstance();
		$query->reset('RefererStatistics');
		$query->setQualifier('blogid', 'equals', getBlogId());
		$query->setQualifier('host', 'equals', $this->host, true);
		if (isset($this->count)) {
			if (!Validator::number($this->count, 1))
				return $this->_error('count');
			$query->setAttribute('count', $this->count);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
