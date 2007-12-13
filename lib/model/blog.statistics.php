<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getStatistics($blogid) {
	global $database;
	$stats = array('total' => 0, 'today' => 0, 'yesterday' => 0);
	$result = DBQuery::queryCell("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
	if (!empty($result)) $stats['total'] = $result;
	
	$result = DBQuery::queryColumn("SELECT visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND `date` in (" . Timestamp::getDate().",".Timestamp::getDate(time()-86400).") ORDER BY date DESC");
	$stats['today'] = (isset($result[0])) ? $result[0] : 0;
	$stats['yesterday'] = (isset($result[1])) ? $result[1] : 0;

	return $stats;
}

function getDailyStatistics($period) {
	global $database, $blogid;
	return DBQuery::queryAll("SELECT date, visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND LEFT(date, 6) = $period ORDER BY date DESC");
}

function getMonthlyStatistics($blogid) {
	global $database;
	$statistics = array();
	if ($result = DBQuery::query("select left(date, 6) date, sum(visits) visits from {$database['prefix']}DailyStatistics where blogid = $blogid group by left(date, 6) order by date desc")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererStatistics($blogid) {
	global $database;
	$statistics = array();
	if ($result = DBQuery::query("select host, count from {$database['prefix']}RefererStatistics where blogid = $blogid order by count desc limit 20")) {
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
	$result = DBQuery::query("select blog from {$database['prefix']}SessionVisits where id = '$id' and address = '{$_SERVER['REMOTE_ADDR']}' and blog = $blogid");
	if ($result && (mysql_num_rows($result) > 0))
		return;
	if (DBQuery::query("insert into {$database['prefix']}SessionVisits values('$id', '{$_SERVER['REMOTE_ADDR']}', $blogid)") && (mysql_affected_rows() > 0)) {
		DBQuery::query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where blogid = $blogid");
		if (mysql_affected_rows() == 0) {
			if (DBQuery::query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where blogid = $blogid") || (mysql_affected_rows() == 0))
				DBQuery::query("insert into {$database['prefix']}BlogStatistics values($blogid, 1)");
		}
		$period = Timestamp::getDate();
		DBQuery::query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where blogid = $blogid and `date` = $period");
		if (mysql_affected_rows() == 0) {
			if (!DBQuery::query("insert into {$database['prefix']}DailyStatistics values($blogid, $period, 1)") || (mysql_affected_rows() == 0))
				DBQuery::query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where blogid = $blogid and `date` = $period");
		}
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			if (!empty($referer['host']) && (($referer['host'] != $_SERVER['HTTP_HOST']) || (strncmp($referer['path'], $blogURL, strlen($blogURL)) != 0))) {
				requireComponent('Textcube.Data.Filter');
				if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $_SERVER['HTTP_REFERER']))
					return;
				if (!fireEvent('AddingRefererLog', true, array('host' => $referer['host'], 'url' => $_SERVER['HTTP_REFERER'])))
					return;
				$host = mysql_tt_escape_string(mysql_lessen($referer['host'], 64));
				$url = mysql_tt_escape_string(mysql_lessen($_SERVER['HTTP_REFERER'], 255));
				DBQuery::query("insert into {$database['prefix']}RefererLogs values($blogid, '$host', '$url', UNIX_TIMESTAMP())");
				DBQuery::query("delete from {$database['prefix']}RefererLogs where referred < UNIX_TIMESTAMP() - 604800");
				if (!DBQuery::query("update {$database['prefix']}RefererStatistics set count = count + 1 where blogid = $blogid and host = '$host'") || (mysql_affected_rows() == 0))
					DBQuery::query("insert into {$database['prefix']}RefererStatistics values($blogid, '$host', 1)");
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
	DBQuery::query("update {$database['prefix']}BlogStatistics set visits = 0 where blogid = $blogid");
	if (mysql_affected_rows() == 0)
		DBQuery::query("insert into {$database['prefix']}BlogStatistics values($blogid, 0)");
	return mysql_affected_rows() ? true : false;
}
?>
