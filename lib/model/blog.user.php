<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $__gUserInfo;

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
	global $database, $__gUserInfo;

	$authorIds = POD::queryColumn("SELECT userid
		FROM {$database['prefix']}Teamblog
		WHERE blogid = $blogid");

	$authorInfo = POD::queryAll("SELECT userid, name
		FROM {$database['prefix']}Users
		WHERE userid IN (".implode(",",$authorIds).")");
	return $authorInfo;
}

function getNumberOfPostsByUser($blogid, $userid) {
	return 0;
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

function changeSetting($userid, $email, $nickname) {
	global $database;
	if (strcmp($email, UTF8::lessenAsEncoding($email, 64)) != 0) return false;
	$email = POD::escapeString(UTF8::lessenAsEncoding($email, 64));
	$nickname = POD::escapeString(UTF8::lessenAsEncoding($nickname, 32));
	if ($email == '' || $nickname == '') {
		return false;
	}
	$sql = "UPDATE `{$database['prefix']}Users` SET loginid = '$email', name = '$nickname' WHERE `userid` = $userid";
	$result = POD::query($sql);
	if (!$result) {
		return false;
	} else {
		return true;
	}
}
?>
