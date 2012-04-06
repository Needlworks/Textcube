<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/* LEGACY FUNCTION SUPPORT
   Functions below will be deprecated after Textcube 1.8 or later.
*/

/***** blog.teamblog *****/
function addTeamUser($email, $name, $comment, $senderName, $senderEmail) {
	return Blog::addUser($email, $name, $comment, $senderName, $senderEmail);
}

function changeACLonBlog($blogid, $ACLtype, $userid, $switch) {  // Change user priviledge on the blog.
	return Blog::changeACLofUser($blogid, $userid, $ACLtype, $switch);
}

function deleteTeamblogUser($userid ,$blogid = null, $clean = true) {
	return Blog::deleteUser($blogid, $userid, $clean);
}

function changeBlogOwner($blogid,$userid) {
	return Blog::changeOwner($blogid, $userid);
}

/***** blog.statistics *****/
function getStatistics($blogid) {
	return Statistics::getStatistics($blogid);
}

function getDailyStatistics($period) {
	return Statistics::getDailyStatistics($period);
}

function getMonthlyStatistics($blogid) {
	return Statistics::getMonthlyStatistics($blogid);
}

function getRefererStatistics($blogid) {
	return Statistics::getRefererStatistics($blogid);
}

function getRefererLogsWithPage($page, $count) {
	return Statistics::getRefererLogsWithPage($page,$count);
}  

function getRefererLogs() {
	return Statistics::getRefererLogs();
}

function updateVisitorStatistics($blogid) {
	return Statistics::updateVisitorStatistics($blogid);
}

function setTotalStatistics($blogid) {
	return Statistics::setTotalStatistics($blogid);
}

/***** common.paging *****/
function pagingInit($url, $prefix = '?page=') {
	return Paging::init($url,$prefix);
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	return Paging::fetch($sql,$page,$count,$url,$prefix,$countItem);
}
?>
