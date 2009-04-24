<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// NOTE : it does not support to save link category yet, 
// due to compartibility issues with other software under 'Project Tattertools.'

class Model_Link {
	function __construct() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->title =
		$this->url =
		$this->feed =
		$this->registered =
		$this->xfn =
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
		$this->_result = Data_IAdapter::query("SELECT $fields FROM {$database['prefix']}Links WHERE blogid = ".getBlogId()." $filter $sort");
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
		global $database;
		if (empty($url))
			return null;
		return Data_IAdapter::queryCell("SELECT id FROM {$database['prefix']}Links WHERE blogid = ".getBlogId()." AND url = '" . Data_IAdapter::escapeString($url) . "'");
	}
	
	/*@static@*/
	function getURL($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return Data_IAdapter::queryCell("SELECT label FROM {$database['prefix']}Links WHERE blogid = ".getBlogId()." AND id = $id");
	}

	function _buildQuery() {
		global $database;
		$query = new Data_table($database['prefix'] . 'Links');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->url)) {
			$this->url = UTF8::lessenAsEncoding(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setQualifier('url', $this->url, true);
		}
		if (isset($this->title)) {
			$this->title = UTF8::lessenAsEncoding(trim($this->title), 255);
			if (empty($this->title))
				return $this->_error('title');
			$query->setAttribute('name', $this->title, true);
		}
		if (isset($this->feed)) {
			$this->feed = UTF8::lessenAsEncoding(trim($this->feed), 255);
			if (empty($this->feed))
				return $this->_error('feed');
			$query->setAttribute('rss', $this->feed, true);
		}
		if (isset($this->registered)) {
			if (!Validator::number($this->registered, 1))
				return $this->_error('registered');
			$query->setAttribute('written', $this->registered);
		}
		if (isset($this->xfn)) {
			$this->xfn = UTF8::lessenAsEncoding(trim($this->xfn), 255);
			if (empty($this->xfn))
				return $this->_error('xfn');
			$query->setAttribute('xfn', $this->xfn, true);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
