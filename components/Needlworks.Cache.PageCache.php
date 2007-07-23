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
		if(!file_exists($this->absoluteFilePathOwner) 
			&& !file_exists($this->absoluteFilePathGuest)) {
			$this->removePageCacheLog();
			return true;
		}
		if(chmod($this->absoluteFilePathOwner, 0777) || chmod($this->absoluteFilePathGuest, 0777)){
			@unlink($this->absoluteFilePathOwner);
			@unlink($this->absoluteFilePathGuest);
			$this->removePageCacheLog();
			return true;
		} else {
			return false;
		}
	}

	function getFileName(){
		if(empty($this->name)) return $this->_error('invalid name');
		$this->realName = $this->name."_".getBlogId().(doesHaveOwnership() ? '_owner' : '');
		$this->filename = md5($this->realName);
		$this->realNameOwner = $this->name."_".getBlogId()."_owner";
		$this->realNameGuest = $this->name."_".getBlogId();
		$this->filenameOwner = md5($this->realNameOwner);
		$this->filenameGuest = md5($this->realNameGuest);
		$this->absoluteFilePath = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filename;
		$this->absoluteFilePathOwner = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameOwner;
		$this->absoluteFilePathGuest = ROOT.'/cache/pageCache/'.getBlogId()."/".$this->filenameGuest;
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
			AND name = '".mysql_tt_escape_string($this->realNameOwner)."'") ||
			DBQuery::execute("DELETE FROM {$database['prefix']}PageCacheLog 
				WHERE blogid = ".getBlogId()."
				AND name = '".mysql_tt_escape_string($this->realNameGuest)."'");
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}

}

class CacheControl{
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
			AND name like 'tagList_".$tagId."%'");
		foreach($tagLists as $tagListName){
			$cache->reset();
			$cache->name = $tagListName;
			$cache->purge();
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
	
	}

	function flushItemsByPlugin($pluginName) {
		global $databases;

		$xmls = new XMLStruct();
		$manifest = @file_get_contents(ROOT . "/plugins/$pluginName/index.xml");
		if ($manifest && $xmls->open($manifest)) {
			if ($xmls->doesExist('/plugin/binding/listener')) {
				foreach ($xmls->selectNodes('/plugin/binding/listener') as $listener) {
					if (!empty($listener['.attributes']['event']) && !empty($listener['.value'])) {
						if (!isset($eventMappings[$listener['.attributes']['event']]))
							$eventMappings[$listener['.attributes']['event']] = array();
						array_push($eventMappings[$listener['.attributes']['event']], array('plugin' => $plugin, 'listener' => $listener['.value']));
					}
				}
				unset($listener);
			}
			if ($xmls->doesExist('/plugin/binding/tag')) {
				foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
					if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
						if (!isset($tagMappings[$tag['.attributes']['name']]))
							$tagMappings[$tag['.attributes']['name']] = array();
						array_push($tagMappings[$tag['.attributes']['name']], array('plugin' => $plugin, 'handler' => $tag['.attributes']['handler']));
					}
				}
				unset($tag);
			}
			if ($xmls->doesExist('/plugin/binding/sidebar')) {
				$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
				foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
					if (!empty($sidebar['.attributes']['handler'])) {
						// parameter parsing
						$parameters = array();
						if (isset($sidebar['params']) && isset($sidebar['params'][0]) && isset($sidebar['params'][0]['param'])) {
							foreach($sidebar['params'][0]['param'] as $param) {
								$parameter = array('name' => $param['name'][0]['.value'], 'type' => $param['type'][0]['.value'], 'title' => XMLStruct::getValueByLocale($param['title']));
								array_push($parameters, $parameter);				
							}
						}
						array_push($sidebarMappings, array('plugin' => $plugin, 'title' => $sidebar['.attributes']['title'], 'display' => $title, 'handler' => $sidebar['.attributes']['handler'], 'parameters' => $parameters));
					}
				}
				unset($sidebar);
			}
			if ($xmls->doesExist('/plugin/binding/formatter[lang()]')){
				$isFormatterExists = true;
				foreach (array($xmls->selectNode('/plugin/binding/formatter[lang()]')) as $formatter) {
					if (!isset($formatter['.attributes']['name'])) continue;
					if (!isset($formatter['.attributes']['id'])) continue;
					$formatterid = $formatter['.attributes']['id'];
					$formatterinfo = array('id' => $formatterid, 'name' => $formatter['.attributes']['name'], 'plugin' => $plugin, 'editors' => array());
					if (isset($formatter['format'][0]['.value'])) $formatterinfo['formatfunc'] = $formatter['format'][0]['.value'];
					if (isset($formatter['summary'][0]['.value'])) $formatterinfo['summaryfunc'] = $formatter['summary'][0]['.value'];
					if (isset($formatter['usedFor'])) {
						foreach ($formatter['usedFor'] as $usedFor) {
							if (!isset($usedFor['.attributes']['editor'])) continue;
							$formatterinfo['editors'][$usedFor['.attributes']['editor']] = @$usedFor['.value'];
						}
					}
					$formatterMapping[$formatterid] = $formatterinfo;
				}
				unset($formatter);
				unset($formatterid);
				unset($formatterinfo);
				unset($usedFor);
			}
			
		}
	}
}

?>
