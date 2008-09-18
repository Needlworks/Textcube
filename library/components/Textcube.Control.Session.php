<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'SESSION_OPENID_USERID', -1 );

final class Session {
	private static $sessionMicrotime;
	private static $sessionName = null;
	
	function __construct() {
		$sessionMicrotime = Timer::getMicroTime();
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
		global $database, $service;
		if ($result = self::query("SELECT data FROM {$database['prefix']}Sessions 
			WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}' AND updated >= (UNIX_TIMESTAMP() - {$service['timeout']})")) {
			return $result;
		}
		return '';
	}
	
	public static function write($id, $data) {
		global $database;
		if (strlen($id) < 32)
			return false;
		$userid = Acl::getIdentity('textcube');
		if( empty($userid) ) {
			$userid = Acl::getIdentity('openid') ? SESSION_OPENID_USERID : '';
		}
		if( empty($userid) ) $userid = 'null';
		$data = POD::escapeString($data);
		$server = POD::escapeString($_SERVER['HTTP_HOST']);
		$request = POD::escapeString(substr($_SERVER['REQUEST_URI'], 0, 255));
		$referer = isset($_SERVER['HTTP_REFERER']) ? POD::escapeString($_SERVER['HTTP_REFERER']) : '';
		$timer = Timer::getMicroTime() - self::$sessionMicrotime;
		$result = POD::queryCount("UPDATE {$database['prefix']}Sessions 
				SET userid = $userid, data = '$data', server = '$server', request = '$request', referer = '$referer', timer = $timer, updated = UNIX_TIMESTAMP() 
				WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
		if ($result && $result == 1)
			return true;
		return false;
	}
	
	public static function destroy($id, $setCookie = false) {
		global $database;
		@self::query("DELETE FROM {$database['prefix']}Sessions 
			WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
		self::gc();
	}
	
	public static function gc($maxLifeTime = false) {
		global $database, $service;
		@self::query("DELETE FROM {$database['prefix']}Sessions 
			WHERE updated < (UNIX_TIMESTAMP() - {$service['timeout']})");
		$result = @self::queryAll("SELECT DISTINCT v.id, v.address 
			FROM {$database['prefix']}SessionVisits v 
			LEFT JOIN {$database['prefix']}Sessions s ON v.id = s.id AND v.address = s.address 
			WHERE s.id IS NULL AND s.address IS NULL");
		if ($result) {
			$gc = array();
			foreach ($result as $g)
				array_push($gc, $g);
			foreach ($gc as $g)
				@self::query("DELETE FROM {$database['prefix']}SessionVisits WHERE id = '{$g[0]}' AND address = '{$g[1]}'");
		}
		return true;
	}
	
	private static function getAnonymousSession() {
		global $database;
		$result = self::query("SELECT id FROM {$database['prefix']}Sessions WHERE address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL");
		if ($result)
			return $result;
		return false;
	}
	
	private static function newAnonymousSession() {
		global $database;
		for ($i = 0; $i < 100; $i++) {
			if (($id = self::getAnonymousSession()) !== false)
				return $id;
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			$result = POD::queryCount("INSERT INTO {$database['prefix']}Sessions(id, address, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
			if ($result > 0)
				return $id;
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
		/* OpenID and Admin sessions are treated as authorized ones*/
		global $database;
		$result = self::queryCell("SELECT id 
			FROM {$database['prefix']}Sessions 
			WHERE id = '$id' 
				AND address = '{$_SERVER['REMOTE_ADDR']}' 
				AND (userid IS NOT NULL OR preexistence IS NOT NULL)");
		if ($result)
			return true;
		return false;
	}
	
	public static function isGuestOpenIDSession($id) {
		global $database;
		$result = self::queryCell("SELECT id 
			FROM {$database['prefix']}Sessions 
			WHERE id = '$id' 
				AND address = '{$_SERVER['REMOTE_ADDR']}' AND userid < 0");
		if ($result)
			return true;
		return false;
	}
	
	public static function set() {
		self::$sessionMicrotime = Timer::getMicroTime();
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
			if( self::isGuestOpenIDSession($id) ) {
				$result = self::execute("UPDATE {$database['prefix']}Sessions
					set userid = $userid WHERE id = '$id' AND address = '{$_SERVER['REMOTE_ADDR']}'");
				if ($result) {
					return true;
				}
			}
		}
		if (self::isAuthorized(session_id()))
			return true;
		for ($i = 0; $i < 100; $i++) {
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			$result = self::execute("INSERT INTO {$database['prefix']}Sessions
				(id, address, userid, created, updated) 
				VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', $userid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
			if ($result) {
				@session_id($id);
				//$service['domain'] = $service['domain'].':8888';
				setcookie( self::getName(), $id, 0, $session_cookie_path, $service['session_cookie_domain']);
				return true;
			}
		}
		return false;
	}

	/* Customized queryset (for recovering Session tables) */
	private static function query($sql) {
		global $database, $sessionDBRepair;
		$result = POD::queryCell($sql);
		if($result === false) {
			if (!isset($sessionDBRepair)) {		
				@POD::query("REPAIR TABLE {$database['prefix']}Sessions, SessionVisits");
				$result = POD::queryCell($sql);
				$sessionDBRepair = true;
			}
		}
		return $result;
	}
	
	private static function queryAll($sql) {
		global $database, $sessionDBRepair;
		$result = POD::queryAll($sql);
		if($result === false) {
			if (!isset($sessionDBRepair)) {		
				@POD::query("REPAIR TABLE {$database['prefix']}Sessions, SessionVisits");
				$result = POD::queryAll($sql);
				$sessionDBRepair = true;
			}
		}
		return $result;
	}

	private static function queryCell($sql) {
		global $database, $sessionDBRepair;
		$result = POD::queryCell($sql);
		if($result === false) {
			if (!isset($sessionDBRepair)) {		
				@POD::query("REPAIR TABLE {$database['prefix']}Sessions, SessionVisits");
				$result = POD::queryCell($sql);
				$sessionDBRepair = true;
			}
		}
		return $result;
	}

	private static function execute($sql) {
		global $database, $sessionDBRepair;
		$result = POD::execute($sql);
		if($result === false) {
			if (!isset($sessionDBRepair)) {		
				@POD::query("REPAIR TABLE {$database['prefix']}Sessions, SessionVisits");
				$result = POD::execute($sql);
				$sessionDBRepair = true;
			}
		}
		return $result;
	}
}
?>
