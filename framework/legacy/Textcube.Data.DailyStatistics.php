<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class DailyStatistics {
	function DailyStatistics() {
		$this->reset();
	}

	function reset() {
		$this->date =
		$this->visits =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'datemark DESC') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND datemark = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}DailyStatistics WHERE blogid = ".getBlogId()." $filter $sort");
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
		if (!isset($this->visits))
			$this->visits = 1;
		
		if (!$query = $this->_buildQuery())
			return false;

		if ($query->doesExist()) {
			$query->setAttribute('visits', "visits + {$this->visits}");
			if (!$query->update())
				return $this->_error('update');
		} else if (!$query->insert()) {
			return $this->_error('insert');
		}
		return true;
	}
	
	function update() {
		if (!isset($this->visits))
			$this->visits = 1;
		
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
		$instance->visits = 1;
		return $instance->update();
	}
	
	/*@static@*/
	function validateDate($date) {
		return (is_numeric($date) && checkdate(intval($date / 100) % 100, $date % 100, intval($date / 10000)));
	}
	
	function _buildQuery() {
		if (!DailyStatistics::validateDate($this->date))
			return $this->_error('date');
			
		$query = DBModel::getInstance();
		$query->reset('DailyStatistics');
		$query->setQualifier('blogid', 'equals', getBlogId());
		$query->setQualifier('datemark', 'equals', $this->date);
		if (isset($this->visits)) {
			if (!Validator::number($this->visits, 1))
				return $this->_error('visits');
			$query->setAttribute('visits', $this->visits);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
