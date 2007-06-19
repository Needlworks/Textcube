<?php 
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getArchives($blogid) {
	global $database;
	$archives = array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$archivesOnPage = DBQuery::queryCell("SELECT archivesOnPage FROM {$database['prefix']}SkinSettings WHERE owner = $blogid");
	$result = DBQuery::query("SELECT EXTRACT(year_month FROM FROM_UNIXTIME(e.published)) period, COUNT(*) count 
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id AND e.owner = c.owner
		WHERE e.owner = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
		GROUP BY period 
		ORDER BY period 
		DESC LIMIT $archivesOnPage");
	if ($result) {
		while ($archive = mysql_fetch_array($result))
			array_push($archives, $archive);
	}
	return $archives;
}

function getCalendar($blogid, $period) {
	global $database;
	$calendar = array('days' => array());
	if (($period === true) || !checkPeriod($period))
		$period = Timestamp::getYearMonth();
	$calendar['period'] = $period;
	$calendar['year'] = substr($period, 0, 4);
	$calendar['month'] = substr($period, 4, 2);
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$result = DBQuery::query("SELECT DISTINCT DAYOFMONTH(FROM_UNIXTIME(e.published)) 
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.category = c.id AND e.owner = c.owner
		WHERE e.owner = $blogid AND e.draft = 0 $visibility AND e.category >= 0 AND YEAR(FROM_UNIXTIME(e.published)) = {$calendar['year']} AND MONTH(FROM_UNIXTIME(e.published)) = {$calendar['month']}");
	if ($result) {
		while (list($day) = mysql_fetch_array($result))
			array_push($calendar['days'], $day);
	}
	$calendar['days'] = array_flip($calendar['days']);
	return $calendar;
}
?>
