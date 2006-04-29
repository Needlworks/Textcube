<?

function getArchives($owner) {
	global $database;
	$archives = array();
	$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
	$result = mysql_query("SELECT EXTRACT(year_month FROM FROM_UNIXTIME(published)) period, COUNT(*) count FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 GROUP BY period ORDER BY period DESC LIMIT 5");
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
	$result = mysql_query("SELECT DISTINCT DAYOFMONTH(FROM_UNIXTIME(published)) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 AND YEAR(FROM_UNIXTIME(published)) = {$calendar['year']} AND MONTH(FROM_UNIXTIME(published)) = {$calendar['month']}");
	if ($result) {
		while (list($day) = mysql_fetch_array($result))
			array_push($calendar['days'], $day);
	}
	$calendar['days'] = array_flip($calendar['days']);
	return $calendar;
}
?>