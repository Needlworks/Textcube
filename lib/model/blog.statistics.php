<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getStatistics($blogid) {
	global $database;
	$stats = array('total' => 0, 'today' => 0, 'yesterday' => 0);
	$result = DBQuery::query("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
	if (!empty($result) && mysql_num_rows($result) == 1)
		list($stats['total']) = mysql_fetch_array($result);
	$result = DBQuery::query("SELECT visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND `date` = " . Timestamp::getDate());
	if (!empty($result) && mysql_num_rows($result) == 1)
		list($stats['today']) = mysql_fetch_array($result);
	$result = DBQuery::query("SELECT visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND `date` = " . Timestamp::getDate(time() - 86400));
	if (!empty($result) && mysql_num_rows($result) == 1)
		list($stats['yesterday']) = mysql_fetch_array($result);
	return $stats;
}

function getDailyStatistics($period) {
	global $database, $blogid;
	return DBQuery::queryAll("SELECT date, visits 
		FROM {$database['prefix']}DailyStatistics 
		WHERE blogid = $blogid 
			AND LEFT(date, 6) = $period 
		ORDER BY date DESC");
}

function getMonthlyStatistics($blogid) {
	global $database;
	$statistics = array();
	if ($result = DBQuery::query("SELECT left(date, 6) date, sum(visits) visits 
		FROM {$database['prefix']}DailyStatistics 
		WHERE blogid = $blogid 
		GROUP BY left(date, 6) 
		ORDER BY date DESC")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererStatistics($blogid) {
	global $database;
	$statistics = array();
	if ($result = DBQuery::query("SELECT host, count FROM {$database['prefix']}RefererStatistics WHERE blogid = $blogid order by count desc limit 20")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererLogsWithPage($page, $count) {  
	global $database;
	return fetchWithPaging("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE blogid = ".getBlogId()." ORDER BY referred DESC", $page, $count);  
}  

function getRefererLogs() {
	global $database;
	return DBQuery::queryAll("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE blogid = ".getBlogId()." ORDER BY referred DESC LIMIT 1500");
}

function updateVisitorStatistics($blogid) {
	global $database, $blogURL;
	if (!fireEvent('UpdatingVisitorStatistics', true))
		return;
	if (doesHaveOwnership())
		return;
	$id = session_id();
	$result = DBQuery::query("SELECT blog FROM {$database['prefix']}SessionVisits WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}' AND blog = $blogid");
	if ($result && (mysql_num_rows($result) > 0))
		return;
	if (DBQuery::query("INSERT into {$database['prefix']}SessionVisits values('$id', '{$_SERVER['REMOTE_ADDR']}', $blogid)") && (mysql_affected_rows() > 0)) {
		DBQuery::query("UPDATE {$database['prefix']}BlogStatistics SET visits = visits + 1 WHERE blogid = $blogid");
		if (mysql_affected_rows() == 0) {
			if (DBQuery::query("UPDATE {$database['prefix']}BlogStatistics SET visits = visits + 1 WHERE blogid = $blogid") || (mysql_affected_rows() == 0))
				DBQuery::query("INSERT into {$database['prefix']}BlogStatistics values($blogid, 1)");
		}
		$period = Timestamp::getDate();
		DBQuery::query("UPDATE {$database['prefix']}DailyStatistics SET visits = visits + 1 WHERE blogid = $blogid AND `date` = $period");
		if (mysql_affected_rows() == 0) {
			if (!DBQuery::query("INSERT into {$database['prefix']}DailyStatistics values($blogid, $period, 1)") || (mysql_affected_rows() == 0))
				DBQuery::query("UPDATE {$database['prefix']}DailyStatistics SET visits = visits + 1 WHERE blogid = $blogid AND `date` = $period");
		}
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			if (!empty($referer['host']) && (($referer['host'] != $_SERVER['HTTP_HOST']) || (strncmp($referer['path'], $blogURL, strlen($blogURL)) != 0))) {
				requireComponent('Textcube.Data.Filter');
				if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $_SERVER['HTTP_REFERER']))
					return;
				if (!fireEvent('AddingRefererLog', true, array('host' => $referer['host'], 'url' => $_SERVER['HTTP_REFERER'])))
					return;
				$host = DBQuery::escapeString(UTF8::lessenAsEncoding($referer['host'], 64));
				$url = DBQuery::escapeString(UTF8::lessenAsEncoding($_SERVER['HTTP_REFERER'], 255));
				DBQuery::query("INSERT INTO {$database['prefix']}RefererLogs values($blogid, '$host', '$url', UNIX_TIMESTAMP())");
				DBQuery::query("DELETE FROM {$database['prefix']}RefererLogs WHERE referred < UNIX_TIMESTAMP() - 604800");
				if (!DBQuery::query("UPDATE {$database['prefix']}RefererStatistics SET count = count + 1 WHERE blogid = $blogid AND host = '$host'") || (mysql_affected_rows() == 0))
					DBQuery::query("INSERT into {$database['prefix']}RefererStatistics values($blogid, '$host', 1)");
			}
		}
	}
}

function setTotalStatistics($blogid) {
	global $database;
	DBQuery::execute("DELETE FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid");
	$prevCount = DBQuery::queryCell("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
	if ((!is_null($prevCount)) && ($prevCount == 0))
		return true;
	DBQuery::query("UPDATE {$database['prefix']}BlogStatistics SET visits = 0 WHERE blogid = $blogid");
	if (mysql_affected_rows() == 0)
		DBQuery::query("INSERT INTO {$database['prefix']}BlogStatistics values($blogid, 0)");
	return mysql_affected_rows() ? true : false;
}
?>
