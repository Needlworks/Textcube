<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class TrackbackLog {
	function TrackbackLog() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->entry =
		$this->url =
		$this->sent =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'written') {
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}TrackbackLogs WHERE owner = $owner $filter $sort");
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
				if ($name == 'owner')
					continue;
				switch ($name) {
					case 'written':
						$name = 'sent';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		$this->id = null;
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->url))
			return $this->_error('url');
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('written'))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert())
			return $this->_error('insert');
		$this->id = $query->id;
		
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function _buildQuery() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'TrackbackLogs');
		$query->setQualifier('owner', $owner);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->entry)) {
			if (!Validator::number($this->entry, 1))
				return $this->_error('entry');
			$query->setAttribute('entry', $this->entry);
		}
		if (isset($this->url)) {
			$this->url = mysql_lessen(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setAttribute('url', $this->url, true);
		}
		if (isset($this->sent)) {
			if (!Validator::timestamp($this->sent))
				return $this->_error('sent');
			$query->setAttribute('written', $this->sent);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>