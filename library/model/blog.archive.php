<?php 
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getArchives($blogid, $option = 'yearmonth') {
	global $database;
	$archives = array();
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	$skinSetting = getSkinSettings($blogid);
	$archivesOnPage = $skinSetting['archivesOnPage'];
	
	switch (POD::dbms()) {
		case 'PostgreSQL':
			if($option == 'year') $format = 'year';
			else if ($option == 'month') $format = 'month';
			else $format = 'year, month';
			$sql = "SELECT EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) AS year, EXTRACT(MONTH FROM FROM_UNIXTIME(e.published)) AS month, COUNT(*) AS count 
				FROM {$database['prefix']}Entries e
				WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
				GROUP BY $format 
				ORDER BY $format
				DESC LIMIT $archivesOnPage";
			$result = POD::queryAllWithDBCache($sql, 'entry');
			if ($result) {
				foreach($result as $archive) {
					switch ($option) {
						case 'year':
							$archive['period'] = $archive['year'];
							break;
						case 'month':
							$archive['period'] = sprintf("%02d",$archive['month']);
							break;
						case 'yearmonth':
						default:
							$archive['period'] = $archive['year'].sprintf("%02d",$archive['month']);
							break;
					}
					array_push($archives, $archive);
				}
			}
			break;
		case 'Cubrid':
			if($option == 'year') $format = 'YYYY';
			else if ($option == 'month') $format = 'MM';
			else $format = 'YYYYMM';
			$sql = "SELECT TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, '$format') period, 
				COUNT(*) \"count\"
				FROM {$database['prefix']}Entries e
				WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
				GROUP BY TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'YYYYMM') 
				ORDER BY period
				DESC FOR ORDERBY_NUM() BETWEEN 1 AND $archivesOnPage";
			$result = POD::queryAllWithDBCache($sql, 'entry');
			if($result) {
				foreach($result as $archive)
					array_push($archives, $archive);
			}
			break;
		case 'MySQL':
		case 'MySQLi':
		default:
			if($option == 'year') $format = 'year';
			else if ($option == 'month') $format = 'month';
			else $format = 'year_month';
			$sql = "SELECT EXTRACT($format FROM FROM_UNIXTIME(e.published)) period, COUNT(*) count 
				FROM {$database['prefix']}Entries e
				WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
				GROUP BY period 
				ORDER BY period 
				DESC LIMIT $archivesOnPage";
			$result = POD::queryAllWithDBCache($sql, 'entry');
			if ($result) {
				foreach($result as $archive)
					array_push($archives, $archive);
			}
			break;
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
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0'.getPrivateCategoryExclusionQuery($blogid);
	
	switch(POD::dbms()) {
		case 'Cubrid':
			$result=POD::queryAllWithDBCache("SELECT DISTINCT TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'DD')
				FROM {$database['prefix']}Entries e
				WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 AND
					TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'YYYY') = '{$calendar['year']}' AND
					TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'MM') = '{$calendar['month']}'",'entry');	
			break;
		case 'MySQL':
		case 'MySQLi':
		case 'PostgreSQL':
		default:	
			$result = POD::queryAllWithDBCache("SELECT DISTINCT DAYOFMONTH(FROM_UNIXTIME(e.published)) 
				FROM {$database['prefix']}Entries e
				WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 AND YEAR(FROM_UNIXTIME(e.published)) = {$calendar['year']} AND MONTH(FROM_UNIXTIME(e.published)) = {$calendar['month']}",'entry');
			break;
	}
	if ($result) {
		foreach($result as $dayArray) {
			list($day) = $dayArray;
			array_push($calendar['days'], $day);
		}
	}
	$calendar['days'] = array_flip($calendar['days']);
	return $calendar;
}
?>
