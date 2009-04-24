<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Model_Session implements ISession {
	private static $sessionName = null;
	private static $mc = null;	

	private static function initialize() {
		global $memcache;        /** After PHP 5.0.5, session write performs after object destruction. */
		self::$mc = $memcache;   /** To Avoid this, just copy memcache handle into Session object.     */
	}

	public static function open($savePath, $sessionName) {
		return true;
	}
	
	public static function close() {
		return true;
	}
	
	public static function getName() {
		global $service;
		if( self::$sessionName == null ) { 
			if( !empty($service['session_cookie']) ) {
				self::$sessionName = $session['session_cookie'];
			} else {
				self::$sessionName = 'TSSESSION'.$service['domain'].$service['path']; 
				self::$sessionName = preg_replace( '/[^a-zA-Z0-9]/', '', self::$sessionName );
			}
		}
		return self::$sessionName;
	}
	
	public static function read($id) {
		global $service, $memcache;
		return $memcache->get("sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
	}
	
	public static function write($id, $data) {
		global $service;
		return self::$mc->set("sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",$data,$service['timeout']);
	}
	
	public static function destroy($id, $setCookie = false) {
		global $memcache;
		$memcache->delete("sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
		$memcache->delete("anonymousSession/{$_SERVER['REMOTE_ADDR']}");
		return $memcache->delete("authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
	}
	
	public static function gc($maxLifeTime = false) {
		return true;
	}
	
	private static function getAnonymousSession() {
		global $memcache;
		$anonymousSessionId = $memcache->get("anonymousSession/{$_SERVER['REMOTE_ADDR']}");
		if(!empty($anonymousSessionId)) return $anonymousSessionId;
		else return false;
	}
	
	private static function newAnonymousSession() {
		global $service, $memcache;
		for ($i = 0; $i < 3; $i++) {
			if (($id = self::getAnonymousSession()) !== false)
				return $id;
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			$result = $memcache->set("sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",true,$service['timeout']);
			if ($result > 0) {
				$result = $memcache->set("anonymousSession/{$_SERVER['REMOTE_ADDR']}",$id,$service['timeout']);
				return $id;
			}
		}
		return false;
	}
	
	public static function setSessionAnonymous($currentId) {
		$id = self::getAnonymousSession();
		if ($id !== false) {
			if ($id != $currentId)
				session_id($id);
			return true;
		}
		$id = self::newAnonymousSession();
		if ($id !== false) {
			session_id($id);
			return true;
		}
		return false;
	}
	
	public static function isAuthorized($id) {
		global $memcache;
		if(is_null(self::$mc)) self::initialize();
		/* OpenID and Admin sessions are treated as authorized ones*/
		$userid = $memcache->get("authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
		if(!empty($userid)) return true;
		else return false;
	}
	
	public static function isGuestOpenIDSession($id) {
		global $memcache;
		$userid = $memcache->get("authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
		if(!empty($userid) && $userid < 0) return true;
		else return false;
	}
	
	public static function set() {
		if(is_null(self::$mc)) self::initialize();
		if( !empty($_GET['TSSESSION']) ) {
			$id = $_GET['TSSESSION'];
			$_COOKIE[session_name()] = $id;
		} else if ( !empty($_COOKIE[session_name()]) ) {
			$id = $_COOKIE[session_name()];
		} else {
			$id = '';
		}
		if ((strlen($id) < 32) || !self::isAuthorized($id)) {
			self::setSessionAnonymous($id);
		}
	}
	
	public static function authorize($blogid, $userid) {
		global $database, $service, $memcache;
		$session_cookie_path = "/";
		if( !empty($service['session_cookie_path']) ) {
			$session_cookie_path = $service['session_cookie_path'];
		}
		if (!is_numeric($userid)) return false;
		if( $userid != SESSION_OPENID_USERID ) { /* OpenID session : -1 */
			$_SESSION['userid'] = $userid;
			$id = session_id();
			if( self::isGuestOpenIDSession($id) ) {
				$result = $memcache->set("authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,$service['timeout']);
				if ($result) {
					return true;
				}
			}
		}
		if (self::isAuthorized(session_id())) return true;
		for ($i = 0; $i < 3; $i++) {
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			$result = $memcache->set("authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,$service['timeout']);
			
			if ($result) {
				@session_id($id);
				setcookie( self::getName(), $id, 0, $session_cookie_path, $service['session_cookie_domain']);
				return true;
			}
		}
		return false;
	}
}
?>
