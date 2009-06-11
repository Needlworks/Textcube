<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'SESSION_OPENID_USERID', -1 );

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

function getSessionName() {
	global $service;
	static $sessionName = null;
	if( $sessionName == null ) { 
		if( !empty($service['session_cookie']) ) {
			$sessionName = $session['session_cookie'];
		} else {
			$sessionName = 'TSSESSION'.$service['domain'].$service['path']; 
			$sessionName = preg_replace( '/[^a-zA-Z0-9]/', '', $sessionName );
		}
	}
	return $sessionName;
}

function readSession($id) {
	global $database, $service;
	if ($result = sessionQuery('cell',"SELECT privilege FROM {$database['prefix']}Sessions 
		WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}' AND updated >= (UNIX_TIMESTAMP() - {$service['timeout']})")) {
		return $result;
	}
	return '';
}

function writeSession($id, $data) {
	global $database;
	global $sessionMicrotime;
	if (strlen($id) < 32)
		return false;
	$userid = Acl::getIdentity('textcube');
	if( empty($userid) ) {
		$userid = Acl::getIdentity('openid') ? SESSION_OPENID_USERID : '';
	}
	if( empty($userid) ) $userid = 'null';
	$data = POD::escapeString($data);
	$server = POD::escapeString($_SERVER['HTTP_HOST']);
	$request = POD::escapeString(substr($_SERVER['REQUEST_URI'],0,255));
	$referer = isset($_SERVER['HTTP_REFERER']) ? POD::escapeString(substr($_SERVER['HTTP_REFERER'],0,255)) : '';
	$timer = getMicrotimeAsFloat() - $sessionMicrotime;

	$result = sessionQuery('count',"UPDATE {$database['prefix']}Sessions 
			SET userid = $userid, privilege = '$data', server = '$server', request = '$request', referer = '$referer', timer = $timer, updated = UNIX_TIMESTAMP() 
			WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
	if ($result && $result == 1) {
		@POD::commit();
		return true;
	}
	return false;
}

function destroySession($id, $setCookie = false) {
	global $database;
	@sessionQuery('query',"DELETE FROM {$database['prefix']}Sessions 
		WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
	gcSession();
}

function gcSession($maxLifeTime = false) {
	global $database, $service;
	@sessionQuery('query',"DELETE FROM {$database['prefix']}Sessions 
		WHERE updated < (UNIX_TIMESTAMP() - {$service['timeout']})");
	$result = @sessionQuery('all',"SELECT DISTINCT v.id, v.address 
		FROM {$database['prefix']}SessionVisits v 
		LEFT JOIN {$database['prefix']}Sessions s ON v.id = s.id AND v.address = s.address 
		WHERE s.id IS NULL AND s.address IS NULL");
	if ($result) {
		$gc = array();
		foreach ($result as $g)
			array_push($gc, $g);
		foreach ($gc as $g)
			@sessionQuery('query',"DELETE FROM {$database['prefix']}SessionVisits WHERE id = '{$g[0]}' AND address = '{$g[1]}'");
	}
	return true;
}

function getAnonymousSession() {
	global $database;
	$result = sessionQuery('cell',"SELECT id FROM {$database['prefix']}Sessions WHERE address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL");
	if ($result)
		return $result;
	return false;
}

function newAnonymousSession() {
	global $database, $service;
	$meet_again_baby = 3600;
	if( isset($service['timeout']) ) { 
		$meet_again_baby = $service['timeout'];
	}

	//If you are not a robot, subsequent UPDATE query will override to proper timestamp.
	$meet_again_baby -= 60;

	for ($i = 0; $i < 3; $i++) {
		if (($id = getAnonymousSession()) !== false)
			return $id;
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = sessionQuery('count',"INSERT INTO {$database['prefix']}Sessions (id, address, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() - $meet_again_baby)");
		if ($result > 0)
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

function isSessionAuthorized($id) {
	/* OpenID and Admin sessions are treated as authorized ones*/
	global $database;
	$result = sessionQuery('cell',"SELECT id 
		FROM {$database['prefix']}Sessions 
		WHERE id = '$id' 
			AND address = '{$_SERVER['REMOTE_ADDR']}' 
			AND (userid IS NOT NULL OR preexistence IS NOT NULL)");
	if ($result)
		return true;
	return false;
}

function isGuestOpenIDSession($id) {
	global $database;
	$result = sessionQuery('cell',"SELECT id 
		FROM {$database['prefix']}Sessions 
		WHERE id = '$id' 
			AND address = '{$_SERVER['REMOTE_ADDR']}' AND userid < 0");
	if ($result)
		return true;
	return false;
}

function setSession() {
	if( !empty($_GET['TSSESSION']) ) {
		$id = $_GET['TSSESSION'];
		$_COOKIE[session_name()] = $id;
	} else if ( !empty($_COOKIE[getSessionName()]) ) {
		$id = $_COOKIE[getSessionName()];
	} else {
		$id = '';
	}
	if ((strlen($id) < 32) || !isSessionAuthorized($id)) {
		setSessionAnonymous($id);
	}
}

function authorizeSession($blogid, $userid) {
	global $database, $service;
	$session_cookie_path = "/";
	if( !empty($service['session_cookie_path']) ) {
		$session_cookie_path = $service['session_cookie_path'];
	}
	if (!is_numeric($userid))
		return false;
	if( $userid != SESSION_OPENID_USERID ) { /* OpenID session : -1 */
		$_SESSION['userid'] = $userid;
		$id = session_id();
		if( isGuestOpenIDSession($id) ) {
			$result = sessionQuery('execute',"UPDATE {$database['prefix']}Sessions
				SET userid = $userid WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
			if ($result) {
				return true;
			}
		}
	}
	if (isSessionAuthorized(session_id()))
		return true;
	for ($i = 0; $i < 3; $i++) {
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = sessionQuery('execute',"INSERT INTO {$database['prefix']}Sessions
			(id, address, userid, created, updated) 
			VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', $userid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if ($result) {
			@session_id($id);
			//$service['domain'] = $service['domain'].':8888';
			setcookie( getSessionName(), $id, 0, $session_cookie_path, $service['session_cookie_domain']);
			return true;
		}
	}
	return false;
}


function sessionQuery($mode = 'query', $sql) {
	global $database;
	static $sessionDBRepair = false;
	$result = _sessionQuery($mode, $sql);
	if($result === false) {
		if ($sessionDBRepair === false) {		
			@POD::query("REPAIR TABLE {$database['prefix']}Sessions");
			@POD::query("REPAIR TABLE {$database['prefix']}SessionVisits");
			$result = _sessionQuery($mode, $sql);
			$sessionDBRepair = true;
		}
	}
	return $result;
}

function _sessionQuery($mode = 'query',$sql) {
	switch($mode) {
		case 'execute' :
			return POD::execute($sql);
		case 'all' :
			return POD::queryAll($sql);
		case 'cell' :
			return POD::queryCell($sql);
		case 'count' :
			return POD::queryCount($sql);
		case 'query' :
		default :
			return POD::query($sql);
	}
	return null;
}

function startSession() {
	global $service;
	session_name(getSessionName());
	setSession();
	session_set_save_handler('openSession', 'closeSession', 'readSession', 'writeSession', 'destroySession', 'gcSession');
	session_cache_expire(1);
	session_set_cookie_params(0, '/', $service['domain']);
	if (session_start() !== true) {
		header('HTTP/1.1 503 Service Unavailable');
		exit;
	}
}
?>