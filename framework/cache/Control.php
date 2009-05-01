<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Cache_Control have functions for flushing caches.
class Cache_control {
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
		Data_IAdapter::query("DELETE FROM {$database['prefix']}PageCacheLog WHERE blogid = ".$blogid);
		return true;
	}
	
	function flushCategory($categoryId = null) {
		global $database;

		if(empty($categoryId)) $categoryId = '';
		else $categoryId = $categoryId.'\\_';
		
		$cache = new Cache_Page;
		$categoryLists = Data_IAdapter::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'categoryList\\_".$categoryId."%' 
				OR name like 'categoryRSS\\_".$categoryId."%'
				OR name like 'categoryATOM\\_".$categoryId."%')");
		Cache_Control::purgeItems($categoryLists);
		Cache_Control::flushRSS();
		unset($cache);
		return true;
	}

	function flushAuthor($authorId = null) {
		global $database;

		if(empty($authorId)) $authorId = '';
		else $authorId = Data_IAdapter::escapeString($authorId).'\\_';
		
		$cache = new Cache_Page;
		$pageLists = Data_IAdapter::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'authorList\\_".$authorId."%')");
		Cache_Control::purgeItems($pageLists);
		unset($cache);
		return true;
	}

	function flushTag($tagId = null) {
		global $database;

		if(empty($tagId)) $tagId = '';
		else $tagId = $tagId.'\\_';
		$cache = new Cache_Page;
		$tagLists = Data_IAdapter::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'tagList\\_".$tagId."%' 
				OR name like 'keyword\\_".$tagId."%')");
		Cache_Control::purgeItems($tagLists);
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
		$cache = new Cache_Page;
		$keywordEntries = Data_IAdapter::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like 'keyword\\_".$tagId."%'");
		Cache_Control::purgeItems($keywordEntries);
		unset($cache);
		return true;
	}
	
	function flushEntry($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		else $entryId = $entryId.'\\_';
		$cache = new Cache_Page;
		$Entries = Data_IAdapter::queryColumn("SELECT name
			FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND (name like 'entry\\_".$entryId."%' OR name = 'commentRSS_".$entryId."')");
		Cache_Control::purgeItems($Entries);
		if(!empty($entryId)) {
			$entry = Data_IAdapter::queryCell("SELECT userid, category FROM {$database['prefix']}Entries
				WHERE blogid = ".getBlogId()." AND id = $entryId");
			if(!empty($entry)) {
				Cache_Control::flushAuthor($entry['userid']);
				Cache_Control::flushCategory($entry['category']);
				Cache_Control::flushDBCache();
			}
		} else {
			Cache_Control::flushAuthor();
			Cache_Control::flushCategory();
			Cache_Control::flushDBCache();
		}
		unset($cache);
		return true;
	}
	function flushRSS() {
		if (file_exists(ROOT . "/cache/rss/".getBlogId().".xml"))
			@unlink(ROOT . "/cache/rss/".getBlogId().".xml");
		Cache_Control::flushCommentRSS();
		Cache_Control::flushTrackbackRSS();
		Cache_Control::flushResponseRSS();
	}

	function flushCommentRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = new Cache_Page;
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
		Cache_Control::flushResponseRSS($entryId);
		return true;
	}
	
	function flushTrackbackRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = new Cache_Page;
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
		Cache_Control::flushResponseRSS($entryId);
		return true;
	}
		
	function flushResponseRSS($entryId = null) {
		global $database;

		if(empty($entryId)) $entryId = '';
		$cache = new Cache_Page;
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
		$cache = new Cache_Page;
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
							Cache_Control::flushCategory();
						}
					}
				}
				unset($listener);
			}
			if ($xmls->doesExist('/plugin/binding/tag')) {
				foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
					if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
						Cache_Control::flushCategory();
						Cache_Control::flushTag();
					}
				}
				unset($tag);
			}
//			if ($xmls->doesExist('/plugin/binding/sidebar')) {
//			TODO:	사이드바 캐시때 처리하도록 하지요.				
//			}
			if ($xmls->doesExist('/plugin/binding/formatter[lang()]')){
				Cache_Control::flushCategory();
			}
			
		}
	}
	function flushDBCache($prefix = null) {
		global $database;
		return Data_IAdapter::query("DELETE FROM {$database['prefix']}PageCacheLog
			WHERE blogid = ".getBlogId()."
			AND name like '%".(!empty($prefix) ? $prefix.'\\_' : '')."queryCache%'");
	}
	function purgeItems($items) {
		if(!empty($items)) {
			$cache = new Cache_Page;
			foreach($items as $item){
				$cache->reset();
				$cache->name = $item;
				$cache->purge();
			}
		}	
	}
}
?>
