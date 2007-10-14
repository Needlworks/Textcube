<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require 'common.correctTT.php';

function getTrackbacksWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count) {
	global $database;
	
	$postfix = '';
	$sql = "SELECT t.*, c.name categoryName 
		FROM {$database['prefix']}Trackbacks t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid AND t.isFiltered = 0";
	if ($category > 0) {
		$categories = DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($site)) {
		$sql .= ' AND t.site = \'' . tc_escape_string($site) . '\'';
		$postfix .= '&site=' . rawurlencode($site);
	}
	if (!empty($ip)) {
		$sql .= ' AND t.ip = \'' . tc_escape_string($ip) . '\'';
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
	global $database;
	$trackbacks = array();
	$result = DBQuery::query("select * 
			from {$database['prefix']}Trackbacks 
			where blogid = ".getBlogId()." 
				AND entry = $entry 
				AND isFiltered = 0 
			order by written");
	while ($trackback = mysql_fetch_array($result))
		array_push($trackbacks, $trackback);
	return $trackbacks;
}

function getTrackbackList($blogid, $search) {
	global $database;
	$list = array('title' => "$search", 'items' => array());
	$search = escapeMysqlSearchString($search);
	$authorized = doesHaveOwnership() ? '' : ' AND (ct.visibility > 1 OR e.category = 0)';
	if ($result = DBQuery::query("SELECT t.id, t.entry, t.url, t.site, t.subject, t.excerpt, t.written, e.slogan
 		FROM {$database['prefix']}Trackbacks t
			LEFT JOIN {$database['prefix']}Entries e ON t.entry = e.id AND t.blogid = e.blogid
			LEFT JOIN {$database['prefix']}Categories ct ON ct.id = e.category AND ct.blogid = t.blogid
			WHERE t.entry > 0 
				AND t.blogid = $blogid $authorized 
				AND t.isFiltered = 0 
				AND (t.excerpt like '%$search%' OR t.subject like '%$search%')")) {
			while ($comment = mysql_fetch_array($result))
				array_push($list['items'], $comment);
	}   
	return $list;
}

function getRecentTrackbacks($blogid, $count = false) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}Trackbacks t
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id
		WHERE 
			t.blogid = $blogid AND t.isFiltered = 0 
		ORDER BY 
			t.written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']) : 
		"SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}Trackbacks t 
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id
			LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE 
			t.blogid = $blogid AND e.draft = 0 AND e.visibility >= 2 AND (c.visibility > 1 OR e.category = 0) AND t.isFiltered = 0 
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

function receiveTrackback($blogid, $entry, $title, $url, $excerpt, $site) {
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

	$url = UTF8::lessenAsEncoding($url);
	$site = UTF8::lessenAsEncoding($site);
	$title = UTF8::lessenAsEncoding($title);
	$excerpt = UTF8::lessenAsEncoding($excerpt);

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

function deleteTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("DELETE FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function trashTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("UPDATE {$database['prefix']}Trackbacks SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function revertTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = DBQuery::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!DBQuery::execute("UPDATE {$database['prefix']}Trackbacks SET isFiltered = 0 WHERE blogid = $blogid AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function sendTrackback($blogid, $entryId, $url) {
	global $database, $defaultURL, $blog;
	requireComponent('Eolin.PHP.HTTPRequest');
	requireModel('blog.entry');
	requireModel('blog.keyword');
	
	$entry = getEntry($blogid, $entryId);
	if (!$entry)
		return false;
	$link = "$defaultURL/$entryId";
	$title = htmlspecialchars(fireEvent('ViewPostTitle', $entry['title'], $entry['id']));
	$entry['content'] = getEntryContentView($blogid, $entryId, $entry['content'], $entry['contentFormatter'], getKeywordNames($blogid));
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
		|| strpos($url, 'www.cine21.com/Movies/tb.php') !== false // cine21
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
		$url = tc_escape_string(UTF8::lessenAsEncoding($url, 255));
		DBQuery::query("insert into {$database['prefix']}TrackbackLogs values ($blogid, '', $entryId, '$url', UNIX_TIMESTAMP())");
		return true;
	}
	return false;
}

function getTrackbackLog($blogid, $entry) {
	global $database;
	$result = DBQuery::query("select * from {$database['prefix']}TrackbackLogs where blogid = $blogid and entry = $entry");
	$str = '';
	while ($row = mysql_fetch_array($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getTrackbackLogs($blogid, $entryId) {
	global $database;
	$logs = array();
	$result = DBQuery::query("select * from {$database['prefix']}TrackbackLogs where blogid = $blogid and entry = $entryId");
	while ($log = mysql_fetch_array($result))
		array_push($logs, $log);
	return $logs;
}

function deleteTrackbackLog($blogid, $id) {
	global $database;
	$result = DBQuery::query("delete from {$database['prefix']}TrackbackLogs where blogid = $blogid and id = $id");
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
	$value = tc_escape_string($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}

function getTrackbackCount($blogid, $entryId = null) {
	global $database;
	if (is_null($entryId))
		return DBQuery::queryCell("SELECT SUM(trackbacks) 
				FROM {$database['prefix']}Entries 
				WHERE blogid = $blogid 
					AND draft= 0");
	return DBQuery::queryCell("SELECT trackbacks 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND id = $entryId 
				AND draft= 0");
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
