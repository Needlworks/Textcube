<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function checkPeriod($period) {
	if (is_numeric($period)) {
		$year = 0;
		$month = 1;
		$day = 1;
		switch (strlen($period)) {
			case 8:
				$day = substr($period, 6, 2);
			case 6:
				$month = substr($period, 4, 2);
			case 4:
				$year = substr($period, 0, 4);
				return checkdate($month, $day, $year);
		}
	}
	return false;
}

function timeInterval($start, $current) {
	$interval = $current - $start;
	if (0 <= $interval && $interval < 60) {
		return $interval . _text('초');
	} else if (60 <= $interval && $interval < 3600) {
		return ceil($interval / 60) . _text('분');
	} else if (3600 <= $interval && $interval < 86400) {
		return ceil($interval / 60 / 60) . _text('시간');
	} else if (86400 <= $interval && $interval < 2592000) {
		return ceil($interval / 60 / 60 / 24) . _text('일');
	} else if (2592000 <= $interval && $interval < 31104000) {
		return ceil($interval / 60 / 60 / 24 / 30) . _text('달');
	} else if (31104000 <= $interval) {
		return ceil($interval / 60 / 60 / 24 / 30 / 12) . _text('년');
	}
	return ceil($interval / 60 / 60 / 24 / 30 / 12 / 100) . _test('세기');
}

function getTimeFromPeriod($period) {
	if (is_numeric($period)) {
		$year = 0;
		$month = 1;
		$day = 1;
		switch (strlen($period)) {
			case 8:
				$day = substr($period, 6, 2);
			case 6:
				$month = substr($period, 4, 2);
			case 4:
				$year = substr($period, 0, 4);
				if (checkdate($month, $day, $year))
					return mktime(0, 0, 0, $month, $day, $year);
		}
	}
	return false;
}

function addPeriod($period, $inc = 1) {
	if (checkPeriod($period) !== false) {
		switch (strlen($period)) {
			case 4:
				return strftime('%Y', mktime(0, 0, 0, 1, 1, $period + $inc));
			case 6:
				return strftime('%Y%m', mktime(0, 0, 0, substr($period, 4) + $inc, 1, substr($period, 0, 4)));
			case 8:
				return strftime('%Y%m%d', mktime(0, 0, 0, substr($period, 4, 2), substr($period, 6, 2) + $inc, substr($period, 0, 4)));
		}
	}
	return false;
}

function getPeriodLabel($period) {
	$name = strval($period);
	switch (strlen($name)) {
		case 4:
			return $name;
		case 6:
			return substr($name, 0, 4) . '/' . substr($name, 4);
		case 8:
			return substr($name, 0, 4) . '/' . substr($name, 4, 2) . '/' . substr($name, 6) . '';
	}
	return '';
}
?>
