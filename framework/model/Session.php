<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'SESSION_OPENID_USERID', -1 );

interface ISession {
	public static function open($savePath, $sessionName); 
	public static function close(); 
	public static function getName();
	public static function read($id);
	public static function write($id, $data); 
	public static function destroy($id, $setCookie = false);
	public static function gc($maxLifeTime = false); 
//	static function getAnonymousSession(); 
//	static function newAnonymousSession();
	public static function setSessionAnonymous($currentId); 
	public static function isAuthorized($id); 
	public static function isGuestOpenIDSession($id); 
	public static function set(); 
	public static function authorize($blogid, $userid); 
//	static function query($mode='query',$sql); 
//	static function DBQuery($mode='query',$sql); 
}

if(isset($service['memcached']) && $service['memcached'] == true) {
	require_once ROOT.'/framework/model/Session.Memcached.php';
} else {
	require_once ROOT.'/framework/model/Session.DB.php';
}
?>
