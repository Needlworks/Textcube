<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

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
		$this->_fileCacheOnly =
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
			if(empty($this->_fileCacheOnly)) $this->setPageCacheLog();
			@chmod($this->absoluteFilePath, 0666);
			return true;
		}
		fclose($fileHandle);
		return false;
	}

	function update () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		$this->purge();
		$this->create();
	}

	function load () {
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

	function getFileName(){
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

class queryCache {
/*	var $query;
	var $queryHash;
	var $contents;
	var $prefix;
	var $error;*/
	function queryCache($query = null, $prefix = null){
		$this->reset();
		$this->query = $query;
		$this->prefix = $prefix;
	}
	function reset() {
		$this->query = $this->queryHash = $this->contents = $this->error = $this->prefix = null;
	}
	function create () {
		$this->setPageCacheLog();
		return true;
	}
	function update () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		$this->purge();
		$this->create();
	}
	function load () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		if($this->getPageCacheLog()) {
			return true;
		}
		else return false;
	}
	function purge () {
		global $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return true;
		if($this->removePageCacheLog())
			return true;
		else return false;
	}
	function getQueryHash(){ 
		if(empty($this->query)) return false;
		$this->queryHash = (isset($this->prefix) ? $this->prefix.'_' : '')."queryCache_".abs(crc32($this->query));
	}
	function getPageCacheLog() {
		global $database;
		if(empty($this->queryHash)) $this->getQueryHash();

		$result = POD::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".POD::escapeString($this->queryHash)."'");
		if(!is_null($result)) {
			$this->contents = unserialize($result);
			return true;
		} else {
			return false;
		}
	}

	function setPageCacheLog() {
		global $database;
		if(empty($this->queryHash)) $this->getQueryHash();
		return POD::execute("REPLACE INTO {$database['prefix']}PageCacheLog 
			VALUES(".getBlogId().", '".POD::escapeString($this->queryHash)."', '".tc_escape_string(serialize($this->contents))."')");
	}

	function removePageCacheLog() {
		global $database;
		if(empty($this->queryHash)) $this->getQueryHash();

		return POD::execute("DELETE FROM {$database['prefix']}PageCacheLog 
			WHERE blogid = ".getBlogId()."
			AND name = '".POD::escapeString($this->queryHash)."'"); 
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}

// globalCacheStorage caches essential but 'relatively static' information, like
// blogSettings, ServiceSettings, activePlugins, etc..
// Textcube will use it as global object.
class globalCacheStorage extends pageCache {
	function globalCacheStorage($blogid = null) {
		$this->_isChanged = false;
		$this->_gCacheStorage = array();
		if(is_null($blogid)) $this->_gBlogId = getBlogId();
		else $this->_gBlogId = $blogid;
	}
	
	function load() {
		global $database;
		$result = POD::queryCell("SELECT value FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$this->_gBlogId." AND name = 'globalCacheStorage'");
		if(isset($result)) $this->_gCacheStorage[$this->_gBlogId] = unserialize($result);
	}

	function save() {
		global $database;
		if($this->_isChanged) return POD::query("REPLACE INTO {$database['prefix']}PageCacheLog VALUES(".$this->_gBlogId.", 'globalCacheStorage', '".POD::escapeString(serialize($this->_gCacheStorage[$this->_gBlogId]))."')");
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
		return POD::query("DELETE FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$this->_gBlogId." AND name = 'globalCacheStorage'");
	}
}

class CacheControl {
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
		
		$cache = pageCache::getInstance();
		$categoryLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'categoryList\\_".$categoryId."%')");
		CacheControl::purgeItems($categoryLists);
		CacheControl::flushRSS();
		unset($cache);
		return true;
	}

	function flushAuthor($authorId = null) {
		global $database;

		if(empty($authorId)) $authorId = '';
		else $authorId = POD::escapeString($authorId).'\\_';
		
		$cache = pageCache::getInstance();
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
		$cache = pageCache::getInstance();
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
		$cache = pageCache::getInstance();
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
		$cache = pageCache::getInstance();
		$Entries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'entry\\_".$entryId."%' OR name = 'commentRSS_".$entryId."')");
		CacheControl::purgeItems($Entries);
		if(!empty($entryId)) {
			$entry = POD::queryCell("SELECT userid, category FROM {$database['prefix']}Entries
				WHERE blogid = ".getBlogId()." AND id = $entryId");
			if(!empty($entry)) {
				CacheControl::flushAuthor($entry['userid']);
				CacheControl::flushCategory($entry['category']);
				CacheControl::flushDBCache();
			}
		} else {
			CacheControl::flushAuthor();
			CacheControl::flushCategory();
			CacheControl::flushDBCache();
		}
		unset($cache);
		return true;
	}
	function flushRSS() {
		if (file_exists(ROOT . "/cache/rss/".getBlogId().".xml"))
			@unlink(ROOT . "/cache/rss/".getBlogId().".xml");
		CacheControl::flushCommentRSS();
		CacheControl::flushTrackbackRSS();
		CacheControl::flushResponseRSS();
	}

	function flushCommentRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'commentRSS_'.$entryId;
		$cache->purge(); 
		$cache->reset();
		$cache->name = 'commentRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentATOM_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentATOM';
		$cache->purge();
		CacheControl::flushResponseRSS($entryId);
		return true;
	}
	
	function flushTrackbackRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'trackbackRSS_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackATOM_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackATOM';
		$cache->purge();
		CacheControl::flushResponseRSS($entryId);
		return true;
	}
		
	function flushResponseRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'responseRSS_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'responseRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'responseATOM_'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'responseATOM';
		$cache->purge();
		return true;
	}

	function flushCommentNotifyRSS() {
		global $database;
		$cache = pageCache::getInstance();
		$cache->name = 'commentNotifiedRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentNotifiedATOM';
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
	function flushDBCache($prefix = null) {
		global $database;
		return POD::query("DELETE FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like '%".(!empty($prefix) ? $prefix.'\\_' : '')."queryCache%'");
	}
	function purgeItems($items) {
		if(!empty($items)) {
			$cache = pageCache::getInstance();
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
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) return $row;
		}
		return false;
	}
	function queryAll($var, $key, $value) {
		$result = array();
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) array_push($result, $row);
		}
		return $result;
	}
	function queryColumn($var, $key, $value, $column){
		$result = array();
		foreach($var as $row){
			if(isset($row[$key]) && $row[$key] == $value) array_push($result, $row[$column]);
		}
		return $result;
	}
}

class CodeCache {
	function CodeCache() {
		$this->reset();
	}
	
	function reset() {
		$this->code =
		$this->name =
		$this->fileName =
		null;
	}
	
	function initialize() {
		 if (!is_dir(ROOT."/cache/code")){
			 @mkdir(ROOT."/cache/code");
			 @chmod(ROOT."/cache/code",0777);
		 }
	}
	
	function save() {
		if(!empty($this->name)) $this->__getCodes();	// Get source codes.
		if(empty($this->code)) return $this->_error(2);
		$this->initialize();
		$this->fileName = ROOT."/cache/code/".$this->name;
		$fileHandle = fopen($this->fileName,'w');
		if(fwrite($fileHandle, $this->code)){
			fclose($fileHandle);
			@chmod($this->fileName, 0666);
			return true;
		}
		fclose($fileHandle);
		return $this->_error(3);
	}
	
	/*@ private @*/
	function __getCodes() {
		global $__requireComponent, $__requireView, $__requireLibrary, $__requireBasics, $__requireInit, $__requireModel;
		$code = '';
/*		foreach($__requireComponent as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/components/'.$lib.'.php');
		}*/
		foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/'.$lib.'.php');
		}
		foreach($__requireModel as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/model/'.$lib.'.php');
		}
		
		foreach($__requireView as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/view/'.$lib.'.php');
		}
		
		foreach($__requireInit as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/'.$lib.'.php');
		}
		$this->code = $code;
	}
	
	function _error($err = 0) {
		var_dump($err);
		return false;
		//return $err;
	}
}
?>
