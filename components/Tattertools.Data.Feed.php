<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class FeedGroup {
	/*@static@*/
	function getId($name, $add = false) {
		global $database, $owner;
		$name = mysql_lessen($name);
		if (empty($name))
			return 0;
		$query = new TableQuery($database['prefix'] . 'FeedGroups');
		$query->setQualifier('owner', $owner);
		$query->setQualifier('title', $name, true);
		$id = $query->getCell('id');
		if (($id === null) && $add) {
			$query->unsetQualifier('title');
			$id = $query->getCell('MAX(id) + 1');
			$query->setQualifier('id', $id);
			$query->setQualifier('title', $name, true);
			if ($query->insert())
				return $id;
			else
				return null;
		}
		return $id;
	}

	/*@static@*/
	function getName($id) {
		global $database, $owner;
		if (!Validator::number($id, 0))
			return null;
		if ($id == 0)
			return '';
		$query = new TableQuery($database['prefix'] . 'FeedGroups');
		$query->setQualifier('owner', $owner);
		$query->setQualifier('id', $id);
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
	
	function open($filter = '', $fields = 'f.*, g.groupId', $sort = 'id') {
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}Feeds f JOIN {$database['prefix']}FeedGroupRelations g ON f.id = g.feed WHERE g.owner = $owner $filter $sort");
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
		global $database, $owner;
		$this->id = null;
		$this->url = trim($this->url);
		if (empty($this->url))
			return $this->_error('url');
		if (!isset($this->group) || !Validator::number($this->group, 0))
			return $this->_error('group');

		$query = new TableQuery($database['prefix'] . 'Feeds');
		$query->setQualifier('xmlURL', mysql_lessen($this->url, 255), true);
		$query->setAttribute('title', mysql_lessen($this->url, 255), true);
		if (!$query->doesExist()) {
			if (!$query->insert())
				return $this->_error('insert');
		}
		$this->id = $query->getCell('id');
		
		$query->reset($database['prefix'] . 'FeedGroupRelations');
		$query->setQualifier('owner', $owner);
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
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}FeedItems i JOIN {$database['prefix']}FeedGroupRelations g ON i.feed = g.feed WHERE g.owner = $owner $filter $sort");
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
		global $database, $owner;
		$this->id = null;
		$this->link = mysql_lessen(trim($this->link), 255);
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

		$query = new TableQuery($database['prefix'] . 'FeedItems');
		$query->setQualifier('feed', $this->feed);
		$query->setQualifier('permalink', $this->link, true);
		$this->id = $query->getCell('id');
		if ($this->id === null) {
			$query->setAttribute('title', mysql_lessen($this->title, 255), true);
			$query->setAttribute('description', $this->description, true);
			$query->setAttribute('tags', mysql_lessen($this->tags, 255), true);
			$query->setAttribute('enclosure', mysql_lessen($this->enclosure, 255), true);
			$query->setAttribute('author', mysql_lessen($this->author, 255), true);
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
		global $database, $owner;
		if (isset($this->id))
			return DBQuery::queryExistence("SELECT * FROM {$database['prefix']}FeedReads WHERE owner = $owner AND item = {$this->id}");
		return false;
	}
	
	function setRead() {
		global $database, $owner;
		if (isset($this->id))
			return DBQuery::execute("INSERT INTO {$database['prefix']}FeedReads VALUES($owner, {$this->id})");
		return false;
	}
	
	function isStarred() {
		global $database, $owner;
		if (isset($this->id))
			return DBQuery::queryExistence("SELECT * FROM {$database['prefix']}FeedStarred WHERE owner = $owner AND item = {$this->id}");
		return false;
	}
	
	function setStarred() {
		global $database, $owner;
		if (isset($this->id))
			return DBQuery::execute("INSERT INTO {$database['prefix']}FeedStarred VALUES($owner, {$this->id})");
		return false;
	}
}

?>
