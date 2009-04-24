<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* LEGACY FUNCTION SUPPORT
   Functions below will be deprecated after Textcube 1.8 or later.
*/

/***** blog.teamblog *****/
function addTeamUser($email, $name, $comment, $senderName, $senderEmail) {
	return Model_Blog::addUser($email, $name, $comment, $senderName, $senderEmail);
}

function changeACLonBlog($blogid, $ACLtype, $userid, $switch) {  // Change user priviledge on the blog.
	return Model_Blog::changeACLofUser($blogid, $userid, $ACLtype, $switch);
}

function deleteTeamblogUser($userid ,$blogid = null, $clean = true) {
	return Model_Blog::deleteUser($blogid, $userid, $clean);
}

function changeBlogOwner($blogid,$userid) {
	return Model_Blog::changeOwner($blogid, $userid);
}

/***** blog.statistics *****/
function getStatistics($blogid) {
	return Model_Statistics::getStatistics($blogid);
}

function getDailyStatistics($period) {
	return Model_Statistics::getDailyStatistics($period);
}

function getMonthlyStatistics($blogid) {
	return Model_Statistics::getMonthlyStatistics($blogid);
}

function getRefererStatistics($blogid) {
	return Model_Statistics::getRefererStatistics($blogid);
}

function getRefererLogsWithPage($page, $count) {
	return Model_Statistics::getRefererLogsWithPage($page,$count);
}  

function getRefererLogs() {
	return Model_Statistics::getRefererLogs();
}

function updateVisitorStatistics($blogid) {
	return Model_Statistics::updateVisitorStatistics($blogid);
}

function setTotalStatistics($blogid) {
	return Model_Statistics::setTotalStatistics($blogid);
}

/***** common.paging *****/
function initPaging($url, $prefix = '?page=') {
	return Model_Paging::initPaging($url,$prefix);
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	return Model_Paging::fetchWithPaging($sql,$page,$count,$url,$prefix,$countItem);
}
?>
