<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class pageCache {
/*	var $name;
	var $realName;
	var $realNameOwner;
	var $realNameGuest;
	var $filename;
	var $filenameOwner;
	var $filenameGuest;
	var $contents;
	var $dbContents;
	var $_dbContents;
	var $absoluteFilePath;
	var $absoluteFilePathOwner;
	var $absoluteFilePathGuest;
	var $error;*/
	function pageCache($name = null){
		$this->reset();
		if($name != null) $this->name = $name;
	}
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
		$this->dbContents =
		$this->_dbContents =
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
		global $service;
		if(isset($service['disablePageCache']) && $service['disablePageCache'] == true) return false;
		$this->purge();
		$this->create();
	}

	function load () {
		global $service;
		if(isset($service['disablePageCache']) && $service['disablePageCache'] == true) return false;
		$this->initialize();
		if(!$this->getFileName()) return false;
		$this->getdbContents();
		if($this->getFileContents()) {
			return true;
		}
		else return false;
	}

	function initialize() {
		 if (!is_dir(ROOT."/cache/pageCache/".getBlogId())){
			 if(!is_dir(ROOT."/cache/pageCache")) {
				 @mkdir(ROOT."/cache/pageCache");
				 @chmod(ROOT."/cache/pageCache",0777);
			 }
			 @mkdir(ROOT."/cache/pageCache/".getBlogId());
			 @chmod(ROOT."/cache/pageCache/".getBlogId(),0777);
		 }
	}
	function purge () {
		global $service;
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
		$this->filenameOwner = crc32($this->realNameOwner);
		$this->filenameGuest = crc32($this->realNameGuest);
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

	function getdbContents() {
		global $database;
		return $this->getPageCacheLog();
	}

	function getPageCacheLog() {
		global $database;
		$result = POD::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".POD::escapeString($this->realName)."'");
		if(!is_null($result)) {
			$this->_dbContents = unserialize($result);
			if(doesHaveOwnership()) $this->dbContents = isset($this->_dbContents['owner']) ? $this->_dbContents['owner'] : null;
			else $this->dbContents = isset($this->_dbContents['user']) ? $this->_dbContents['user'] : null;
		} else {
			return false;
		}
		return true;
	}

	function setPageCacheLog() {
		global $database;
		if(doesHaveOwnership()) $this->_dbContents['owner'] = $this->dbContents;
		else $this->_dbContents['user'] = $this->dbContents;
		return POD::execute("REPLACE INTO {$database['prefix']}PageCacheLog 
			VALUES(".getBlogId().", '".POD::escapeString($this->realName)."', '".tc_escape_string(serialize($this->_dbContents))."')");
	}

	function removePageCacheLog() {
		global $database;
		return POD::execute("DELETE FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".POD::escapeString($this->realName)."'"); 
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}

}

class CacheControl{
	function flushAll($blogid = null) {
		global $database;
		if(empty($blogid)) $blogid = getBlogId();
		
		$dir = ROOT . '/cache/pageCache/'.$blogid;
		if( !file_exists($dir) ) return true;
		if(!($dirHandle = @opendir($dir))) return true;
		while(($object = readdir($dirHandle))) {
			if($object == '.' || $object == '..') continue;
			if(!@unlink($dir.'/'.$object)) return false;
		}
		@rmdir($dir);
		POD::query("DELETE FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$blogid);
		return true;
	}
	
	function flushCategory($categoryId = null) {
		global $database;

		if(empty($categoryId)) $categoryId = '';
		else $categoryId = $categoryId.'\\_';
		
		$cache = new pageCache;
		$categoryLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'categoryList\\_".$categoryId."%')");
		CacheControl::purgeItems($categoryLists);
		unset($cache);
		return true;
	}

	function flushAuthor($authorId = null) {
		global $database;

		if(empty($authorId)) $authorId = '';
		else $authorId = POD::escapeString($authorId).'\\_';
		
		$cache = new pageCache;
		$pageLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'authorList\\_".$authorId."%')");
		CacheControl::purgeItems($pageLists);
		unset($cache);
		return true;
	}

	function flushTag($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'\\_';
		$cache = new pageCache;
		$tagLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'tagList\\_".$tagId."%' 
				OR name like 'keyword\\_".$tagId."%')");
		CacheControl::purgeItems($tagLists);
		$cache->reset();
		$cache->name = 'tagPage';
		$cache->purge();
		unset($cache);
		return true;
	}

	function flushKeyword($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'\\_';
		$cache = new pageCache;
		$keywordEntries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'keyword\\_".$tagId."%'");
		CacheControl::purgeItems($keywordEntries);
		unset($cache);
		return true;
	}
	
	function flushEntry($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		else $entryId = $entryId.'\\_';
		$cache = new pageCache;
		$Entries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'entry\\_".$entryId."%' OR name = commentRSS_'.$entryId.')");
		CacheControl::purgeItems($Entries);
		$entry = POD::queryCell("SELECT userid, category FROM {$database['prefix']}Entries
				WHERE blogid = $blogid AND id = $entryId");
		if(!empty($entry)) {
			CacheControl::flushAuthor($entry['userid']);
			CacheControl::flushCategory($entry['category']);
		}
		unset($cache);
		return true;
	}

	function flushCommentRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = new pageCache;
		$cache->name = 'commentRSS_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentRSS';
		$cache->purge();
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
	function purgeItems($items) {
		if(!empty($items)) {
			$cache = new pageCache;
			foreach($items as $item){
				$cache->reset();
				$cache->name = $item;
				$cache->purge();
			}
		}	
	}
}

class MMCache{
	/*var $variable;*/
	
	//Variable must be the table form. (2-dimensional recursive structure)
	function queryRow($var, $key, $value) {
		$init = false;
		foreach($var as $row){
			if($init == false) {
				if(!isset($row[$key])) return false;
				$init = true;
			}
			if($row[$key] == $value) return $row;
		}
		return false;
	}
	function queryAll($var, $key, $value) {
		$init = false;
		$result = array();
		foreach($var as $row){
			if($init == false) {
				if(!isset($row[$key])) return false;
				$init = true;
			}
			if($row[$key] == $value) array_push($result, $row);
		}
		return $result;
	}
	function queryColumn($var, $key, $value, $column){
		$init = false;
		$result = array();
		foreach($var as $row){
			if($init == false) {
				if(!isset($row[$key])) return false;
				$init = true;
			}
			if($row[$key] == $value) array_push($result, $row[$column]);
		}
		return $result;
	}
}
?>
