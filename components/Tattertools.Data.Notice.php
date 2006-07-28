<?
class Notice {
	function Notice() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->visibility =
		$this->title =
		$this->content =
		$this->published =
		$this->created =
		$this->modified =
			null;
	}

	/*@polymorphous(numeric $id, $fields, $sort)@*/
	function open($filter = '', $fields = '*', $sort = 'published DESC') {
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category = -2 $filter $sort");
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
				switch ($name) {
					case 'owner':
					case 'draft':
					case 'category':
						unset($name);
						break;
					case 'visibility':
						if ($value <= 0)
							$value = 'private';
						else
							$value = 'public';
						break;
				}
				if (isset($name))
					$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database, $owner;
		unset($this->id);
		$this->title = trim($this->title);
		if (empty($this->title))
			return $this->_error('title');
		if (empty($this->content))
			return $this->_error('content');

		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->published))
			$query->setAttribute('published', 'UNIX_TIMESTAMP()');
		if (!isset($this->created))
			$query->setAttribute('created', 'UNIX_TIMESTAMP()');
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');

		if (!$query->insert())
			return $this->_error('insert');
		$this->id = $query->id;
		
		return true;
	}
	
	function remove($id) {
		global $database, $owner;
		if (!is_numeric($id)) {
			return false;
		}
		$result = mysql_query("DELETE FROM FROM {$database['prefix']}Entries WHERE owner = $owner AND category = -2 AND id = $id");
		if ($result && ($this->_count = mysql_affected_rows()))
			return true;
		return false;
	}
	
	function update() {
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->update())
			return $this->_error('update');
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getLink() {
		global $defaultURL;
		if (!Validator::number($this->id, 1))
			return null;
		return "$defaultURL/notice/{$this->id}";
	}
	
	function getAttachments() {
		if (!Validator::number($this->id, 1))
			return null;
		requireComponent('Tattertools.Data.Attachment');
		$attachment = new Attachment();
		if ($attachment->open('parent = ' . $this->id))
			return $attachment;
	}

	/*@static@*/
	function doesExist($id) {
		global $database, $owner;
		if (!Validator::number($id, 1))
			return false;
		return DBQuery::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND category = -2 AND draft = 0");
	}
	
	function _buildQuery() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'Entries');
		$query->setQualifier('owner', $owner);
		$query->setQualifier('category', -2);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->title))
			$query->setAttribute('title', $this->title, true);
		if (isset($this->content))
			$query->setAttribute('content', $this->content, true);
		if (isset($this->visibility)) {
			switch ($this->visibility) {
				case 'private':
					$query->setAttribute('visibility', 0);
					break;
				case 'public':
					$query->setAttribute('visibility', 2);
					break;
				default:
					return $this->_error('visibility');
			}
		}
		if (isset($this->published)) {
			if (!Validator::number($this->published, 1))
				return $this->_error('published');
			$query->setAttribute('published', $this->published);
		}
		if (isset($this->created)) {
			if (!Validator::number($this->created, 1))
				return $this->_error('created');
			$query->setAttribute('created', $this->created);
		}
		if (isset($this->modified)) {
			if (!Validator::number($this->modified, 1))
				return $this->_error('modified');
			$query->setAttribute('modified', $this->modified);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>