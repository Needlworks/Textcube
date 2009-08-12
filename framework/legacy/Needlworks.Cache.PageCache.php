<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class pageCache {
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
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setQualifier('blogid','equals',getBlogId());
		$query->setQualifier('name','equals',$this->realName,true);
		$result = $query->getCell('value');
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
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setAttribute('blogid',getBlogId());
		$query->setAttribute('name',$this->realName,true);
		$query->setAttribute('value',serialize($this->_dbContents),true);
		$query->setQualifier('blogid','equals',getBlogId());
		$query->setQualifier('name','equals',$this->realName,true);
		return $query->replace();
	}

	private function removePageCacheLog() {
		global $database;
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setQualifier('blogid','equals',getBlogId());
		$query->setQualifier('name','equals',$this->realName,true);
		return $query->delete();
	}

	private function _error($error) {
		$this->error = $error;
		return false;
	}

}

class queryCache {
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
		if(empty($this->queryHash)) $this->getQueryHash();
		
		if(!is_null($memcache)) {
			$result = $memcache->get(getBlogId().'-'.$this->queryHash);
		} else {
			$query = new DBModel($database['prefix'].'PageCacheLog');
			$query->setQualifier('blogid','equals',getBlogId());
			$query->setQualifier('name','equals',$this->queryHash,true);
			$result = $query->getCell('value');
		}
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

			$name = $this->queryHash;
			$value = serialize($this->contents);
			$query = new DBModel($database['prefix'].'PageCacheLog');
			$query->setAttribute('blogid',getBlogId());
			$query->setAttribute('name',$name,true);
			$query->setAttribute('value',$value,true);
			$query->setQualifier('blogid','equals',getBlogId());
			$query->setQualifier('name','equals',$name,true);
			return $query->replace();
		}
	}

	private function removePageCacheLog() {
		global $database, $memcache;
		if(empty($this->queryHash)) $this->getQueryHash();

		if(!is_null($memcache)) {
			return $memcache->delete(getBlogId().'-'.$this->queryHash);
		} else {
			$query = new DBModel($database['prefix'].'PageCacheLog');
			$query->setQualifier('blogid','equals',getBlogId());
			$query->setQualifier('name','equals',$this->queryHash,true);
			return $query->delete();
		}
	}

	private function _error($error) {
		$this->error = $error;
		return false;
	}
}

// globalCacheStorage caches essential but 'relatively static' information, like
// blogSettings, ServiceSettings, activePlugins, etc..
// Textcube will use it as global object.
class globalCacheStorage extends pageCache {
	function __construct($blogid = null) {
		$this->_isChanged = false;
		$this->_gCacheStorage = array();
		if(is_null($blogid)) $this->_gBlogId = getBlogId();
		else $this->_gBlogId = $blogid;
	}
	
	function load() {
		global $database, $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setQualifier('blogid','equals',$this->_gBlogId);
		$query->setQualifier('name','equals','globalCacheStorage',true);
		$result = $query->getCell('value');
		if(isset($result)) $this->_gCacheStorage[$this->_gBlogId] = unserialize($result);
	}

	function save() {
		global $database, $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		if($this->_isChanged) {	
			$query = new DBModel($database['prefix'].'PageCacheLog');
			$query->setAttribute('blogid',$this->_gBlogId);
			$query->setAttribute('name','globalCacheStorage',true);
			$query->setAttribute('value',serialize($this->_gCacheStorage[$this->_gBlogId]),true);
			$query->setQualifier('blogid','equals',$this->_gBlogId);
			$query->setQualifier('name','equals','globalCacheStorage',true);
			return $query->replace();
		}
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
		global $database, $service;
		if(isset($service['pagecache']) && $service['pagecache'] == false) return false;
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setQualifier('blogid','equals',$this->_gBlogId);
		$query->setQualifier('name','equals','globalCacheStorage',true);
		return $query->delete();		
	}
}

// CacheControl have functions for flushing caches.
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
		$query = new DBModel($database['prefix'].'PageCacheLog');
		$query->setQualifier('blogid','equals',$blogid);
		$query->delete();
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
			AND (name like 'categoryList\\_".$categoryId."%' 
				OR name like 'categoryRSS\\_".$categoryId."%'
				OR name like 'categoryATOM\\_".$categoryId."%')");
		CacheControl::purgeItems($categoryLists);
		CacheControl::flushRSS();
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
		$cache = new pageCache;
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
		$cache = new pageCache;
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
		$cache = new pageCache;
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
		$cache = new pageCache;
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
			$cache = new pageCache;
			foreach($items as $item){
				$cache->reset();
				$cache->name = $item;
				$cache->purge();
			}
		}	
	}
}

// MMCache is instant memory cache as table type data.
// Supports same methods as POD raw mode.
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
	function __construct() {
		$this->reset();
	}
	
	private function reset() {
		$this->code =
		$this->name =
		$this->fileName =
		null;
	}
	
	private function initialize() {
		 if (!is_dir(ROOT."/cache/code")){
			 @mkdir(ROOT."/cache/code");
			 @chmod(ROOT."/cache/code",0777);
		 }
	}
	
	public function save() {
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
	private function __getCodes() {
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
	
	private function _error($err = 0) {
		var_dump($err);
		return false;
		//return $err;
	}
}
?>
