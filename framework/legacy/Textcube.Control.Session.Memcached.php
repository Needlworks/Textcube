<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define( 'SESSION_OPENID_USERID', -1 );

final class Session {
	private static $sessionName = null;
	private static $mc = null;	
	private static $context;
	private static function initialize() {
		global $memcache;        /** After PHP 5.0.5, session write performs after object destruction. */
		self::$mc = $memcache;   /** To Avoid this, just copy memcache handle into Session object.     */
		self::$context = Model_Context::getInstance();
	}

	public static function open($savePath, $sessionName) {
		return true;
	}
	
	public static function close() {
		return true;
	}
	
	public static function getName() {
		if(is_null(self::$mc)) self::initialize();
		if( self::$sessionName == null ) { 
			if( !is_null(self::$context->getProperty('service.session_cookie'))) {
				self::$sessionName = self::$context->getProperty('service.session_cookie');
			} else {
				self::$sessionName = 'TSSESSION'.self::$context->getProperty('service.domain').self::$context->getProperty('service.path'); 
				self::$sessionName = preg_replace( '/[^a-zA-Z0-9]/', '', self::$sessionName );
			}
		}
		return self::$sessionName;
	}
	
	public static function read($id) {
		if(is_null(self::$mc)) self::initialize();
		//return self::$mc->get(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
		return self::$mc->get(self::$context->getProperty('service.domain')."/sessions/{$id}");
	}
	
	public static function write($id, $data) {
		if(is_null(self::$mc)) self::initialize();		
		//return self::$mc->set(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",$data,0,self::$context->getProperty('service.timeout'));
		return self::$mc->set(self::$context->getProperty('service.domain')."/sessions/{$id}",$data,0,self::$context->getProperty('service.timeout'));
	}
	
	public static function destroy($id, $setCookie = false) {
		//self::$mc->delete(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}");
		self::$mc->delete(self::$context->getProperty('service.domain')."/sessions/{$id}");
		self::$mc->delete(self::$context->getProperty('service.domain')."/anonymousSession/{$_SERVER['REMOTE_ADDR']}");
		return self::$mc->delete(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
	}
	
	public static function gc($maxLifeTime = false) {
		return true;
	}
	
	private static function getAnonymousSession() {
		$anonymousSessionId = self::$mc->get(self::$context->getProperty('service.domain')."/anonymousSession/{$_SERVER['REMOTE_ADDR']}");
		if(!empty($anonymousSessionId)) return $anonymousSessionId;
		else return false;
	}
	
	private static function newAnonymousSession() {
		for ($i = 0; $i < 3; $i++) {
			if (($id = self::getAnonymousSession()) !== false)
				return $id;
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			$result = self::$mc->set(self::$context->getProperty('service.domain')."/sessions/{$id}/{$_SERVER['REMOTE_ADDR']}",true,0,self::$context->getProperty('service.timeout'));
			if ($result > 0) {
				$result = self::$mc->set(self::$context->getProperty('service.domain')."/anonymousSession/{$_SERVER['REMOTE_ADDR']}",$id,0,self::$context->getProperty('service.timeout'));
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
		if(is_null(self::$mc)) self::initialize();
		/* OpenID and Admin sessions are treated as authorized ones*/
		//$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
		$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}");
		if(!empty($userid)) return true;
		else return false;
	}
	
	public static function isGuestOpenIDSession($id) {
		if(is_null(self::$mc)) self::initialize();
		//$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}");
		$userid = self::$mc->get(self::$context->getProperty('service.domain')."/authorizedSession/{$id}");
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
		if(is_null(self::$mc)) self::initialize();
		$session_cookie_path = "/";
		if( !is_null(self::$context->getProperty('service.session_cookie_path') )) {
			$session_cookie_path = self::$context->getProperty('service.session_cookie_path');
		}
		if (!is_numeric($userid)) return false;
		if( $userid != SESSION_OPENID_USERID ) { /* OpenID session : -1 */
			$_SESSION['userid'] = $userid;
			$id = session_id();
			if( self::isGuestOpenIDSession($id) ) {
				//$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,0,self::$context->getProperty('service.timeout'));
				$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}",$userid,0,self::$context->getProperty('service.timeout'));
				if ($result) {
					return true;
				}
			}
		}
		if (self::isAuthorized(session_id())) return true;
		for ($i = 0; $i < 3; $i++) {
			$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
			//$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}/{$_SERVER['REMOTE_ADDR']}",$userid,0,self::$context->getProperty('service.timeout'));
			$result = self::$mc->set(self::$context->getProperty('service.domain')."/authorizedSession/{$id}",$userid,0,self::$context->getProperty('service.timeout'));
			
			if ($result) {
				@session_id($id);
				setcookie( self::getName(), $id, 0, $session_cookie_path, self::$context->getProperty('service.session_cookie_domain'));
				return true;
			}
		}
		return false;
	}
}
?>
