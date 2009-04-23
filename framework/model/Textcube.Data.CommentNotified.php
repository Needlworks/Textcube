<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class CommentNotified {
	function CommenNotified() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->commenter =
		$this->id =
		$this->entry =
		$this->parent =
		$this->name =
		$this->password =
		$this->homepage =
		$this->secret =
		$this->content =
		$this->ip =
		$this->written =
		$this->modified =
		$this->siteId =
		$this->isNew =
		$this->url =
		$this->remoteId =
		$this->entryTitle =
		$this->entryUrl =
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
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}CommentsNotified WHERE blogid = ".getBlogId()." $filter $sort");
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
					case 'replier':
						$name = 'commenter';
						break;
					case 'comment':
						$name = 'content';
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
		if (!isset($this->id))
			$this->id = $this->nextId();
		else $this->id = $this->nextId($this->id);
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->commenter) && !isset($this->name))
			return $this->_error('commenter');
		if (!isset($this->content))
			return $this->_error('content');
		if (!isset($this->siteId))
			return $this->_error('siteId');
		if (!isset($this->remoteId))
			return $this->_error('remoteId');
		if (!isset($this->entryTitle)) {
			if($this->entry !== 0) return $this->_error('entryTitle');
			else $this->entryTitle = 'guestbook';
		}
		if (!isset($this->entryUrl))
			return $this->_error('entryUrl');
		if (!isset($this->ip))
			$this->ip = $_SERVER['REMOTE_ADDR'];
		else if (empty($this->ip))
			$this->ip = '127.0.0.1'; // Temporary patch: 현재 댓글 알리미에 IP가 기록되지 않으므로 validation 통과를 위한 트릭
		if (!isset($this->isNew))
			$this->isNew = 0;
		if (!isset($this->secret))
			$this->secret = 0;
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('written'))
			$query->setAttribute('written', 'UNIX_TIMESTAMP()');
		if (!$query->hasAttribute('modified'))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert()) {
			return $this->_error('insert');
		}
		
		if (isset($this->parent))
			$this->entry = CommentNotified::getEntry($this->parent);
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getChildren() {
		if (!Validator::number($this->id, 1))
			return null;
		$comment = new CommentNotified();
		if ($comment->open('parent = ' . $this->id))
			return $comment;
	}
	
	/*@static@*/
	function getEntry($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return POD::queryCell("SELECT entry FROM {$database['prefix']}CommentsNotified WHERE blogid = ".getBlogId()." AND id = {$id}");
	}

	function nextId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}CommentsNotified WHERE blogid = ".getBlogId());
		if($id == 0)
			return $maxId + 1;
		else
			 return ($maxId > $id ? $maxId : $id);
	}

	function _buildQuery() {
		global $database;
		$query = new TableQuery($database['prefix'] . 'CommentsNotified');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->entry)) {
			if (!Validator::number($this->entry, 0))
				return $this->_error('entry');
			$query->setAttribute('entry', $this->entry);
		}
		if (isset($this->parent)) {
			if (empty($this->parent))
				$this->parent = NULL;
			else
				if (!Validator::number($this->parent, 0))
					return $this->_error('parent');
		}
		$query->setAttribute('parent', $this->parent);
		if (isset($this->commenter)) {
			if (!Validator::number($this->commenter, 1))
				return $this->_error('commenter');
			if (!$this->name = User::getName($this->commenter))
				return $this->_error('commenter');
			$query->setAttribute('replier', $this->commenter);
		}
		if (isset($this->name)) {
			$this->name = UTF8::lessenAsEncoding(trim($this->name), 80);
			if (empty($this->name))
				return $this->_error('name');
			$query->setAttribute('name', $this->name, true);
		}
		if (isset($this->homepage) && !empty($this->homepage)) {
			$this->homepage = UTF8::lessenAsEncoding(trim($this->homepage), 80);
			$query->setAttribute('homepage', $this->homepage, true);
		}
		if (isset($this->ip) && !empty($this->ip)) {
			if (!Validator::ip($this->ip))
				return $this->_error('ip');
			$query->setAttribute('ip', $this->ip, true);
		}
		if (isset($this->secret))
			$query->setAttribute('secret', Validator::getBit($this->secret));
		if (isset($this->isNew))
			$query->setAttribute('isNew', Validator::getBit($this->isNew));
		if (isset($this->content)) {
			$this->content = trim($this->content);
			if (empty($this->content))
				return $this->_error('content');
			$query->setAttribute('comment', $this->content, true);
		}
		if (isset($this->written)) {
			if (!Validator::timestamp($this->written))
				return $this->_error('written');
			$query->setAttribute('written', $this->written);
		}
		if (isset($this->modified)) {
			if (!Validator::timestamp($this->modified))
				return $this->_error('modified');
			$query->setAttribute('modified', $this->modified);
		}
		if (isset($this->siteId)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setAttribute('siteId', $this->siteId);
		}
		if (isset($this->remoteId)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setAttribute('remoteId', $this->remoteId);
		}
		if (isset($this->url) && !empty($this->url)) {
			// TODO: url validator doesn't validate correctly?
			//if (!Validator::url($this->url))
			//	return $this->_error('url');
			$query->setAttribute('url', $this->url, true);
		}
		if (isset($this->entryTitle)) {
			$this->entryTitle = UTF8::lessenAsEncoding(trim($this->entryTitle), 255);
			if (empty($this->entryTitle))
				return $this->_error('entryTitle');
			$query->setAttribute('entryTitle', $this->entryTitle, true);
		}
		if (isset($this->entryUrl)) {
			//if (!Validator::url($this->entryUrl))
			//	return $this->_error('entryUrl');
			$query->setAttribute('entryUrl', $this->entryUrl, true);
		}
		if (isset($this->password)) {
			$this->password = UTF8::lessenAsEncoding($this->password, 32);
			$query->setAttribute('password', $this->password, true);
			$this->password = null;
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
