<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


function getUserEmail($userid) {
	global $database;
	return POD::queryCell("SELECT loginid
		FROM {$database['prefix']}Users
		WHERE userid = ".$userid);
}

function getUserIdByEmail($email) {
	global $database;
	$email = trim($email);
	return POD::queryCell("SELECT userid 
		FROM {$database['prefix']}Users
		WHERE loginid = '".$email."'");
}

function getUserNamesOfBlog($blogid) {
	global $database;

	$authorIds = POD::queryColumn("SELECT userid
		FROM {$database['prefix']}Teamblog
		WHERE blogid = $blogid");

	$authorInfo = POD::queryAll("SELECT userid, name
		FROM {$database['prefix']}Users
		WHERE userid IN (".implode(",",$authorIds).")");
	return $authorInfo;
}

function deleteUser($userid) {
	global $database;
	if( POD::execute("DELETE FROM {$database['prefix']}UserSettings WHERE userid = '$userid' AND name = 'AuthToken' LIMIT 1") ) {
		return POD::execute("DELETE FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	else {
		return false;
	}
}
?>
