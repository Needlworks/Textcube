<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
function getMicrotimeAsFloat() {
	list($usec, $sec) = explode(" ", microtime());
	return ($usec + $sec);
}
$sessionMicrotime = getMicrotimeAsFloat();

function openSession($savePath, $sessionName) {
	return true;
}

function closeSession() {
	return true;
}

function readSession($id) {
	global $database, $service;
	if ($result = sessionQuery("SELECT data FROM {$database['prefix']}Sessions WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}' AND updated >= (UNIX_TIMESTAMP() - {$service['timeout']})")) {
		if ($session = mysql_fetch_array($result))
			return $session['data'];
	}
	return '';
}

function writeSession($id, $data) {
	global $database;
	global $sessionMicrotime;
	if (strlen($id) < 32)
		return false;
	$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : 'null';
	$data = mysql_tt_escape_string($data);
	$server = mysql_tt_escape_string($_SERVER['HTTP_HOST']);
	$request = mysql_tt_escape_string($_SERVER['REQUEST_URI']);
	$referer = isset($_SERVER['HTTP_REFERER']) ? mysql_tt_escape_string($_SERVER['HTTP_REFERER']) : '';
	$timer = getMicrotimeAsFloat() - $sessionMicrotime;
	$result = mysql_query("UPDATE {$database['prefix']}Sessions SET userid = $userid, data = '$data', server = '$server', request = '$request', referer = '$referer', timer = $timer, updated = UNIX_TIMESTAMP() WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
	if ($result && (mysql_affected_rows() == 1))
		return true;
	return false;
}

function destroySession($id, $setCookie = false) {
	global $database;
	@mysql_query("DELETE FROM {$database['prefix']}Sessions WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
	gcSession();
}

function gcSession($maxLifeTime = false) {
	global $database, $service;
	@mysql_query("DELETE FROM {$database['prefix']}Sessions WHERE updated < (UNIX_TIMESTAMP() - {$service['timeout']})");
	$result = @sessionQuery("SELECT DISTINCT v.id, v.address FROM {$database['prefix']}SessionVisits v LEFT JOIN {$database['prefix']}Sessions s ON v.id = s.id AND v.address = s.address WHERE s.id IS NULL AND s.address IS NULL");
	if ($result) {
		$gc = array();
		while ($g = mysql_fetch_row($result))
			array_push($gc, $g);
		foreach ($gc as $g)
			@mysql_query("DELETE FROM {$database['prefix']}SessionVisits WHERE id = '{$g[0]}' AND address = '{$g[1]}'");
	}
	return true;
}

function getAnonymousSession() {
	global $database;
	$result = sessionQuery("SELECT id FROM {$database['prefix']}Sessions WHERE address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL");
	if ($result && (list($id) = mysql_fetch_array($result)))
		return $id;
	return false;
}

function newAnonymousSession() {
	global $database;
	for ($i = 0; $i < 100; $i++) {
		if (($id = getAnonymousSession()) !== false)
			return $id;
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		mysql_query("INSERT INTO {$database['prefix']}Sessions(id, address, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if (mysql_affected_rows() > 0)
			return $id;
	}
	return false;
}

function setSessionAnonymous($currentId) {
	$id = getAnonymousSession();
	if ($id !== false) {
		if ($id != $currentId)
			session_id($id);
		return true;
	}
	$id = newAnonymousSession();
	if ($id !== false) {
		session_id($id);
		return true;
	}
	return false;
}

function newSession() {
	global $database;
	for ($i = 0; ($i < 100) && !setSessionAnonymous(); $i++) {
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = mysql_query("INSERT INTO {$database['prefix']}Sessions(id, address, created, updated) SELECT DISTINCT '$id', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if (($result !== false) && (mysql_affected_rows() > 0)) {
			session_id($id);
			return true;
		}
	}
	return false;
}

function isSessionAuthorized($id) {
	global $database;
	$result = mysql_query("select id from {$database['prefix']}Sessions where id = '$id' and address = '{$_SERVER['REMOTE_ADDR']}' and (userid is not null or preexistence is not null)");
	if ($result && (mysql_num_rows($result) == 1))
		return true;
	return false;
}

function setSession() {
	$id = empty($_COOKIE[session_name()]) ? '' : $_COOKIE[session_name()];
	if ((strlen($id) < 32) || !isSessionAuthorized($id))
		setSessionAnonymous($id);
}

// Teamblog : insert userid to variable admin when member logins.
function authorizeSession($blogid, $userid) {
	global $database, $service;
	if (!is_numeric($userid))
		return false;
	$_SESSION['userid'] = $userid;
	$_SESSION['blogid'] = $blogid;
	if (isSessionAuthorized(session_id()))
		return true;
	for ($i = 0; $i < 100; $i++) {
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = mysql_query("INSERT INTO {$database['prefix']}Sessions(id, address, userid, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', $userid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if ($result && (mysql_affected_rows() == 1)) {
			@session_id($id);
			header("Set-Cookie: TSSESSION=$id; path=/; domain={$service['domain']}");
			return true;
		}
	}
	return false;
}

function sessionQuery($sql) {
	global $database, $sessionDBRepair;
	$result = mysql_query($sql);
	if($result === false) {
		if (!isset($sessionDBRepair)) {		
			mysql_query("REPAIR TABLE {$database['prefix']}Sessions");
			$result = mysql_query($sql);
			$sessionDBRepair = true;
		}
	}
	return $result;
}

session_name('TSSESSION');
setSession();
session_set_save_handler('openSession', 'closeSession', 'readSession', 'writeSession', 'destroySession', 'gcSession');
session_cache_expire(1);
session_set_cookie_params(0, '/', $service['domain']);
if (session_start() !== true) {
	header('HTTP/1.1 503 Service Unavailable');
}
?>
