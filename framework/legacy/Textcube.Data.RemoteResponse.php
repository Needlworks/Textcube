<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
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
		$this->isFiltered =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'written') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}RemoteResponses WHERE blogid = ".getBlogId()." AND type = 'trackback' $filter $sort");
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
		global $database;
		if (!isset($this->id)) $this->id = $this->nextId();
		else $this->id = $this->nextId($this->id);
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->url))
			return $this->_error('url');
		if (!isset($this->ip))
			$this->ip = $_SERVER['REMOTE_ADDR'];
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('written'))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		if (!isset($this->isFiltered))
			$this->isFiltered = 0;
		
		if (!$query->insert())
			return $this->_error('insert');

		if ($this->isFiltered == 0) {
			POD::query("UPDATE {$database['prefix']}Entries SET trackbacks = trackbacks + 1 WHERE blogid = ".getBlogId()." AND id = {$this->entry}");
		}
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function nextId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}RemoteResponses WHERE blogid = ".getBlogId());
		if($id == 0)
			return $maxId + 1;
		else
			 return ($maxId > $id ? $maxId + 1: $id);
	}
	
	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('RemoteResponses');
		$query->setQualifier('blogid', getBlogId());
		$query->setQualifier('type', 'trackback');
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
			$this->url = UTF8::lessenAsEncoding(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setQualifier('url', $this->url, true);
		}
		if (isset($this->site)) {
			$this->site = UTF8::lessenAsEncoding(trim($this->site), 255);
			$query->setAttribute('site', $this->site, true);
		}
		if (isset($this->title)) {
			$this->title = UTF8::lessenAsEncoding(trim($this->title), 255);
			$query->setAttribute('subject', $this->title, true);
		}
		if (isset($this->excerpt)) {
			$this->excerpt = UTF8::lessenAsEncoding(trim($this->excerpt), 255);
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
		if (isset($this->isFiltered)) {
			if ($this->isFiltered) {
				$query->setAttribute('isFiltered', 'UNIX_TIMESTAMP()');
			} else {
				$query->setAttribute('isFiltered', Validator::getBit($this->isFiltered));
			}
			
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}

class Pingback {
	function Pingback() {
		$this->reset();
	}

	function reset() {
		$this->error = 
		$this->id =
		$this->url = // source URI
		$this->title = // title of source page (may include site)
		$this->ip = // IP of pingback client
		$this->isFiltered =
			null;
		// Unused: writer, site, subject, excerpt
		// Target URI is processed in the pingback server handler.
	}

	function open($filter = '', $fields = '*', $sort = 'written') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}RemoteResponses WHERE blogid = ".getBlogId()." AND type = 'pingback' $filter $sort");
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

	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}

	function add() {
		global $database;
		if (!isset($this->id)) $this->id = $this->nextId();
		else $this->id = $this->nextId($this->id);
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->url))
			return $this->_error('url');
		if (!isset($this->ip))
			$this->ip = $_SERVER['REMOTE_ADDR'];
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('written'))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		if (!isset($this->isFiltered))
			$this->isFiltered = 0;
		
		if (!$query->insert())
			return $this->_error('insert');

		if ($this->isFiltered == 0) {
			// TODECIDE: include pingbacks in counting trackbacks?
			POD::query("UPDATE {$database['prefix']}Entries SET trackbacks = trackbacks + 1 WHERE blogid = ".getBlogId()." AND id = {$this->entry}");
		}
		return true;
	}

	function nextId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}RemoteResponses WHERE blogid = ".getBlogId());
		if($id == 0)
			return $maxId + 1;
		else
			 return ($maxId > $id ? $maxId + 1: $id);
	}

	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('RemoteResponses');
		$query->setQualifier('blogid', getBlogId());
		$query->setQualifier('type', 'pingback');
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
			$this->url = UTF8::lessenAsEncoding(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setQualifier('url', $this->url, true);
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
		if (isset($this->isFiltered)) {
			if ($this->isFiltered) {
				$query->setAttribute('isFiltered', 'UNIX_TIMESTAMP()');
			} else {
				$query->setAttribute('isFiltered', Validator::getBit($this->isFiltered));
			}
			
		}
		return $query;
	}

	function _error($error) {
		$this->ereror = $error;
		return false;
	}
}
?>
