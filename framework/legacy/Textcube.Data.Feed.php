<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class FeedGroup {
	/*@static@*/
	function getId($name, $add = false) {
		$name = UTF8::lessenAsEncoding($name);
		if (empty($name))
			return 0;
		$query = DBModel::getInstance();
		$query->reset('FeedGroups');
		$query->setQualifier('blogid', 'equals', getBlogId());
		$query->setQualifier('title', 'equals', $name, true);
		$id = $query->getCell('id');
		if (is_null($id) && $add) {
			$query->unsetQualifier('title');
			$id = $query->getCell('MAX(id) + 1');
			$query->setQualifier('id', 'equals', $id);
			$query->setQualifier('title', 'equals', $name, true);
			if ($query->insert())
				return $id;
			else
				return null;
		}
		return $id;
	}

	/*@static@*/
	function getName($id) {
		if (!Validator::number($id, 0))
			return null;
		if ($id == 0)
			return '';
		$query = DBModel::getInstance();
		$query->reset('FeedGroups');
		$query->setQualifier('blogid', 'equals', getBlogId());
		$query->setQualifier('id', 'equals', $id);
		return $query->getCell('title');
	}
}

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
	
	function open($filter = '', $fields = 'f.*, g.groupid', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}Feeds f JOIN {$database['prefix']}FeedGroupRelations g ON f.id = g.feed WHERE g.blogid = ".getBlogId()." $filter $sort");
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
					case 'xmlurl':
						$name = 'url';
						break;
					case 'blogURL':
						$name = 'link';
						break;
					case 'written':
						$name = 'registered';
						break;
					case 'groupid':
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

		$query = DBModel::getInstance();
		$query->reset('Feeds');
		$query->setQualifier('xmlurl', 'equals', UTF8::lessenAsEncoding($this->url, 255), true);
		$query->setAttribute('title', UTF8::lessenAsEncoding($this->url, 255), true);
		$query->setAttribute('id',($this->_getMaxId()+1));
		if (!$query->doesExist()) {
			if (!$query->insert())
				return $this->_error('insert');
		}
		$this->id = $query->getCell('id');
		
		$query->reset('FeedGroupRelations');
		$query->setQualifier('blogid', 'equals',$blogid);
		$query->setQualifier('feed', 'equals',$this->id);
		$query->setQualifier('groupid', 'equals',$this->group);
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

	function _getMaxId() {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Feeds");
		if($maxId) return $maxId;
		else return 0;
	}
	
	function _error($error) {
		$this->error = $error;
		return false;
	}
}

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
		$this->_result = POD::query("SELECT $fields 
				FROM {$database['prefix']}FeedItems i 
				JOIN {$database['prefix']}FeedGroupRelations g ON i.feed = g.feed 
				WHERE g.blogid = $blogid $filter $sort");
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

		$query = DBModel::getInstance();
		$query->reset('FeedItems');
		$query->setQualifier('feed', 'equals', $this->feed);
		$query->setQualifier('permalink', 'equals', $this->link, true);
		$this->id = $query->getCell('id');
		if (is_null($this->id)) {
			$query->setAttribute('id', $this->_getMaxId()+1);
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
		if (isset($this->id)) {
			$pool = DBModel::getInstance();
			$context = Model_Context::getInstance();
			$blogid = intval($context->getProperty('blog.id'));
			$pool->reset('FeedReads');
			$pool->setQualifier('blogid','equals',$blogid);
			$pool->setQualifier('item','equals',$this->id);
			return $pool->doesExist();
		}
		return false;
	}
	
	function setRead() {
		if (isset($this->id)) {
			$pool = DBModel::getInstance();
			$context = Model_Context::getInstance();
			$blogid = intval($context->getProperty('blog.id'));
			$pool->reset('FeedReads');
			$pool->setAttribute('blogid',$blogid);
			$pool->setAttribute('item', $this->id);
			return $pool->insert();
		}
		return false;
	}
	
	function isStarred() {
		if (isset($this->id)){
			$pool = DBModel::getInstance();
			$context = Model_Context::getInstance();
			$blogid = intval($context->getProperty('blog.id'));
			$pool->reset('FeedStarred');
			$pool->setQualifier('blogid','equals',$blogid);
			$pool->setQualifier('item','equals',$this->id);
			return $pool->doesExist();
		}
		return false;
	}
	
	function setStarred() {
		if (isset($this->id)) {
			$pool = DBModel::getInstance();
			$context = Model_Context::getInstance();
			$blogid = intval($context->getProperty('blog.id'));
			$pool->reset('FeedStarred');
			$pool->setAttribute('blogid',$blogid);
			$pool->setAttribute('item',$this->id);
			return $pool->insert();
		}
		return false;
	}

	function _getMaxId() {
		$pool = DBModel::getInstance();
		$pool->reset('FeedItems');
		$maxId = $pool->getCell('max(id)');
		if(!empty($maxId)) return $maxId;
		else return 0;
	}
}

?>
