<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class pageCache extends Singleton {
	function __construct($name = null){
		$this->pool = DBModel::getInstance();
		$this->context = Model_Context::getInstance();
		$this->reset();
	}
    
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	public function reset($name = null) {
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
		if(!is_null($name)) $this->name = $name;
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
		if($this->context->getProperty('service.pagecache') != true) return false;
		$this->purge();
		$this->create();
	}

	public function load () {
		if($this->context->getProperty('service.pagecache') != true) return false;
		$this->initialize();
		$this->contents = $this->dbContents = null;
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
		if($this->context->getProperty('service.pagecache') != true) return true;
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
		return $this->getPageCacheLog();
	}

	private function getPageCacheLog() {
		$this->pool->reset('PageCacheLog');
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$this->realName,true);
		$result = $this->pool->getCell('value');
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
		if(doesHaveOwnership()) $this->_dbContents['owner'] = $this->dbContents;
		else $this->_dbContents['user'] = $this->dbContents;
		$this->pool->reset('PageCacheLog');
		$this->pool->setAttribute('blogid',getBlogId());
		$this->pool->setAttribute('name',$this->realName,true);
		$this->pool->setAttribute('value',serialize($this->_dbContents),true);
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$this->realName,true);
		return $this->pool->replace();
	}

	private function removePageCacheLog() {
		$this->pool->reset('PageCacheLog');
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$this->realName,true);
		return $this->pool->delete();
	}

	private function _error($error) {
		$this->error = $error;
		return false;
	}

}

class queryCache extends Singleton {
	private $pooltype = null;
	function __construct($query = null, $prefix = null){
		$this->reset();
	
		$this->context = Model_Context::getInstance();
		$this->__usePageCache = $this->context->getProperty('service.pagecache');
		
		if($this->context->getProperty('service.memcached') == true) {
			$this->pool = Cache_Memcache::getInstance();
			$this->pooltype = 'memcache';
		} else {
			$this->pool = DBModel::getInstance();
			$this->pooltype = 'db';
		}
	}

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	public function reset($query = null, $prefix = null) {
		$this->query = $this->queryHash = $this->contents = $this->error = $this->prefix = $this->namespace = null;
		$this->query = $query;
		if(!is_null($prefix)) {
			$this->prefix = $prefix."-";
			$this->namespace = $this->prefix;
		}
		
	}
	
	public function create() {
		$this->setPageCacheLog();
		return true;
	}
	public function update() {
		if(empty($this->__usePageCache)) return false;
		$this->purge();
		$this->create();
	}
	public function load() {
		if(empty($this->__usePageCache)) return false;
		if($this->getPageCacheLog()) {
			return true;
		}
		else return false;
	}
	public function purge() {
		if(empty($this->__usePageCache)) return false;
		if($this->removePageCacheLog())
			return true;
		else return false;
	}
	public function flush() {
		if(empty($this->__usePageCache)) return false;
		$this->pool->reset('PageCacheLog',$this->prefix);
		if($this->pooltype == 'memcache') {	
			$this->pool->flush();
		} else {
			$this->pool->setQualifier('blogid','equals',getBlogId());
			$this->pool->setQualifier('name','like',$this->prefix,true);
			$this->pool->delete();
		}
	}
	
	private function getQueryHash(){ 
		if(empty($this->query)) return false;
		$this->queryHash = $this->namespace."queryCache-".abs(crc32($this->query));
	}
	
	private function getPageCacheLog() {
		if(empty($this->queryHash)) $this->getQueryHash();
		
		$this->pool->reset('PageCacheLog',$this->prefix);
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$this->queryHash,true);
		$result = $this->pool->getCell('value');

		if(!is_null($result) && !empty($result)) {
			$this->contents = unserialize($result);
			return true;
		} else {
			return false;
		}
	}

	private function setPageCacheLog() {
		if(empty($this->queryHash)) $this->getQueryHash();
		
		$name = $this->queryHash;
		$value = serialize($this->contents);
		$this->pool->reset('PageCacheLog',$this->prefix);
		$this->pool->setAttribute('blogid',getBlogId());
		$this->pool->setAttribute('name',$name,true);
		$this->pool->setAttribute('value',serialize($this->contents),true);
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$name,true);
		return $this->pool->replace();
	}

	private function removePageCacheLog() {
		if(empty($this->queryHash)) $this->getQueryHash();

		$this->pool->reset('PageCacheLog',$this->prefix);
		$this->pool->setQualifier('blogid','equals',getBlogId());
		$this->pool->setQualifier('name','equals',$this->queryHash,true);
		return $this->pool->delete();
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
		$this->context = Model_Context::getInstance();
		$this->__usePageCache = $this->context->getProperty('service.pagecache');
		if(is_null($blogid)) $this->_gBlogId = getBlogId();
		else $this->_gBlogId = $blogid;
		if($this->context->getProperty('service.memcached') == true) {
			$this->pool = Cache_Memcache::getInstance();
		} else {
			$this->pool = DBModel::getInstance();
		}
	}

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	function load() {
		if(empty($this->__usePageCache)) return false;
		$this->pool->reset('PageCacheLog');
		$this->pool->setQualifier('blogid','equals',$this->_gBlogId);
		$this->pool->setQualifier('name','equals','globalCacheStorage',true);
		$result = $this->pool->getCell('value');
		if(isset($result)) $this->_gCacheStorage[$this->_gBlogId] = unserialize($result);
	}

	function save() {
		if(empty($this->__usePageCache)) return false;
		if($this->_isChanged) {	
			$this->pool->reset('PageCacheLog');
			$this->pool->setAttribute('blogid',$this->_gBlogId);
			$this->pool->setAttribute('name','globalCacheStorage',true);
			$this->pool->setAttribute('value',serialize($this->_gCacheStorage[$this->_gBlogId]),true);
			$this->pool->setQualifier('blogid','equals',$this->_gBlogId);
			$this->pool->setQualifier('name','equals','globalCacheStorage',true);
			return $this->pool->replace();
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
		if(empty($this->__usePageCache)) return false;
		$this->pool->reset('PageCacheLog');
		$this->pool->setQualifier('blogid','equals',$this->_gBlogId);
		$this->pool->setQualifier('name','equals','globalCacheStorage',true);
		return $this->pool->delete();		
	}
}

// CacheControl have functions for flushing caches.
class CacheControl {
	function flushAll($blogid = null) {
		if(empty($blogid)) $blogid = getBlogId();
		
		$dir = ROOT . '/cache/pageCache/'.$blogid;
		if( !file_exists($dir) ) return true;
		if(!($dirHandle = @opendir($dir))) return true;
		while(($object = readdir($dirHandle))) {
			if($object == '.' || $object == '..') continue;
			if(!@unlink($dir.'/'.$object)) return false;
		}
		@rmdir($dir);
		$query = DBModel::getInstance();
		$query->reset('PageCacheLog');
		$query->setQualifier('blogid','equals',$blogid);
		$query->delete();
		return true;
	}
	function flushSkin($blogid = null) {
		global $gCacheStorage;
		if(empty($blogid)) $blogid = getBlogId();
		$cache = pageCache::getInstance();
		$cache->reset('skinCache');
		$cache->purge();
		$gCacheStorage->purge();
	}
	function flushCategory($categoryId = null) {
		global $database;

		if(empty($categoryId)) $categoryId = '';
		else $categoryId = $categoryId.'-';
		
		$categoryLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'categoryList-".$categoryId."%' 
				OR name like 'categoryRSS-".$categoryId."%'
				OR name like 'categoryATOM-".$categoryId."%')");
		CacheControl::purgeItems($categoryLists);
		CacheControl::flushRSS();
		return true;
	}

	function flushAuthor($authorId = null) {
		global $database;
		if(empty($authorId)) $authorId = '';
		else $authorId = POD::escapeString($authorId).'-';
		
		$pageLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'authorList-".$authorId."%')");
		CacheControl::purgeItems($pageLists);
		return true;
	}

	function flushTag($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'-';
		$cache = pageCache::getInstance();
		$tagLists = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'tagList-".$tagId."%' 
				OR name like 'keyword-".$tagId."%'
				OR name like 'tagATOM-".$tagId."%'
				OR name like 'tagRSS-".$tagId."%')");
		CacheControl::purgeItems($tagLists);
		CacheControl::flushRSS();
		$cache->reset();
		$cache->name = 'tagPage';
		$cache->purge();
		return true;
	}

	function flushKeyword($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'-';
		$keywordEntries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'keyword-".$tagId."%'");
		CacheControl::purgeItems($keywordEntries);
		return true;
	}
	
	function flushSearchKeywordRSS($search = null) {
		global $database;

		if(empty($search)) $search = '';
		else $search = escapeSearchString($search);
		$searchEntries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'searchATOM-".$search."%'
				OR name like 'searchRSS-".$search."%')");
		if(!empty($searchEntries)) CacheControl::purgeItems($searchEntries);
		return true;
	}
	
	function flushEntry($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		else $entryId = intval($entryId);
		
		$Entries = POD::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'entry-".$entryId."-%' OR name like '%RSS-".$entryId."' OR name like '%ATOM-".$entryId."')");
		if(!empty($Entries)) CacheControl::purgeItems($Entries);
		if(!empty($entryId)) {
			$entry = POD::queryCell("SELECT userid, category FROM {$database['prefix']}Entries
				WHERE blogid = ".getBlogId()." AND id = $entryId");
			if(!empty($entry)) {
				CacheControl::flushAuthor($entry['userid']);
				CacheControl::flushCategory($entry['category']);
				CacheControl::flushDBCache('entry');
			}
		} else {
			CacheControl::flushAuthor();
			CacheControl::flushCategory();
			CacheControl::flushDBCache('entry');
		}
		return true;
	}
	function flushRSS() {
		if (file_exists(ROOT . "/cache/rss/".getBlogId().".xml"))
			@unlink(ROOT . "/cache/rss/".getBlogId().".xml");
		CacheControl::flushCommentRSS();
		CacheControl::flushTrackbackRSS();
		CacheControl::flushResponseRSS();
		CacheControl::flushSearchKeywordRSS();
	}

	function flushCommentRSS($entryId = null) {
		global $database;
		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'commentRSS-'.$entryId;
		$cache->purge(); 
		$cache->reset();
		$cache->name = 'commentRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentATOM-'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'commentATOM';
		$cache->purge();
		CacheControl::flushResponseRSS($entryId);
		return true;
	}
	
	function flushTrackbackRSS($entryId = null) {
		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'trackbackRSS-'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackATOM-'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'trackbackATOM';
		$cache->purge();
		CacheControl::flushResponseRSS($entryId);
		return true;
	}
		
	function flushResponseRSS($entryId = null) {
		if(empty($entryId)) $entryId = '';
		$cache = pageCache::getInstance();
		$cache->name = 'responseRSS-'.$entryId;
		$cache->purge();
		$cache->reset();
		$cache->name = 'responseRSS';
		$cache->purge();
		$cache->reset();
		$cache->name = 'responseATOM-'.$entryId;
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
		$pool = queryCache::getInstance();
		$pool->reset('PageCacheLog',$prefix);
		return $pool->flush();
/*		
		global $database;
		$context = Model_Context::getInstance();
		if($context->getProperty('service.memcached') !== null) {
			$memcache = Cache_Memcache::getInstance();
			$memcache->reset('PageCacheLog',$prefix);
			return $memcache->flush();
		}
		return POD::query("DELETE FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like '%".(!empty($prefix) ? $prefix.'-' : '')."queryCache%'");*/
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
