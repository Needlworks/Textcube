<?php 
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getArchives($owner) {
	global $database;
	$archives = array();
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$query = DBQuery::query("SELECT archivesOnPage FROM {$database['prefix']}SkinSettings WHERE owner = $owner");
	$row = mysql_fetch_row($query);
	$archivesOnPage = $row[0];
	$result = DBQuery::query("SELECT EXTRACT(year_month FROM FROM_UNIXTIME(published)) period, COUNT(*) count FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 GROUP BY period ORDER BY period DESC LIMIT $archivesOnPage");
	if ($result) {
		while ($archive = mysql_fetch_array($result))
			array_push($archives, $archive);
	}
	return $archives;
}

function getCalendar($owner, $period) {
	global $database;
	$calendar = array('days' => array());
	if (($period === true) || !checkPeriod($period))
		$period = Timestamp::getYearMonth();
	$calendar['period'] = $period;
	$calendar['year'] = substr($period, 0, 4);
	$calendar['month'] = substr($period, 4, 2);
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$result = DBQuery::query("SELECT DISTINCT DAYOFMONTH(FROM_UNIXTIME(published)) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 AND YEAR(FROM_UNIXTIME(published)) = {$calendar['year']} AND MONTH(FROM_UNIXTIME(published)) = {$calendar['month']}");
	if ($result) {
		while (list($day) = mysql_fetch_array($result))
			array_push($calendar['days'], $day);
	}
	$calendar['days'] = array_flip($calendar['days']);
	return $calendar;
}
?>
