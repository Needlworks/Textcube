<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

//require 'common.correctTT.php';

function getTrashTrackbackWithPagingForOwner($blogid, $category, $site, $url, $ip, $search, $page, $count) {
	global $database;
	
	$postfix = '';
	$sql = "SELECT t.*, c.name AS categoryName 
		FROM {$database['prefix']}RemoteResponses t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid AND t.isfiltered > 0 AND t.responsetype = 'trackback'";
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&amp;category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($site)) {
		$sql .= ' AND t.site = \'' . POD::escapeString($site) . '\'';
		$postfix .= '&amp;site=' . rawurlencode($site);
	}
	if (!empty($url)) {
		$sql .= ' AND t.url = \'' . POD::escapeString($url) . '\'';
		$postfix .= '&amp;url=' . rawurlencode($url);
	}
	if (!empty($ip)) {
		$sql .= ' AND t.ip = \'' . POD::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (t.site LIKE '%$search%' OR t.subject LIKE '%$search%' OR t.excerpt LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}
	$sql .= ' ORDER BY t.written DESC';
	list($trackbacks, $paging) =  Paging::fetch($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$paging['postfix'] .= $postfix . '&amp;withSearch=on';
	}
	return array($trackbacks, $paging);
}


function getTrashCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count) {
	global $database;
	$sql = "SELECT c.*, e.title, c2.name AS parentName 
		FROM {$database['prefix']}Comments c 
		LEFT JOIN {$database['prefix']}Entries e ON c.blogid = e.blogid AND c.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Comments c2 ON c.parent = c2.id AND c.blogid = c2.blogid 
		WHERE c.blogid = $blogid AND c.isfiltered > 0";

	$postfix = '';	
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&amp;category=' . rawurlencode($category);
	} else
		$sql .= ' AND (e.category >= 0 OR c.entry = 0)';
	if (!empty($name)) {
		$sql .= ' AND c.name = \'' . POD::escapeString($name) . '\'';
		$postfix .= '&amp;name=' . rawurlencode($name);
	}
	if (!empty($ip)) {
		$sql .= ' AND c.ip = \'' . POD::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (c.name LIKE '%$search%' OR c.homepage LIKE '%$search%' OR c.comment LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}
	$sql .= ' ORDER BY c.written DESC';
	list($comments, $paging) =  Paging::fetch($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$paging['postfix'] .= $postfix . '&amp;withSearch=on';
	}
	return array($comments, $paging);
}

function getTrackbackTrash($entry) {
	global $database;
	$trackbacks = array();
	$result = POD::queryAll("SELECT * 
			FROM {$database['prefix']}RemoteResponses 
			WHERE blogid = ".getBlogId()."
				AND entry = $entry 
			ORDER BY written",'assoc');
	if(!empty($result)) return $result;
	else return array();
}

function getRecentTrackbackTrash($blogid) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}RemoteResponses
		WHERE blogid = $blogid 
		ORDER BY written 
		DESC LIMIT {$skinSetting['trackbacksOnRecent']}" : 
		"SELECT t.* FROM {$database['prefix']}RemoteResponses t, 
		{$database['prefix']}Entries e 
		WHERE t.blogid = $blogid AND t.blogid = e.blogid AND t.entry = e.id AND t.responsetype = 'trackback' AND e.draft = 0 AND e.visibility >= 2 
		ORDER BY t.written DESC LIMIT {$skinSetting['trackbacksOnRecent']}";
	if ($result = POD::query($sql)) {
		while ($trackback = POD::fetch($result))
			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

function deleteTrackbackTrash($blogid, $id) {
	global $database;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function restoreTrackbackTrash($blogid, $id) {
   	global $database;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("UPDATE {$database['prefix']}RemoteResponses SET isfiltered = 0 WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function trashVan() {
   	global $database;
	requireModel('common.setting');
	if(Timestamp::getUNIXtime() - Setting::getServiceSetting('lastTrashSweep',0, true) > 86400) {
//		var_dump(Timestamp::getUNIXtime());
//		var_dump(Setting::getServiceSetting('lastTrashSweep',0, true));
		POD::execute("DELETE FROM {$database['prefix']}Comments where isfiltered < ".Timestamp::getUNIXtime()." - 1296000 AND isfiltered > 0");
		POD::execute("DELETE FROM {$database['prefix']}RemoteResponses where isfiltered < ".Timestamp::getUNIXtime()." - 1296000 AND isfiltered > 0");
		POD::execute("DELETE FROM {$database['prefix']}RefererLogs WHERE referred < ".Timestamp::getUNIXtime()." - 604800");
		Setting::setServiceSetting('lastTrashSweep',Timestamp::getUNIXtime(),true);
	}
	if(Timestamp::getUNIXtime() - Setting::getServiceSetting('lastNoticeRead',0, true) > 43200) {
		Setting::removeServiceSetting('TextcubeNotice',true);
		Setting::setServiceSetting('lastNoticeRead',Timestamp::getUNIXtime(),true);
	}
}

function emptyTrash($comment = true)
{
   	global $database;
	requireModel('common.setting');
	$blogid = getBlogId();
	if ($comment == true) {
		POD::execute("DELETE FROM {$database['prefix']}Comments where blogid = ".$blogid." and isfiltered > 0");
	} else {
		POD::execute("DELETE FROM {$database['prefix']}RemoteResponses where blogid = ".$blogid." and isfiltered > 0");
	}
}

?>
