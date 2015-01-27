<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getLinks($blogid, $sort="category") {
	$context = Model_Context::getInstance();
	$__gCacheLink = $context->getProperty("cache.links",array());
	if(empty($__gCacheLink)) {
		$pool = DBModel::getInstance();
		$pool->init("Links");
		$pool->setAlias("Links","l");
		$pool->setAlias("LinkCategories","lc");
		$pool->join("LinkCategories","left",array(array("lc.blogid","eq","l.blogid"),array("lc.id","eq","l.category")));
		$pool->setQualifier("l.blogid","eq",$blogid);
		$pool->setOrder("lc.name, l.name","desc");
		if ($result = $pool->getAll("l.*, lc.name AS categoryName")) {
			$__gCacheLink = array();
			foreach($result as $link) {
				array_push($__gCacheLink, $link);
			}
			$context->setProperty("cache.links",$__gCacheLink);
		}
	}
	return $__gCacheLink;
}

function getLinksWithPagingForOwner($blogid, $page, $count) {
	$pool = DBModel::getInstance();
	$pool->init("Links");
	$pool->setAlias("Links","l");
	$pool->setAlias("LinkCategories","lc");
	$pool->join("LinkCategories","left",array(array("lc.blogid","eq","l.blogid"),array("lc.id","eq","l.category")));
	$pool->setQualifier("l.blogid","eq",$blogid);
	$pool->setOrder("l.name","desc");
	$pool->setProjection("l.*","lc.name AS categoryName");

	return Paging::fetch($pool, $page, $count );
}

function getLink($blogid, $id) {
	$pool = DBModel::getInstance();
	$pool->init("Links");
	$pool->setAlias("Links","l");
	$pool->setAlias("LinkCategories","lc");
	$pool->join("LinkCategories","left",array(array("lc.blogid","eq","l.blogid"),array("lc.id","eq","l.category")));
	$pool->setQualifier("l.blogid","eq",$blogid);
	$pool->setQualifier("l.id","eq",$id);
	return $pool->getRow("l.*, lc.name AS categoryName");
}

function deleteLink($blogid, $id) {
	$pool = DBModel::getInstance();
	$pool->init("Links");
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("id","eq",$id);
	$result = $pool->delete();
	return ($result) ? true : false;
}

function toggleLinkVisibility($blogid, $id, $visibility) {
	$pool = DBModel::getInstance();
	$pool->init("Links");
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("id","eq",$id);
	$pool->setAttribute("visibility",$visibility);
	$result = $pool->update();
	return array( ($result) ? true : false, $visibility );
}

function addLink($blogid, $link) {
	global $database;
	$name = Utils_Unicode::lessenAsEncoding(trim($link['name']), 255);
	$url = Utils_Unicode::lessenAsEncoding(trim($link['url']), 255);

	if (empty($name) || empty($url))
		return - 1;
	$category = (isset($link['category'])) ? $link['category'] : 0;
	$name = POD::escapeString($name);
	$url = POD::escapeString($url);
	if(isset($link['newCategory']) && !empty($link['newCategory'])) { // Add new category information
		$newCategoryTitle = POD::escapeString(Utils_Unicode::lessenAsEncoding(trim($link['newCategory']), 255));
		$newCategoryId = addLinkCategory($blogid, $newCategoryTitle);
		if(!empty($newCategoryId)) $category = $newCategoryId;
		else return false;
	}
	
	$id = getMaxIdOfLink() + 1;
	$pid = getMaxPidOfLink() + 1;

	$rss = isset($link['rss']) ? POD::escapeString(Utils_Unicode::lessenAsEncoding(trim($link['rss']), 255)) : '';
	if (POD::queryCell("SELECT id FROM {$database['prefix']}Links WHERE blogid = $blogid AND url = '$url'"))
		return 1;
	if (POD::execute("INSERT INTO {$database['prefix']}Links (pid, blogid, id,category,name,url,rss,written) VALUES ($pid, $blogid, $id, $category, '$name', '$url', '$rss', UNIX_TIMESTAMP())"))
		return 0;
	else
		return - 1;
}

function updateLink($blogid, $link) {
	global $database;
	$id = $link['id'];
	$name = Utils_Unicode::lessenAsEncoding(trim($link['name']), 255);
	$url = Utils_Unicode::lessenAsEncoding(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return false;
	$category = (isset($link['category'])) ? $link['category'] : 0;
	$name = POD::escapeString($name);
	$url = POD::escapeString($url);

	if(isset($link['newCategory']) && !empty($link['newCategory'])) { // Add new category information
		$newCategoryTitle = Utils_Unicode::lessenAsEncoding(trim($link['newCategory']), 255);
		$newCategoryId = addLinkCategory($blogid, $newCategoryTitle);
		if(!empty($newCategoryId)) $category = $newCategoryId;
	}

	$rss = isset($link['rss']) ? POD::escapeString(Utils_Unicode::lessenAsEncoding(trim($link['rss']), 255)) : '';
	$result = POD::execute("UPDATE {$database['prefix']}Links
				SET
					category = $category,
					name = '$name',
					url = '$url',
					rss = '$rss',
					written = UNIX_TIMESTAMP()
				WHERE
					blogid = $blogid and id = {$link['id']}");
	// Garbage correction
	$existCategories = POD::queryColumn("SELECT DISTINCT category FROM {$database['prefix']}Links
			WHERE blogid = $blogid");
	@POD::execute("DELETE FROM {$database['prefix']}LinkCategories
			WHERE blogid = $blogid AND id NOT IN (".implode(",",$existCategories).")");
	return $result;
}

function updateXfn($blogid, $links) {
	global $database;
	$ids = Array();
	foreach( $links as $k => $v ) {
		if( substr($k,0,3) == 'xfn' ) {
			$id = substr( $k, 3 );
			$xfn = POD::escapeString($v);
			POD::execute("update {$database['prefix']}Links
				set
					xfn = '$xfn',
					written = UNIX_TIMESTAMP()
				where
					blogid = $blogid and id = $id");
		}
	}
}
function getLinkCategories($blogid) {
	global $database;
	return POD::queryAll("SELECT * FROM {$database['prefix']}LinkCategories
			WHERE blogid = $blogid");
}

function addLinkCategory($blogid, $categoryTitle) {
	global $database;
	$categoryTitle = POD::escapeString($categoryTitle);
	$id = POD::queryCell("SELECT id FROM {$database['prefix']}LinkCategories
		WHERE blogid = $blogid AND name = '".$categoryTitle."'");
	if(!empty($id)) {
		return $id;
	} else {	// Add new Link Category
		$pid = getMaxPidOfLinkCategory() + 1;
		$id = getMaxIdOfLinkCategory($blogid) + 1;
		$priority = 0;
		$visibility = 2; // Default visibility
		if(POD::query("INSERT INTO {$database['prefix']}LinkCategories
			(pid, blogid, id, name, priority, visibility) VALUES
			($pid, $blogid, $id, '$categoryTitle', $priority, $visibility)")) {
			return $id;
		} else {
			return false;
		}
	}
}

function updateLinkCategory($blogid, $category) {
	global $database;
	$categoryTitle = POD::escapeString($category['name']);
	$id = $category['id'];
	
	if(POD::query("UPDATE {$database['prefix']}LinkCategories
		SET
			name = '".$categoryTitle."'
			WHERE blogid = $blogid AND id = $id")) {
		return true;
	} else {
		return false;
	}
}

function deleteLinkCategory($blogid, $id) {
	global $database;
	if(POD::query("DELETE FROM {$database['prefix']}LinkCategories
		WHERE blogid = $blogid AND id = $id")) {
		POD::execute("UPDATE {$database['prefix']}Links
			SET category = 0
			WHERE blogid = $blogid AND category = $id");
		return true;
	} else {
		return false;
	}
}

function getLinkCategory($blogid, $id) {
	$pool = DBModel::getInstance();
	$pool->reset('LinkCategories');
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('id','eq',$id);	
	return $pool->getRow('*');
}

function getMaxIdOfLink($blogid = null) {
	if(empty($blogid)) $blogid = getBlogId();
	$pool = DBModel::getInstance();
	$pool->reset('Links');
	$pool->setQualifier('blogid','eq',$blogid);
	$id = $pool->getCell('max(id)');
	return (empty($id) ? 0 : $id);
}

function getMaxPidOfLink() {
	$pool = DBModel::getInstance();
	$pool->reset('Links');
	$id = $pool->getCell('max(pid)');
	return (empty($id) ? 0 : $id);
}

function getMaxIdOfLinkCategory($blogid = null) {
	if(empty($blogid)) $blogid = getBlogId();	
	$pool = DBModel::getInstance();
	$pool->reset('LinkCategories');
	$pool->setQualifier('blogid','eq',$blogid);
	$id = $pool->getCell('max(id)');
	return (empty($id) ? 0 : $id);
}
function getMaxPidOfLinkCategory() {
	$pool = DBModel::getInstance();
	$pool->reset('LinkCategories');
	$id = $pool->getCell('max(pid)');
	return (empty($id) ? 0 : $id);
}
?>
