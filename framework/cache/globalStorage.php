<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// globalCacheStorage caches essential but 'relatively static' information, like
// blogSettings, ServiceSettings, activePlugins, etc..
// Textcube will use it as global object.
class Cache_globalStorage extends Cache_page {
	function __construct($blogid = null) {
		$this->_isChanged = false;
		$this->_gCacheStorage = array();
		if(is_null($blogid)) $this->_gBlogId = getBlogId();
		else $this->_gBlogId = $blogid;
	}
	
	function load() {
		global $database;
		$result = Data_IAdapter::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$this->_gBlogId." AND name = 'globalCacheStorage'");
		if(isset($result)) $this->_gCacheStorage[$this->_gBlogId] = unserialize($result);
	}

	function save() {
		global $database;
		if($this->_isChanged) return Data_IAdapter::query("REPLACE INTO {$database['prefix']}PageCacheLog VALUES(".$this->_gBlogId.", 'globalCacheStorage', '".Data_IAdapter::escapeString(serialize($this->_gCacheStorage[$this->_gBlogId]))."')");
	}
	
	function getContent($name) {
		if(empty($this->_gCacheStorage)) $this->load();
		if(isset($this->_gCacheStorage[$this->_gBlogId][$name])) return $this->_gCacheStorage[$this->_gBlogId][$name];
		else return null;
	}

	function setContent($name, $value) {
		if(isset($this->_gCacheStorage[$this->_gBlogId][$name]) && $this->_gCacheStorage[$this->_gBlogId][$name] == $value) {return true;}
		else {
			$this->_gCacheStorage[$this->_gBlogId][$name] = $value;
			$this->_isChanged = true;
			return true;
		}
	}

	function purge() {
		global $database;
		return Data_IAdapter::query("DELETE FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$this->_gBlogId." AND name = 'globalCacheStorage'");
	}
}

?>