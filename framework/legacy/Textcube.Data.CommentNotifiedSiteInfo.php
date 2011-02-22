<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class CommentNotifiedSiteInfo {
	function CommentNotifiedSiteInfo() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->title =
		$this->name=
		$this->url =
		$this->modified =
			null;
	}
	
	function open($filter = 'True', $fields = '*', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'id = ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE $filter $sort");
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
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database;
		if (!isset($this->id))
			$this->id = $this->nextId();
		else $this->id = $this->nextId($this->id);
		if (!isset($this->title))
			return $this->_error('title');
		if (!isset($this->name))
			return $this->_error('name');
		if (!isset($this->url))
			return $this->_error('url');
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('modified'))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert())
			return $this->_error('insert');
//		$this->id = $query->id;
		
		return true;
	}

	function update() {
		global $database;
		if (!isset($this->id))
			return $this->_error('id');
		if (!isset($this->title))
			return $this->_error('title');
		if (!isset($this->name))
			return $this->_error('name');
		if (!isset($this->url))
			return $this->_error('url');
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('modified'))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->update())
			return $this->_error('update');
//		$this->id = $query->id;
		
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getCommentsNotified() {
		if (!Validator::number($this->id, 1))
			return null;
		$comment = new CommentNotified();
		if ($comment->open('siteid = ' . $this->id))
			return $comment;
	}
	
	/*@static@*/
	function getEntry($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return POD::queryCell("SELECT entry FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE id = {$id}");
	}

	function nextId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}CommentsNotifiedSiteInfo");
		if($id == 0)
			return $maxId + 1;
		else
			 return ($maxId > $id ? $maxId + 1: $id);
	}

	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('CommentsNotifiedSiteInfo');
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', 'equals',$this->id);
		}
		if (isset($this->title)) {
			$this->title = UTF8::lessenAsEncoding(trim($this->title), 255);
			$query->setAttribute('title', $this->title, true);
		}
		if (isset($this->name)) {
			$this->name = UTF8::lessenAsEncoding(trim($this->name), 255);
			$query->setAttribute('name', $this->name, true);
		}
		if (isset($this->url)) {
			$this->url = UTF8::lessenAsEncoding(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setAttribute('url', $this->url, true);
		}
		if (isset($this->modified)) {
			if (!Validator::timestamp($this->modified))
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
