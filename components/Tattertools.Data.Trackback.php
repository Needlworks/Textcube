<?
class Trackback {
	function Trackback() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->entry =
		$this->url =
		$this->site =
		$this->title =
		$this->excerpt =
		$this->ip =
		$this->received =
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
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}Trackbacks WHERE owner = $owner $filter $sort");
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
					case 'subject':
						$name = 'title';
						break;
					case 'written':
						$name = 'received';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database, $owner;
		$this->id = null;
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->url))
			return $this->_error('url');
		if (!isset($this->site))
			return $this->_error('site');
		if (!isset($this->title))
			return $this->_error('title');
		if (!isset($this->ip))
			$this->ip = $_SERVER['REMOTE_ADDR'];

		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('written'))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert())
			return $this->_error('insert');
		$this->id = $query->id;

		mysql_query("UPDATE {$database['prefix']}Entries SET trackbacks = trackbacks + 1 WHERE owner = $owner AND id = {$this->entry}");
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function _buildQuery() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'Trackbacks');
		$query->setQualifier('owner', $owner);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->entry)) {
			if (!Validator::number($this->entry, 1))
				return $this->_error('entry');
			$query->setQualifier('entry', $this->entry);
		}
		if (isset($this->url)) {
			$this->url = trim($this->url);
			if (empty($this->url))
				return $this->_error('url');
			$query->setQualifier('url', $this->url, true);
		}
		if (isset($this->site)) {
			$this->site = trim($this->site);
			if (empty($this->site))
				return $this->_error('site');
			$query->setAttribute('site', $this->site, true);
		}
		if (isset($this->title)) {
			$this->title = trim($this->title);
			if (empty($this->title))
				return $this->_error('title');
			$query->setAttribute('subject', $this->title, true);
		}
		if (isset($this->excerpt)) {
			$this->excerpt = trim($this->excerpt);
			$query->setAttribute('excerpt', $this->excerpt, true);
		}
		if (isset($this->ip)) {
			if (!Validator::ip($this->ip))
				return $this->_error('ip');
			$query->setAttribute('ip', $this->ip, true);
		}
		if (isset($this->received)) {
			if (!Validator::timestamp($this->received))
				return $this->_error('received');
			$query->setAttribute('written', $this->received);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>