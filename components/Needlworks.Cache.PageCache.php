<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class pageCache {
	var $name;
	var $realName;
	var $filename;
	var $contents;
	var $absoluteFilePath;
	var $error;

	function reset() {
		$this->name = 
		$this->realName = 
		$this->error = 
		$this->filename =
		$this->absoluteFilePath =
		$this->contents =
		null;
	}

	function create () {
		$this->initialize();
		if(!$this->getFileName()) return false;
		if(file_exists($this->absoluteFilePath))
			$this->purge();
		if(empty($this->contents)) return $this->_error('No contents');	
		$fileHandle = fopen($this->absoluteFilePath,'w');
		if(fwrite($fileHandle, $this->contents)){
			fclose($fileHandle);
			$this->setPageCacheLog();
			@chmod($this->absoluteFilePath, 0666);
			return true;
		}
		fclose($fileHandle);
		return false;
	}

	function update () {
		$this->purge();
		$this->create();
	}

	function load () {
		$this->initialize();
		if(!$this->getFileName()) return false;
		if($this->getFileContents())
			return true;
		else return false;
	}

	function initialize() {
		 if (!is_dir(ROOT."/cache/pageCache/".getBlogId())){
			 @mkdir(ROOT."/cache/pageCache");
			 @mkdir(ROOT."/cache/pageCache/".getBlogId());
			 @chmod(ROOT."/cache/pageCache/".getBlogId(),0777);
		 }
	}
	function purge () {
		$this->getFileName();
		if(!file_exists($this->absoluteFilePath)) {
			$this->removePageCacheLog();
			return true;
		}
		if($this->getFileName() && chmod($this->absoluteFilePath,0777)){
			@unlink($this->absoluteFilePath);
			$this->removePageCacheLog();
			return true;
		} else {
			return false;
		}
	}

	function getFileName(){
		if(empty($this->name)) return $this->_error('invalid name');
		$this->realName = $this->name."_".getBlogId()."_".(doesHaveOwnership() ? 'owner' : '');
		$this->filename = md5($this->realName);
		$this->absoluteFilePath = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filename;
		return true;
	}

	function getFileContents() {
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
	function getPageCacheLog() {
		global $database;
		return DBQuery::queryCell("DESC {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".mysql_tt_escape_string($this->realName)."'");
	}

	function setPageCacheLog() {
		global $database;
		return DBQuery::execute("REPLACE INTO {$database['prefix']}PageCacheLog 
			VALUES(".getBlogId().", '".mysql_tt_escape_string($this->realName)."')");
	}

	function removePageCacheLog() {
		global $database;
		return DBQuery::execute("DELETE FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".mysql_tt_escape_string($this->realName)."'");
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}

}

class CacheControl{
	function flushCategory($categoryId) {
		global $database;
		$cache = new pageCache;
		$categoryLists = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'categoryList_".$categoryId."%'");
		foreach($categoryLists as $categoryListName){
			$cache->reset();
			$cache->name = $categoryListName;
			$cache->purge();
		}

		$categoryEntries = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'categoryEntries_".$categoryId."%'");
		foreach($categoryEntries as $categoryEntryName){
			$cache->reset();
			$cache->name = $categoryEntryName;
			$cache->purge();
		}
		unset($cache);
		return true;
	}

	function flushTag($tag) {
		global $database;

		$tagId = getTagId($blogid, $tag);
		$cache = new pageCache;
		$tagLists = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'tagList_".$tagId."%'");
		foreach($tagLists as $tagListName){
			$cache->reset();
			$cache->name = $tagListName;
			$cache->purge();
		}

		$keywordEntries = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'keyword_".$tag."_%'");
		foreach($keywordEntries as $keywordEntryName){
			$cache->reset();
			$cache->name = $keywordEntryName;
			$cache->purge();
		}
		unset($cache);
		return true;
	}
	
	function flushEntry($entryId) {
	
	}
}

?>
