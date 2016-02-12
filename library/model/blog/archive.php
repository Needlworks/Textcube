<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getArchives($blogid, $option = 'yearmonth') {
    $archives = array();
    $skinSetting = Setting::getSkinSettings($blogid);
    $archivesOnPage = $skinSetting['archivesOnPage'];
    $pool = DBModel::getInstance();

    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setLimit($archivesOnPage);

    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }

    switch (POD::dbms()) {
        case 'PostgreSQL':
            if ($option == 'year') {
                $pool->setGroup("year");
                $pool->setOrder("year", "desc");
            } else {
                if ($option == 'month') {
                    $pool->setGroup("month");
                    $pool->setOrder("month", "desc");
                } else {
                    $pool->setGroup("year", "month");
                    $pool->setOrder("year, month", "desc");
                }
            }
            $result = $pool->getAll("EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) AS year, EXTRACT(MONTH FROM FROM_UNIXTIME(e.published)) AS month, COUNT(*) AS count", array('usedbcache' => true, 'cacheprefix' => 'entry'));
            if ($result) {
                foreach ($result as $archive) {
                    switch ($option) {
                        case 'year':
                            $archive['period'] = $archive['year'];
                            break;
                        case 'month':
                            $archive['period'] = sprintf("%02d", $archive['month']);
                            break;
                        case 'yearmonth':
                        default:
                            $archive['period'] = $archive['year'] . sprintf("%02d", $archive['month']);
                            break;
                    }
                    array_push($archives, $archive);
                }
            }
            break;
        case 'SQLite3':
            if ($option == 'year') {
                $format = '%Y';
            } else {
                if ($option == 'month') {
                    $format = '%m';
                } else {
                    $format = '%Y%m';
                }
            }
            $pool->setGroup("period");
            $pool->setOrder("period", "desc");
            $result = $pool->getAll("strftime('" . $format . "',e.published,'unixepoch') AS period, COUNT(*) AS count", array('usedbcache' => true, 'cacheprefix' => 'entry'));
            if ($result) {
                foreach ($result as $archive)
                    array_push($archives, $archive);
            }
            break;
        case 'Cubrid':
            if ($option == 'year') {
                $format = 'YYYY';
            } else {
                if ($option == 'month') {
                    $format = 'MM';
                } else {
                    $format = 'YYYYMM';
                }
            }

            $pool->setGroup("TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'YYYYMM')");
            $pool->setOrder("period", "desc");

            $result = $pool->getAll("TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, '$format') period,
				COUNT(*) \"count\"", array('usedbcache' => true, 'cacheprefix' => 'entry'));
            if ($result) {
                foreach ($result as $archive)
                    array_push($archives, $archive);
            }
            break;
        case 'MySQLi':
        default:
            if ($option == 'year') {
                $format = 'year';
            } else {
                if ($option == 'month') {
                    $format = 'month';
                } else {
                    $format = 'year_month';
                }
            }
            $pool->setGroup("period");
            $pool->setOrder("period", "desc");
            $result = $pool->getAll("EXTRACT(" . $format . " FROM FROM_UNIXTIME(e.published)) period, COUNT(*) count", array('usedbcache' => true, 'cacheprefix' => 'entry'));
            if ($result) {
                foreach ($result as $archive)
                    array_push($archives, $archive);
            }
            break;
    }
    return $archives;
}

function getCalendar($blogid, $period) {
    global $database;
    $skinSetting = Setting::getSkinSettings($blogid);
    $pool = DBModel::getInstance();

    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.category", ">=", 0);
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }

    $calendar = array('days' => array());
    if (($period === true) || !checkPeriod($period)) {
        $period = Timestamp::getYearMonth();
    }
    $calendar['period'] = $period;
    $calendar['year'] = substr($period, 0, 4);
    $calendar['month'] = substr($period, 4, 2);

    switch (POD::dbms()) {
        case 'Cubrid':
            $pool->setQualifier("TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'YYYY')", "eq", $calendar['year'], true);
            $pool->setQualifier("TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'MM')", "eq", $calendar['month'], true);
            $result = $pool->getAll("TO_CHAR(to_timestamp('09:00:00 AM 01/01/1970')+e.published, 'DD')", array('filter' => 'distinct', 'usedbcache' => true, 'cacheprefix' => 'entry'));
            break;
        case 'SQLite3':
            $pool->setQualifier("strftime('%Y',e.published,'unixepoch')", "eq", $calendar['year'], true);
            $pool->setQualifier("strftime('%m',e.published,'unixepoch')", "eq", $calendar['month'], true);
            $result = $pool->getAll("strftime('%d',e.published,'unixepoch')", array('filter' => 'distinct', 'usedbcache' => true, 'cacheprefix' => 'entry'));
            break;
        case 'MySQL':
        case 'MySQLi':
        case 'PostgreSQL':
        default:
            $pool->setQualifier("YEAR(FROM_UNIXTIME(e.published))", "eq", $calendar['year'], true);
            $pool->setQualifier("MONTH(FROM_UNIXTIME(e.published))", "eq", $calendar['month'], true);
            $result = $pool->getAll("DAYOFMONTH(FROM_UNIXTIME(e.published))", array('filter' => 'distinct', 'usedbcache' => true, 'cacheprefix' => 'entry'));
            break;
    }
    if ($result) {
        foreach ($result as $dayArray) {
            list($day) = $dayArray;
            array_push($calendar['days'], $day);
        }
    }
    $calendar['days'] = array_flip($calendar['days']);
    return $calendar;
}

?>
