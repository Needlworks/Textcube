<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Cache_query {
	function __construct($query = null, $prefix = null){
		global $service;
		$this->reset();
		$this->query = $query;
		$this->prefix = $prefix;
	}
	public function reset() {
		$this->query = $this->queryHash = $this->contents = $this->error = $this->prefix = null;
	}
	public function create () {
		$this->setPageCacheLog();
		return true;
	}
	public function update () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		$this->purge();
		$this->create();
	}
	public function load () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		if($this->getPageCacheLog()) {
			return true;
		}
		else return false;
	}
	public function purge () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return true;
		if($this->removePageCacheLog())
			return true;
		else return false;
	}
	private function getQueryHash(){ 
		if(empty($this->query)) return false;
		$this->queryHash = (isset($this->prefix) ? $this->prefix.'_' : '')."queryCache_".abs(crc32($this->query));
	}
	private function getPageCacheLog() {
		global $database, $memcache;
/*		if(empty($this->queryHash)) $this->getQueryHash();
		
		if(!is_null($memcache)) {
			$result = $memcache->get(getBlogId().'-'.$this->queryHash);
		} else {*/
			$result = Data_IAdapter::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog 
				WHERE blogid = ".getBlogId()."
				AND name = '".Data_IAdapter::escapeString($this->queryHash)."'");
//		}
		if(!is_null($result) && !empty($result)) {
			$this->contents = unserialize($result);
			return true;
		} else {
			return false;
		}
	}

	private function setPageCacheLog() {
		global $database, $memcache;
		if(empty($this->queryHash)) $this->getQueryHash();
		
		if(!is_null($memcache)) {
			return $memcache->set(getBlogId().'-'.$this->queryHash,serialize($this->contents));
		} else {
			return Data_IAdapter::execute("REPLACE INTO {$database['prefix']}PageCacheLog 
				VALUES(".getBlogId().", '".Data_IAdapter::escapeString($this->queryHash)."', '".Data_IAdapter::escapeString(serialize($this->contents))."')");
		}
	}

	private function removePageCacheLog() {
		global $database, $memcache;
		if(empty($this->queryHash)) $this->getQueryHash();

		if(!is_null($memcache)) {
			return $memcache->delete(getBlogId().'-'.$this->queryHash);
		} else {
			return Data_IAdapter::execute("DELETE FROM {$database['prefix']}PageCacheLog 
				WHERE blogid = ".getBlogId()."
				AND name = '".Data_IAdapter::escapeString($this->queryHash)."'");
		}
	}

	private function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>