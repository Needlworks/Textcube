<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class FeedItem {
	function FeedItem() {
		$this->reset();
	}

	function reset() {
		$this->id =
		$this->feed =
		$this->link =
		$this->title =
		$this->description =
		$this->tags =
		$this->enclosure =
		$this->author =
		$this->published =
			null;
	}
	
	function open($filter = '', $fields = 'i.*', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = Data_IAdapter::query("SELECT $fields 
				FROM {$database['prefix']}FeedItems i 
				JOIN {$database['prefix']}FeedGroupRelations g ON i.feed = g.feed 
				WHERE g.blogid = $blogid $filter $sort");
		if ($this->_result) {
			if ($this->_count = Data_IAdapter::num_rows($this->_result))
				return $this->shift();
			else
				Data_IAdapter::free($this->_result);
		}
		unset($this->_result);
		return false;
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
					case 'permalink':
						$name = 'link';
						break;
					case 'written':
						$name = 'published';
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
		$this->id = null;
		$this->link = UTF8::lessenAsEncoding(trim($this->link), 255);
		if (empty($this->link))
			return false;
		
		if (isset($this->group)) {
			if (is_numeric($this->group) && ($this->group > 0))
				FeedGroup::getName($this->group);
		}
	
		if (!is_numeric($this->feed)) {
			return false;
		}
		if (!is_numeric($this->published)) {
			return false;
		}

		$query = new Data_table($database['prefix'] . 'FeedItems');
		$query->setQualifier('feed', $this->feed);
		$query->setQualifier('permalink', $this->link, true);
		$this->id = $query->getCell('id');
		if (is_null($this->id)) {
			$query->setAttribute('title', UTF8::lessenAsEncoding($this->title, 255), true);
			$query->setAttribute('description', $this->description, true);
			$query->setAttribute('tags', UTF8::lessenAsEncoding($this->tags, 255), true);
			$query->setAttribute('enclosure', UTF8::lessenAsEncoding($this->enclosure, 255), true);
			$query->setAttribute('author', UTF8::lessenAsEncoding($this->author, 255), true);
			$query->setAttribute('written', $this->published);
			$this->id = $query->insert();
			//echo mysql_error(), '<br />';
			if ($this->id === false)
				return false;
		}
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function isRead() {
		global $database;
		if (isset($this->id))
			return Data_IAdapter::queryExistence("SELECT * FROM {$database['prefix']}FeedReads WHERE blogid = ".getBlogId()." AND item = {$this->id}");
		return false;
	}
	
	function setRead() {
		global $database;
		if (isset($this->id))
			return Data_IAdapter::execute("INSERT INTO {$database['prefix']}FeedReads VALUES(".getBlogId().", {$this->id})");
		return false;
	}
	
	function isStarred() {
		global $database;
		if (isset($this->id))
			return Data_IAdapter::queryExistence("SELECT * FROM {$database['prefix']}FeedStarred 
					WHERE blogid = ".getBlogId()." AND item = {$this->id}");
		return false;
	}
	
	function setStarred() {
		global $database;
		if (isset($this->id))
			return Data_IAdapter::execute("INSERT INTO {$database['prefix']}FeedStarred VALUES(".getBlogId().", {$this->id})");
		return false;
	}
}

?>