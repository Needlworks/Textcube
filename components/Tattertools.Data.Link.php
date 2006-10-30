<?php
class Link {
	function Link() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->title =
		$this->url =
		$this->feed =
		$this->registered =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'id') {
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}Links WHERE owner = $owner $filter $sort");
		if ($this->_result)
			$this->_count = mysql_num_rows($this->_result);
		return $this->shift();
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
					case 'name':
						$name = 'title';
						break;
					case 'rss':
						$name = 'feed';
						break;
					case 'written':
						$name = 'registered';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}

	function add() {
		unset($this->id);
		if (!isset($this->url))
			return $this->_error('url');
		if (!isset($this->title))
			return $this->_error('title');
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->registered))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert())
			return $this->_error('insert');
		$this->id = $query->id;
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
	function getId($url) {
		global $database, $owner;
		if (empty($url))
			return null;
		return DBQuery::queryCell("SELECT id FROM {$database['prefix']}Links WHERE owner = $owner AND url = '" . mysql_tt_escape_string($url) . "'");
	}
	
	/*@static@*/
	function getURL($id) {
		global $database, $owner;
		if (!Validator::number($id, 1))
			return null;
		return DBQuery::queryCell("SELECT label FROM {$database['prefix']}Links WHERE owner = $owner AND id = $id");
	}

	function _buildQuery() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'Links');
		$query->setQualifier('owner', $owner);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->url)) {
			$this->url = trim($this->url);
			if (empty($this->url))
				return $this->_error('url');
			$query->setQualifier('url', $this->url, true);
		}
		if (isset($this->title)) {
			$this->title = trim($this->title);
			if (empty($this->title))
				return $this->_error('title');
			$query->setAttribute('name', $this->title, true);
		}
		if (isset($this->feed)) {
			$this->feed = trim($this->feed);
			if (empty($this->feed))
				return $this->_error('feed');
			$query->setAttribute('rss', $this->feed, true);
		}
		if (isset($this->registered)) {
			if (!Validator::number($this->registered, 1))
				return $this->_error('registered');
			$query->setAttribute('written', $this->registered);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>