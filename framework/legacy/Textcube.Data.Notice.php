<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Notice {
	function Notice() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->blogid = 
		$this->userid = 
		$this->id =
		$this->visibility =
		$this->starred =
		$this->title =
		$this->slogan =
		$this->content =
		$this->contentformatter =
		$this->contenteditor =
		$this->published =
		$this->created =
		$this->modified =
			null;
	}
	
	function init() {
		if(!isset($this->blogid) || $this->blogid === null) $this->blogid = getBlogId();
	}

	/*@polymorphous(numeric $id, $fields, $sort)@*/
	function open($filter = '', $fields = '*', $sort = 'published DESC') {
		global $database;
		$this->close();
		$this->init();
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}Entries WHERE blogid = ".getBlogId()." AND draft = 0 AND category = -2 $filter $sort");
		if ($this->_result)
			$this->_count = POD::num_rows($this->_result);
		return $this->shift();
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
				switch ($name) {
					case 'blogid':
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
	
	function add($userid = null) {
		$this->init();
		if (isset($this->id) && !Validator::number($this->id, 1))
			 return $this->_error('id');
		$this->title = trim($this->title);
		if (empty($this->title))
			return $this->_error('title');
		if (empty($this->content))
			return $this->_error('content');

		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->id) || $query->doesExist() || $this->doesExist($this->id)) {
			$this->id = $this->nextEntryId();
		}
		$query->setQualifier('id', 'equals', $this->id);
			
		if (empty($this->starred))
			$this->starred = 0;
		if (!isset($this->published))
			$query->setAttribute('published', 'UNIX_TIMESTAMP()');
		if (!isset($this->created))
			$query->setAttribute('created', 'UNIX_TIMESTAMP()');
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		$query->setAttribute('blogid',$this->blogid);
		if (!isset($this->userid)){
			$this->userid = getUserId();
			$query->setAttribute('userid',getUserId());
		}
		$this->saveSlogan();
		if (!$query->insert())
			return $this->_error('insert');
		
		return true;
	}
	
	function remove($id) {
		global $database;
		$this->init();
		if (is_numeric($id)) {
			return false;
		}
		$result = POD::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = ".getBlogId()." AND category = -2 AND id = $id");
		if ($result && ($this->_count = POD::num_rows($result)))
			return true;
		return false;
	}
	
	function update() {
		$this->init();
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		if (isset($this->slogan))
			$this->saveSlogan();
					
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
		$attachment = new Attachment();
		if ($attachment->open('parent = ' . $this->id))
			return $attachment;
	}

	function saveSlogan($slogan = null) {
		global $database;
		$this->init();
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		if (!Validator::number($this->userid, 1))
			return $this->_error('userid');
		if (isset($slogan))
			$this->slogan = $slogan;

		$query = new DBModel;
		$query->reset('Entries');
		$query->setQualifier('blogid',$this->blogid);
		if(isset($this->userid)) $query->setQualifier('userid', $this->userid);
		$query->setQualifier('id', $this->id);
		if (!$query->doesExist())
			return $this->_error('id');

		if (isset($this->slogan) && $this->validateSlogan($this->slogan))
			$slogan0 = $this->slogan;
		else
			$slogan0 = $this->slogan = $this->makeSlogan($this->title);
			
		$slogan0 = UTF8::lessenAsEncoding($slogan0, 255);

		for ($i = 1; $i < 1000; $i++) {
//			$checkSlogan = POD::escapeString($this->slogan);
			$checkSlogan = $this->slogan;
			$query->setAttribute('slogan', $checkSlogan, true);
			if (!POD::queryExistence(
				"SELECT id FROM {$database['prefix']}Entries " 
				. "WHERE blogid = ".$this->blogid." AND id <> {$this->id} AND slogan ='{$checkSlogan}'")
				) 
			{
				if (!$query->update())
					return $this->_error('update');
				return true;
			}
			$this->slogan = UTF8::lessenAsEncoding($slogan0, 245) . '-' . $i;
		}
		// if try saveSlogan again, slogan string has more $i
		return $this->_error('limit');
	}

	/*@static@*/
	function doesExist($id) {
		global $database;
		if (!Validator::number($id, 1))
			return false;
		return POD::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE blogid = ".getBlogId()." AND id = $id AND category = -2 AND draft = 0");
	}

	function nextEntryId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}Entries WHERE blogid = ".getBlogId());
		if($id==0)
			return $maxId + 1;
		else
			return ($maxId > $id ? $maxId : $id);
	}
	
	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('Entries');
		$query->setQualifier('blogid', 'equals', getBlogId());
		$query->setQualifier('category', 'equals', -2);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', 'equals', $this->id);
		}
		if (isset($this->title))
			$query->setAttribute('title', $this->title, true);
		if (isset($this->content)) {
			$query->setAttribute('content', $this->content, true);
			$query->setAttribute('contentformatter', $this->contentformatter, true);
			$query->setAttribute('contenteditor', $this->contenteditor, true);
		}
		if (isset($this->visibility)) {
			switch ($this->visibility) {
				case 'private':
					$query->setAttribute('visibility', 0);
					break;
				case 'public':
					$query->setAttribute('visibility', 2);
					break;
				default:
					$query->setAttribute('visibility', 0);
					break;
			}
		}
		if(isset($this->starred)) {
			$query->setAttribute('starred',$this->starred);
		} else {
			$query->setAttribute('starred',0);
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
