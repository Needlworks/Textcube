<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class pageCache {
	var $name;
	var $realName;
	var $realNameOwner;
	var $realNameGuest;
	var $filename;
	var $filenameOwner;
	var $filenameGuest;
	var $contents;
	var $absoluteFilePath;
	var $absoluteFilePathOwner;
	var $absoluteFilePathGuest;
	var $error;

	function reset() {
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
		$this->error = 
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
		if(isset($service['disablePageCache']) && $service['disablePageCache'] == true) return false;
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
		if(isset($service['disablePageCache']) && $service['disablePageCache'] == true) return true;
		$this->getFileName();
		if(
			(file_exists($this->absoluteFilePathOwner) && @chmod($this->absoluteFilePathOwner, 0777))
			|| (file_exists($this->absoluteFilePathGuest) && @chmod($this->absoluteFilePathGuest, 0777))
			)
		{
			if(file_exists($this->absoluteFilePathOwner)) @unlink($this->absoluteFilePathOwner);
			if(file_exists($this->absoluteFilePathGuest)) @unlink($this->absoluteFilePathGuest);
			$this->removePageCacheLog();
			return true;
		} else {
			$this->removePageCacheLog();
			return false;
		}
	}

	function getFileName(){
		if(empty($this->name)) return $this->_error('invalid name');
		$this->realName = $this->name;
		$this->realNameOwner = $this->name."_".getBlogId()."_owner";
		$this->realNameGuest = $this->name."_".getBlogId();
		$this->filenameOwner = md5($this->realNameOwner);
		$this->filenameGuest = md5($this->realNameGuest);
		$this->filename = doesHaveOwnership() ? $this->filenameOwner : $this->filenameGuest;
		$this->absoluteFilePathOwner = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameOwner;
		$this->absoluteFilePathGuest = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameGuest;
		$this->absoluteFilePath = doesHaveOwnership() ? $this->absoluteFilePathOwner : $this->absoluteFilePathGuest;
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
		return DBQuery::queryExistence("DESC {$database['prefix']}PageCacheLog 
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
	function flushAll($blogid = null) {
		if(empty($blogid)) $blogid = getBlogId();
		
		$dir = ROOT . '/cache/pageCache/'.$blogid;
		if(!$dirHandle = opendir($dir)) return true;
		while(($object = readdir($dirHandle))) {
			if($object == '.' || $object == '..') continue;
			if(!@unlink($dir.'/'.$object)) return false;
		}
		DBQuery::query("DELETE FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$blogid);
		return true;
	}
	
	function flushCategory($categoryId = null) {
		global $database;

		if(empty($categoryId)) $categoryId = '';
		else $categoryId = $categoryId.'_';
		
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

	function flushTag($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'_';
		$cache = new pageCache;
		$tagLists = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'tagList_".$tagId."%' OR name like 'tagEntries_".$tagId."%'");
		if (!is_null($tagLists)) {
			foreach($tagLists as $tagListName){
				$cache->reset();
				$cache->name = $tagListName;
				$cache->purge();
			}
		}
		$cache->reset();
		$cache->name = 'tagPage';
		$cache->purge();
		unset($cache);
		return true;
	}

	function flushKeyword($tagId) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'_';
		$cache = new pageCache;
		$keywordEntries = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'keyword_".$tagId."%'");
		foreach($keywordEntries as $keywordEntryName){
			$cache->reset();
			$cache->name = $keywordEntryName;
			$cache->purge();
		}
		unset($cache);
		return true;
	}
	
	function flushEntry($entryId) {
		global $database;

		if(empty($entryId)) $entryId = '';
		else $entryId = $entryId.'_';
		$cache = new pageCache;
		$Entries = DBQuery::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'entry_".$tagId."%'");
		foreach($Entries as $EntryName){
			$cache->reset();
			$cache->name = $EntryName;
			$cache->purge();
		}
		unset($cache);
		return true;
	}

	function flushItemsByPlugin($pluginName) {
		global $databases;

		$xmls = new XMLStruct();
		$manifest = @file_get_contents(ROOT . "/plugins/$pluginName/index.xml");
		if ($manifest && $xmls->open($manifest)) {
			if ($xmls->doesExist('/plugin/binding/listener')) { //event listener가 있는 경우
				foreach ($xmls->selectNodes('/plugin/binding/listener') as $listener) {
					if (!empty($listener['.attributes']['event']) && !empty($listener['.value'])) { // Event가 있는 경우
						if(strpos(strtolower($listener['.attributes']['event']),'view')!==false) {
							CacheControl::flushCategory();
						}
					}
				}
				unset($listener);
			}
			if ($xmls->doesExist('/plugin/binding/tag')) {
				foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
					if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
						CacheControl::flushCategory();
						CacheControl::flushTag();
					}
				}
				unset($tag);
			}
//			if ($xmls->doesExist('/plugin/binding/sidebar')) {
//			TODO:	사이드바 캐시때 처리하도록 하지요.				
//			}
			if ($xmls->doesExist('/plugin/binding/formatter[lang()]')){
				CacheControl::flushCategory();
			}
			
		}
	}
}

?>
