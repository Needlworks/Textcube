<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require 'common.correctTT.php';

function getTrackbacksWithPagingForOwner($owner, $category, $site, $ip, $search, $page, $count) {
	global $database;
	
	$postfix = '';
	$sql = "SELECT t.*, c.name categoryName FROM {$database['prefix']}Trackbacks t LEFT JOIN {$database['prefix']}Entries e ON t.owner = e.owner AND t.entry = e.id AND e.draft = 0 LEFT JOIN {$database['prefix']}Categories c ON t.owner = c.owner AND e.category = c.id WHERE t.owner = $owner AND t.isFiltered = 0";
	if ($category > 0) {
		$categories = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($site)) {
		$sql .= ' AND t.site = \'' . mysql_tt_escape_string($site) . '\'';
		$postfix .= '&site=' . rawurlencode($site);
	}
	if (!empty($ip)) {
		$sql .= ' AND t.ip = \'' . mysql_tt_escape_string($ip) . '\'';
		$postfix .= '&ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = escapeMysqlSearchString($search);
		$sql .= " AND (t.site LIKE '%$search%' OR t.subject LIKE '%$search%' OR t.excerpt LIKE '%$search%')";
		$postfix .= '&search=' . rawurlencode($search);
	}
	$sql .= ' ORDER BY t.written DESC';
	list($trackbacks, $paging) = fetchWithPaging($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$paging['postfix'] .= $postfix . '&withSearch=on';
	}
	return array($trackbacks, $paging);
}

function getTrackbacks($entry) {
	global $database, $owner;
	$trackbacks = array();
	$result = DBQuery::query("select * from {$database['prefix']}Trackbacks where owner = $owner AND entry = $entry AND isFiltered = 0 order by written");
	while ($trackback = mysql_fetch_array($result))
		array_push($trackbacks, $trackback);
	return $trackbacks;
}

function getRecentTrackbacks($owner, $count = false) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * 
		FROM 
			{$database['prefix']}Trackbacks 
		WHERE 
			owner = $owner AND isFiltered = 0 
		ORDER BY 
			written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']) : 
		"SELECT t.* 
		FROM 
			{$database['prefix']}Trackbacks t 
			LEFT JOIN {$database['prefix']}Entries e ON t.owner = e.owner AND t.entry = e.id
			LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id
		WHERE 
			t.owner = $owner AND e.draft = 0 AND e.visibility >= 2 AND (c.visibility > 1 OR e.category = 0) AND t.isFiltered = 0 
		ORDER BY 
			t.written 
		DESC LIMIT ".($count = false ? $count : $skinSetting['trackbacksOnRecent']);
	if ($result = DBQuery::query($sql)) {
		while ($trackback = mysql_fetch_array($result))
			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

function sendTrackbackPing($entryId, $permalink, $url, $site, $title) {
	requireComponent('Eolin.PHP.Core');
	requireComponent('Eolin.PHP.XMLRPC');
	$rpc = new XMLRPC();
	$rpc->url = TEXTCUBE_SYNC_URL;
	$summary = array(
		'permalink' => $permalink,
		'url' => $url,
		'blogName' => $site,
		'title' => $title
	);
	$rpc->async = true;
	$rpc->call('sync.trackback', $summary);
}

function receiveTrackback($owner, $entry, $title, $url, $excerpt, $site) {
	global $database, $blog, $defaultURL;
	if (empty($url))
		return 5;
	requireComponent('Textcube.Data.Post');
	if (!Post::doesAcceptTrackback($entry))
		return 3;
		
	$filtered = 0;
	
	requireComponent('Textcube.Data.Filter');
	if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $url))
		$filtered = 1;
	else if (Filter::isFiltered('content', $excerpt))
		$filtered = 1;
	else if (!fireEvent('AddingTrackback', true, array('entry' => $entry, 'url' => $url, 'site' => $site, 'title' => $title, 'excerpt' => $excerpt)))
		$filtered = 1;

	$title = correctTTForXmlText($title);
	$excerpt = correctTTForXmlText($excerpt);

	$url = mysql_lessen($url);
	$site = mysql_lessen($site);
	$title = mysql_lessen($title);
	$excerpt = mysql_lessen($excerpt);

	requireComponent('Textcube.Data.Trackback');
	$trackback = new Trackback();
	$trackback->entry = $entry;
	$trackback->url = $url;
	$trackback->site = $site;
	$trackback->title = $title;
	$trackback->excerpt = $excerpt;
	if ($filtered > 0) {
		$trackback->isFiltered = true;
	}
	if ($trackback->add())
		return ($filtered == 0) ? 0 : 3;
	else
		return 4;
	return 0;
}

function deleteTrackback($owner, $id) {
	global $database;
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($owner, $entry))
		return $entry;
	return false;
}

function trashTrackback($owner, $id) {
	global $database;
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("UPDATE {$database['prefix']}Trackbacks SET isFiltered = UNIX_TIMESTAMP() WHERE owner = $owner AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($owner, $entry))
		return $entry;
	return false;
}

function revertTrackback($owner, $id) {
	global $database;
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("UPDATE {$database['prefix']}Trackbacks SET isFiltered = 0 WHERE owner = $owner AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($owner, $entry))
		return $entry;
	return false;
}

function sendTrackback($owner, $entryId, $url) {
	global $database, $defaultURL, $blog;
	requireComponent('Eolin.PHP.HTTPRequest');
	$entry = getEntry($owner, $entryId);
	if (!$entry)
		return false;
	$link = "$defaultURL/$entryId";
	$title = htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id']));
	$entry['content'] = getEntryContentView($owner, $entryId, $entry['content'], $entry['contentFormatter'], getKeywordNames($owner));
	$excerpt = UTF8::lessen(removeAllTags(stripHTML($entry['content'])), 255);
	$blogTitle = $blog['title'];
	$isNeedConvert = 
		strpos($url, '/rserver.php?') !== false // 구버전 태터
		|| strpos($url, 'blog.naver.com/tb') !== false // 네이버 블로그
		|| strpos($url, 'news.naver.com/tb/') !== false // 네이버 뉴스
		|| strpos($url, 'blog.empas.com') !== false // 엠파스 블로그
		|| strpos($url, 'blog.yahoo.com') !== false // 야후 블로그
		|| strpos($url, 'www.blogin.com/tb/') !== false // 블로긴
		|| strpos($url, 'cytb.cyworld.nate.com') !== false // 싸이 페이퍼
		;
	if ($isNeedConvert) {
		$title = UTF8::convert($title);
		$excerpt = UTF8::convert($excerpt);
		$blogTitle = UTF8::convert($blogTitle);
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=euc-kr';
		$isSuccess = $request->send($content);
	} else {
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		$isSuccess = $request->send($content);
	}
	if ($isSuccess && (checkResponseXML($request->responseText) === 0)) {
		$url = mysql_tt_escape_string(mysql_lessen($url, 255));
		DBQuery::query("insert into {$database['prefix']}TrackbackLogs values ($owner, '', $entryId, '$url', UNIX_TIMESTAMP())");
		return true;
	}
	return false;
}

function getTrackbackLog($owner, $entry) {
	global $database;
	$result = DBQuery::query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entry");
	$str = '';
	while ($row = mysql_fetch_array($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getTrackbackLogs($owner, $entryId) {
	global $database;
	$logs = array();
	$result = DBQuery::query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entryId");
	while ($log = mysql_fetch_array($result))
		array_push($logs, $log);
	return $logs;
}

function deleteTrackbackLog($owner, $id) {
	global $database;
	$result = DBQuery::query("delete from {$database['prefix']}TrackbackLogs where owner = $owner and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
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
	$value = mysql_tt_escape_string($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}

function getTrackbackCount($owner, $entryId = null) {
	global $database;
	if (is_null($entryId))
		return DBQuery::queryCell("SELECT SUM(trackbacks) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft= 0");
	return DBQuery::queryCell("SELECT trackbacks FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $entryId AND draft= 0");
}


function getTrackbackCountPart($trackbackCount, &$skin) {
	$noneTrackbackMessage = $skin->noneTrackbackMessage;
	$singleTrackbackMessage = $skin->singleTrackbackMessage;
	
	if ($trackbackCount == 0 && !empty($noneTrackbackMessage)) {
		dress('article_rep_tb_cnt', 0, $noneTrackbackMessage);
		$trackbackView = $noneTrackbackMessage;
	} else if ($trackbackCount == 1 && !empty($singleTrackbackMessage)) {
		dress('article_rep_tb_cnt', 1, $singleTrackbackMessage);
		$trackbackView = $singleTrackbackMessage;
	} else {
		$trackbackPart = $skin->trackbackCount;
		dress('article_rep_tb_cnt', $trackbackCount, $trackbackPart);
		$trackbackView = $trackbackPart;
	}
	
	return array("tb_count", $trackbackView);
}
?>
