<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getRemoteResponsesWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count, $type = null) {
	global $database;
	if (is_null($type)) $typeFilter = " AND t.type = '".POD::escapeString($type)."'";
	else $typeFilter = '';
	$postfix = '';
	$sql = "SELECT t.*, c.name categoryName 
		FROM {$database['prefix']}RemoteResponses t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid AND t.isFiltered = 0 $typeFilter";
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
	list($responses, $paging) = fetchWithPaging($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$paging['postfix'] .= $postfix . '&amp;withSearch=on';
	}
	return array($responses, $paging);
}

function getRemoteResponseLogsWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count, $type = null) {
	global $database;
	if (is_null($type)) $typeFilter = " AND t.type = '".POD::escapeString($type)."'";
	else $typeFilter = '';
	$postfix = '&amp;status=sent';
	$sql = "SELECT t.*, e.title as subject, c.name categoryName 
		FROM {$database['prefix']}RemoteResponseLogs t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid $typeFilter";
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&amp;category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}
	$sql .= ' ORDER BY t.written DESC';
	list($responses, $paging) = fetchWithPaging($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$paging['postfix'] .= $postfix . '&amp;withSearch=on';
	}
	return array($responses, $paging);
}

function getRemoteResponses($entry, $type = null) {
	global $database;
	if (is_null($type)) $typeFilter = " AND type = '".POD::escapeString($type)."'";
	else $typeFilter = '';
	$responses = array();
	$result = POD::query("SELECT * 
			FROM {$database['prefix']}RemoteResponses 
			WHERE blogid = ".getBlogId()." 
				AND entry = $entry 
				AND isFiltered = 0 $typeFilter 
			ORDER BY written");
	while ($response = POD::fetch($result))
		array_push($responses, $response);
	return $responses;
}

function getRemoteResponseList($blogid, $search, $type = null) {
	global $database;
	if (is_null($type)) $typeFilter = " AND type = '".POD::escapeString($type)."'";
	else $typeFilter = '';
	$list = array('title' => "$search", 'items' => array());
	$search = escapeSearchString($search);
	$authorized = doesHaveOwnership() ? '' : getPrivateCategoryExclusionQuery($blogid);
	if ($result = POD::queryAll("SELECT t.id, t.entry, t.url, t.site, t.subject, t.excerpt, t.written, e.slogan
 		FROM {$database['prefix']}RemoteResponses t
		LEFT JOIN {$database['prefix']}Entries e ON t.entry = e.id AND t.blogid = e.blogid AND e.draft = 0
		WHERE  t.blogid = $blogid
			AND t.isFiltered = 0
			AND t.entry > 0 $authorized $typeFilter 
			AND (t.excerpt like '%$search%' OR t.subject like '%$search%')")) {
		foreach($result as $response)	
			array_push($list['items'], $response);
	}   
	return $list;
}

function getRecentRemoteResponses($blogid, $count = false, $guestShip = false, $type = null) {
	global $database, $skinSetting;
	if (is_null($type)) $typeFilter = " AND t.type = '".POD::escapeString($type)."'";
	else $typeFilter = '';
	$sql = (doesHaveOwnership() && !$guestShip) ? "SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}RemoteResponses t
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0
		WHERE 
			t.blogid = $blogid AND t.isFiltered = 0 $typeFilter 
		ORDER BY 
			t.written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']) : 
		"SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}RemoteResponses t 
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id
		WHERE 
			t.blogid = $blogid 
			AND t.isFiltered = 0 
			AND e.draft = 0 
			AND e.visibility >= 2 ".getPrivateCategoryExclusionQuery($blogid)."
			$typeFilter
		ORDER BY 
			t.written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']);
	if ($result = POD::queryAllWithDBCache($sql,'remoteResponse')) {
		return $result;
	}
	else return array();
}

function deleteRemoteResponse($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	CacheControl::flushDBCache('remoteResponse');
	if (updateRemoteResponsesOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function trashRemoteResponse($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::query("UPDATE {$database['prefix']}RemoteResponses SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	CacheControl::flushDBCache('remoteResponse');
	if (updateRemoteResponsesOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function revertRemoteResponse($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}RemoteResponses WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("UPDATE {$database['prefix']}RemoteResponses SET isFiltered = 0 WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	CacheControl::flushDBCache('remoteResponse');
	if (updateRemoteResponsesOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function getRemoteResponseLog($blogid, $entry, $type = null) {
	global $database;
	if($type === null) $filter = '';
	else $filter = " AND type = '".POD::escapeString($type)."'";
	$result = POD::query("SELECT * FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid AND entry = $entry $filter");
	$str = '';
	while ($row = POD::fetch($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getRemoteResponseLogs($blogid, $entryId, $type = null) {
	global $database;
	if($type === null) $filter = '';
	else $filter = " AND type = '".POD::escapeString($type)."'";
	$logs = array();
	$result = POD::query("SELECT * FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid AND entry = $entryId $filter");
	while ($log = POD::fetch($result))
		array_push($logs, $log);
	return $logs;
}

function deleteRemoteResponseLog($blogid, $id) {
	global $database;
	$result = POD::queryCount("DELETE FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = $blogid AND id = $id");
	return ($result == 1) ? true : false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));
	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		return $index;
	} else
		return - 1;
}

function getURLForFilter($value) {
	$value = POD::escapeString($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}

function getRemoteResponseCount($blogid, $entryId = null) {
	global $database;
	if (is_null($entryId)) {
		$result = POD::queryRow("SELECT SUM(trackbacks) as t, SUM(pingbacks) as p
				FROM {$database['prefix']}Entries 
				WHERE blogid = $blogid 
					AND draft= 0");
		return $result['t'] + $result ['p'];
	} else {
		$result = POD::queryRow("SELECT trackbacks, pingbacks 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND id = $entryId 
				AND draft= 0");
		return $result['trackbacks'] + $result['pingbacks'];
	}
}
?>
