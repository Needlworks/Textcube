<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getStatistics($blogid) {
	global $database;
	$stats = array('total' => 0, 'today' => 0, 'yesterday' => 0);
	$result = DBQuery::queryCell("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
	if (!empty($result)) $stats['total'] = $result;
	
	$result = DBQuery::queryAll("SELECT date, visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND `date` in ('" . Timestamp::getDate()."','".Timestamp::getDate(time()-86400)."')");
	$stat['today'] = $stat['yesterday'] = 0;
	foreach($result as $data) {
		if($data['date'] == Timestamp::getDate()) $stats['today'] = $data['visits'];
		if($data['date'] == Timestamp::getDate(time()-86400)) $stats['yesterday'] = $data['visits'];
	}

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
	if ($result = DBQuery::queryAll("SELECT left(date, 6) date, sum(visits) visits 
		FROM {$database['prefix']}DailyStatistics 
		WHERE blogid = $blogid 
		GROUP BY left(date, 6) 
		ORDER BY date DESC")) {
		foreach($result as $record)
			array_push($statistics, $record);
	}
	return $statistics;
}

function getRefererStatistics($blogid) {
	global $database;
	$statistics = array();
	if ($result = DBQuery::queryAll("SELECT host, count FROM {$database['prefix']}RefererStatistics WHERE blogid = $blogid order by count desc limit 20")) {
		foreach($result as $record)
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
	if(DBQuery::queryCount("SELECT blog FROM {$database['prefix']}SessionVisits WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}' AND blog = $blogid") > 0)
		return;
	if (DBQuery::queryCount("INSERT INTO {$database['prefix']}SessionVisits values('$id', '{$_SERVER['REMOTE_ADDR']}', $blogid)") > 0) {
		if(DBQuery::queryCount("UPDATE {$database['prefix']}BlogStatistics SET visits = visits + 1 WHERE blogid = $blogid") < 1) {
			DBQuery::execute("INSERT into {$database['prefix']}BlogStatistics values($blogid, 1)");
		}
		
		$period = Timestamp::getDate();
		if(DBQuery::queryCount("UPDATE {$database['prefix']}DailyStatistics SET visits = visits + 1 WHERE blogid = $blogid AND `date` = $period") < 1) {
			DBQuery::execute("INSERT into {$database['prefix']}DailyStatistics values($blogid, $period, 1)");
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
				if (!DBQuery::execute("UPDATE {$database['prefix']}RefererStatistics SET count = count + 1 WHERE blogid = $blogid AND host = '$host'"))
					DBQuery::execute("INSERT into {$database['prefix']}RefererStatistics values($blogid, '$host', 1)");
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
	if(DBQuery::execute("UPDATE {$database['prefix']}BlogStatistics SET visits = 0 WHERE blogid = $blogid")) {
		return true;
	} else {
		$result = DBQuery::execute("INSERT INTO {$database['prefix']}BlogStatistics values($blogid, 0)");
		return $result;
	}
}
?>
