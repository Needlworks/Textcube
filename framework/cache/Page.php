<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Cache_Page {
	function __construct($name = null){
		$this->reset();
		if($name != null) $this->name = $name;
	}
	public function reset() {
		$this->name = 
		$this->realName = 
		$this->realNameOwner = 
		$this->realNameGuest = 
		$this->filename =
		$this->filenameOwner =
		$this->filenameGuest =
		$this->absoluteFilePath =
		$this->absoluteFilePathOwner =
		$this->absoluteFilePathGuest =
		$this->contents =
		$this->dbContents =
		$this->_dbContents =
		$this->_fileCacheOnly =
		$this->error = 
		null;
	}

	private function create () {
		$this->initialize();
		if(!$this->getFileName()) return false;
		if(file_exists($this->absoluteFilePath))
			$this->purge();
		if(empty($this->contents)) return $this->_error('No contents');	
		$fileHandle = fopen($this->absoluteFilePath,'w');
		if(fwrite($fileHandle, $this->contents)){
			fclose($fileHandle);
			if(empty($this->_fileCacheOnly)) $this->setPageCacheLog();
			@chmod($this->absoluteFilePath, 0666);
			return true;
		}
		fclose($fileHandle);
		return false;
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
		$this->initialize();
		if(!$this->getFileName()) return false;
		if(empty($this->_fileCacheOnly)) $this->getdbContents();
		if($this->getFileContents()) {
			return true;
		}
		else return false;
	}

	private function initialize() {
		 if (!is_dir(ROOT."/cache/pageCache/".getBlogId())){
			 if(!is_dir(ROOT."/cache/pageCache")) {
				 @mkdir(ROOT."/cache/pageCache");
				 @chmod(ROOT."/cache/pageCache",0777);
			 }
			 @mkdir(ROOT."/cache/pageCache/".getBlogId());
			 @chmod(ROOT."/cache/pageCache/".getBlogId(),0777);
		 }
	}
	public function purge () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return true;
		$this->getFileName();
		if(
			(file_exists($this->absoluteFilePathOwner) && @chmod($this->absoluteFilePathOwner, 0777))
			|| (file_exists($this->absoluteFilePathGuest) && @chmod($this->absoluteFilePathGuest, 0777))
			)
		{
			if(file_exists($this->absoluteFilePathOwner)) @unlink($this->absoluteFilePathOwner);
			if(file_exists($this->absoluteFilePathGuest)) @unlink($this->absoluteFilePathGuest);
			if(empty($this->_fileCacheOnly)) $this->removePageCacheLog();
			return true;
		} else {
			if(empty($this->_fileCacheOnly)) $this->removePageCacheLog();
			return false;
		}
	}

	private function getFileName(){
		if(empty($this->name)) return $this->_error('invalid name');
		$this->realName = $this->name;
		$this->realNameOwner = $this->name."_".getBlogId()."_owner";
		$this->realNameGuest = $this->name."_".getBlogId();
		$this->filenameOwner = abs(crc32($this->realNameOwner)).'.cache';
		$this->filenameGuest = abs(crc32($this->realNameGuest)).'.cache';
		$this->filename = doesHaveOwnership() ? $this->filenameOwner : $this->filenameGuest;
		$this->absoluteFilePathOwner = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameOwner;
		$this->absoluteFilePathGuest = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameGuest;
		$this->absoluteFilePath = doesHaveOwnership() ? $this->absoluteFilePathOwner : $this->absoluteFilePathGuest;
		return true;
	}

	private function getFileContents() {
		if(!isset($this->absoluteFilePath)) return false;
		if(!file_exists($this->absoluteFilePath)) {
			return $this->_error('no file exists');
		}
		$this->contents = @file_get_contents($this->absoluteFilePath);
		if(!isset($this->contents)) {
			return $this->_error('content');
		}
		return true;
	}

	private function getdbContents() {
		global $database;
		return $this->getPageCacheLog();
	}

	private function getPageCacheLog() {
		global $database;
		$result = Data_IAdapter::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".Data_IAdapter::escapeString($this->realName)."'");
		if(!is_null($result)) {
			$this->_dbContents = unserialize($result);
			if(doesHaveOwnership()) $this->dbContents = isset($this->_dbContents['owner']) ? $this->_dbContents['owner'] : null;
			else $this->dbContents = isset($this->_dbContents['user']) ? $this->_dbContents['user'] : null;
		} else {
			return false;
		}
		return true;
	}

	private function setPageCacheLog() {
		global $database;
		if(doesHaveOwnership()) $this->_dbContents['owner'] = $this->dbContents;
		else $this->_dbContents['user'] = $this->dbContents;
		return Data_IAdapter::execute("REPLACE INTO {$database['prefix']}PageCacheLog 
			VALUES(".getBlogId().", '".Data_IAdapter::escapeString($this->realName)."', '".Data_IAdapter::escapeString(serialize($this->_dbContents))."')");
	}

	private function removePageCacheLog() {
		global $database;
		return Data_IAdapter::execute("DELETE FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".Data_IAdapter::escapeString($this->realName)."'"); 
	}

	private function _error($error) {
		$this->error = $error;
		return false;
	}

}
?>