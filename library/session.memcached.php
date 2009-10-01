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
	global $memcache, $service;
	return $memcache->get("{$service['domain']}/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
}

function writeSession($id, $data) {
	global $memcache, $service;
	return $memcache->set("{$service['domain']}/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",$data,$service['timeout']);
}

function destroySession($id, $setCookie = false) {
	global $memcache, $service;
	$memcache->delete("{$service['domain']}/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
	$memcache->delete("{$service['domain']}/anonymousSession/{$_SERVER['REMOTE_ADDR']}");
	return $memcache->delete("{$service['domain']}/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
}

function gcSession($maxLifeTime = false) {
	return true;
}

function getAnonymousSession() {
	global $memcache, $service;
	$anonymousSessionId = $memcache->get("{$service['domain']}/anonymousSession/{$_SERVER['REMOTE_ADDR']}");
	if(!empty($anonymousSessionId)) return $anonymousSessionId;
	else return false;
}

function newAnonymousSession() {
	global $service, $memcache;
	for ($i = 0; $i < 3; $i++) {
		if (($id = getAnonymousSession()) !== false)
			return $id;
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = $memcache->set("{$service['domain']}/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",true,$service['timeout']);
		if ($result > 0) {
			$result = $memcache->set("{$service['domain']}/anonymousSession/{$_SERVER['REMOTE_ADDR']}",$id,$service['timeout']);
			return $id;
		}
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
	global $memcache, $service;
	/* OpenID and Admin sessions are treated as authorized ones*/
	$userid = $memcache->get("{$service['domain']}/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
	if(!empty($userid)) return true;
	else return false;
}

function isGuestOpenIDSession($id) {
	global $memcache, $service;
	$userid = $memcache->get("{$service['domain']}/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
	if(!empty($userid) && $userid < 0) return true;
	else return false;
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
	global $database, $service, $memcache;
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
			$result = $memcache->set("{$service['domain']}/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,$service['timeout']);
			if ($result) {
				return true;
			}
		}
	}
	if (isSessionAuthorized(session_id())) return true;
	for ($i = 0; $i < 3; $i++) {
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		$result = $memcache->set("{$service['domain']}/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,$service['timeout']);

		if ($result) {
			@session_id($id);
			setcookie( getSessionName(), $id, 0, $session_cookie_path, $service['session_cookie_domain']);
			return true;
		}
	}
	return false;
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
