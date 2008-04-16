<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* LEGACY FUNCTION SUPPORT
   Functions below will be deprecated after Textcube 1.7 or later.
*/
/***** blog.statistics *****/
function getStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getStatistics($blogid);
}

function getDailyStatistics($period) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getDailyStatistics($period);
}

function getMonthlyStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getMonthlyStatistics($blogid);
}

function getRefererStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererStatistics($blogid);
}

function getRefererLogsWithPage($page, $count) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererLogsWithPage($page,$count);
}  

function getRefererLogs() {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::getRefererLogs();
}

function updateVisitorStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::updateVisitorStatistics($blogid);
}

function setTotalStatistics($blogid) {
	requireComponent('Textcube.Model.Statistics');
	return Statistics::setTotalStatistics($blogid);
}

/***** common.paging *****/
function initPaging($url, $prefix = '?page=') {
	requireComponent('Textcube.Model.Paging');
	return Paging::initPaging($url,$prefix);
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	requireComponent('Textcube.Model.Paging');
	return Paging::fetchWithPaging($sql,$page,$count,$url,$prefix,$countItem);
}
?>
