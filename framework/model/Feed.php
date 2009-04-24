<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Feed {
	function Feed() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->url =
		$this->group =
		$this->link =
		$this->title =
		$this->description =
		$this->language =
		$this->modified =
			null;
	}
	
	function open($filter = '', $fields = 'f.*, g.groupId', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = Data_IAdapter::query("SELECT $fields FROM {$database['prefix']}Feeds f JOIN {$database['prefix']}FeedGroupRelations g ON f.id = g.feed WHERE g.blogid = ".getBlogId()." $filter $sort");
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
					case 'xmlURL':
						$name = 'url';
						break;
					case 'blogURL':
						$name = 'link';
						break;
					case 'written':
						$name = 'registered';
						break;
					case 'groupId':
						$name = 'group';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database, $blogid;
		$this->id = null;
		$this->url = trim($this->url);
		if (empty($this->url))
			return $this->_error('url');
		if (!isset($this->group) || !Validator::number($this->group, 0))
			return $this->_error('group');

		$query = new Data_table($database['prefix'] . 'Feeds');
		$query->setQualifier('xmlURL', UTF8::lessenAsEncoding($this->url, 255), true);
		$query->setAttribute('title', UTF8::lessenAsEncoding($this->url, 255), true);
		if (!$query->doesExist()) {
			if (!$query->insert())
				return $this->_error('insert');
		}
		$this->id = $query->getCell('id');
		
		$query->reset($database['prefix'] . 'FeedGroupRelations');
		$query->setQualifier('blogid', $blogid);
		$query->setQualifier('feed', $this->id);
		$query->setQualifier('groupId', $this->group);
		if (!$query->doesExist()) {
			if (!$query->insert())
				return $this->_error('insert');
		}
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getGroupName() {
		if (isset($this->group))
			return FeedGroup::getName($this->group);
		return null;
	}
	
	function getItems() {
		if (!is_numeric($this->id) || ($this->id <= 0))
			return null;
		$item = new FeedItem();
		if ($item->open('i.feed = ' . $this->id))
			return $item;
		return null;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
