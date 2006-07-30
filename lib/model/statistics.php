<?php

function getStatistics($owner) {
	global $database;
	$stats = array('total' => 0, 'today' => 0, 'yesterday' => 0);
	$result = mysql_query("select visits from {$database['prefix']}BlogStatistics where owner = $owner");
	if (mysql_num_rows($result) == 1)
		list($stats['total']) = mysql_fetch_array($result);
	$result = mysql_query("select visits from {$database['prefix']}DailyStatistics where owner = $owner and `date` = " . Timestamp::getDate());
	if (mysql_num_rows($result) == 1)
		list($stats['today']) = mysql_fetch_array($result);
	$result = mysql_query("select visits from {$database['prefix']}DailyStatistics where owner = $owner and `date` = " . Timestamp::getDate(time() - 86400));
	if (mysql_num_rows($result) == 1)
		list($stats['yesterday']) = mysql_fetch_array($result);
	return $stats;
}

function getDailyStatistics($period) {
	global $database, $owner;
	return DBQuery::queryAll("SELECT date, visits FROM {$database['prefix']}DailyStatistics WHERE owner = $owner AND LEFT(date, 6) = $period ORDER BY date DESC");
}

function getMonthlyStatistics($owner) {
	global $database;
	$statistics = array();
	if ($result = mysql_query("select left(date, 6) date, sum(visits) visits from {$database['prefix']}DailyStatistics where owner = $owner group by left(date, 6) order by date desc")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererStatistics($owner) {
	global $database;
	$statistics = array();
	if ($result = mysql_query("select host, count from {$database['prefix']}RefererStatistics where owner = $owner order by count desc limit 20")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererLogsWithPage($page, $count) {  
	global $database, $owner;  
	return fetchWithPaging("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE owner = $owner ORDER BY referred DESC", $page, $count);  
}  

function getRefererLogs() {
	global $database, $owner;
	return DBQuery::queryAll("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE owner = $owner ORDER BY referred DESC LIMIT 1500");
}

function updateVisitorStatistics($owner) {
	global $database, $blogURL;
	if (!fireEvent('UpdatingVisitorStatistics', true))
		return;
	if (doesHaveOwnership())
		return;
	$id = session_id();
	$result = mysql_query("select blog from {$database['prefix']}SessionVisits where id = '$id' and address = '{$_SERVER['REMOTE_ADDR']}' and blog = $owner");
	if ($result && (mysql_num_rows($result) > 0))
		return;
	if (mysql_query("insert into {$database['prefix']}SessionVisits values('$id', '{$_SERVER['REMOTE_ADDR']}', $owner)") && (mysql_affected_rows() > 0)) {
		mysql_query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where owner = $owner");
		if (mysql_affected_rows() == 0) {
			if (mysql_query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where owner = $owner") || (mysql_affected_rows() == 0))
				mysql_query("insert into {$database['prefix']}BlogStatistics values($owner, 1)");
		}
		$period = Timestamp::getDate();
		mysql_query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where owner = $owner and `date` = $period");
		if (mysql_affected_rows() == 0) {
			if (!mysql_query("insert into {$database['prefix']}DailyStatistics values($owner, $period, 1)") || (mysql_affected_rows() == 0))
				mysql_query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where owner = $owner and `date` = $period");
		}
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			if (!empty($referer['host']) && (($referer['host'] != $_SERVER['HTTP_HOST']) || (strncmp($referer['path'], $blogURL, strlen($blogURL)) != 0))) {
				requireComponent('Tattertools.Data.Filter');
				if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $_SERVER['HTTP_REFERER']))
					return;
				if (!fireEvent('AddingRefererLog', true, array('host' => $referer['host'], 'url' => $_SERVER['HTTP_REFERER'])))
					return;
				$host = mysql_escape_string($referer['host']);
				$url = mysql_escape_string($_SERVER['HTTP_REFERER']);
				mysql_query("insert into {$database['prefix']}RefererLogs values($owner, '$host', '$url', UNIX_TIMESTAMP())");
				mysql_query("delete from {$database['prefix']}RefererLogs where referred < UNIX_TIMESTAMP() - 604800");
				if (!mysql_query("update {$database['prefix']}RefererStatistics set count = count + 1 where owner = $owner and host = '$host'") || (mysql_affected_rows() == 0))
					mysql_query("insert into {$database['prefix']}RefererStatistics values($owner, '$host', 1)");
			}
		}
	}
}

function setTotalStatistics($owner) {
	global $database;
	DBQuery::query("DELETE FROM {$database['prefix']}DailyStatistics WHERE owner = $owner");
	$prevCount = DBQuery::queryCell("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE owner = $owner");
	if ((!is_null($prevCount)) && ($prevCount == 0))
		return true;
	mysql_query("update {$database['prefix']}BlogStatistics set visits = 0 where owner = $owner");
	if (mysql_affected_rows() == 0)
		mysql_query("insert into {$database['prefix']}BlogStatistics values($owner, 0)");
	return mysql_affected_rows() ? true : false;
}
?>
