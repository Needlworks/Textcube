<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Filter {
	function Filter() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->type =
		$this->pattern =
			null;
	}
	
	function open($filter = '', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND type = \'' . $filter . '\'';
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT * FROM {$database['prefix']}Filters WHERE blogid = ".getBlogId()." $filter $sort");
		if ($this->_result) {
			if ($this->_count = mysql_num_rows($this->_result))
				return $this->shift();
			else
				mysql_free_result($this->_result);
		}
		unset($this->_result);
		return false;
	}
	
	function close() {
		if (isset($this->_result)) {
			mysql_free_result($this->_result);
			unset($this->_result);
		}
		$this->_count = 0;
		$this->reset();
	}
	
	function shift() {
		$this->reset();
		if ($this->_result && ($row = mysql_fetch_assoc($this->_result))) {
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
		unset($this->id);
		if (!isset($this->type))
			return $this->_error('type');
		if (!isset($this->pattern))
			return $this->_error('pattern');

		if (!$query = $this->_buildQuery())
			return false;
		
		if (!$query->insert())
			return $this->_error('insert');
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
	
	function remove() {
		if (!isset($this->id) && (!isset($this->type) || !isset($this->pattern)))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		
		if (!$query->delete())
			return $this->_error('delete');
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	/*@static@*/
	function isFiltered($type, $value) {
		global $database;
		$type = tc_escape_string($type);
		$value = tc_escape_string($value);
		return DBQuery::queryExistence("SELECT * FROM {$database['prefix']}Filters WHERE blogid = ".getBlogId()." AND type = '$type' AND '$value' LIKE CONCAT('%', pattern, '%')");
	}
	
	function _buildQuery() {
		global $database;
		$query = new TableQuery($database['prefix'] . 'Filters');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->type)) {
			switch ($this->type) {
				case 'content':
				case 'ip':
				case 'name':
				case 'url':
					break;
				default:
					return $this->_error('type');
			}
			if (isset($this->id))
				$query->setAttribute('type', $this->type, false);
			else
				$query->setQualifier('type', $this->type, false);
		}
		if (isset($this->pattern)) {
			$this->pattern = UTF8::lessenAsEncoding(trim($this->pattern), 255);
			if (empty($this->pattern))
				return $this->_error('pattern');
			if (isset($this->id))
				$query->setAttribute('pattern', $this->pattern, true);
			else
				$query->setQualifier('pattern', $this->pattern, true);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
