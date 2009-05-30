<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/** Binders */
global $__requireBasics, $__requireLibrary, $__requireComponent, $__requireModel, $__requireView;
if(!isset($__requireBasics)) $__requireBasics = array();
if(!isset($__requireLibrary)) $__requireLibrary = array();
if(!isset($__requireComponent)) $__requireComponent = array();
if(!isset($__requireModel)) $__requireModel = array();
if(!isset($__requireView)) $__requireView = array();
if(!isset($service)) $service = array();

function requireComponent($name) {
	return true;
}
function requireModel($name) {
	global $__requireModel;
	if(!in_array($name,$__requireModel)) {
		include_once (ROOT . "/library/model/$name.php");
		array_push($__requireModel,$name);
	}
}
function requireView($name) {
	global $__requireView;
	if(!in_array($name,$__requireView)) {
		include_once (ROOT . "/library/view/$name.php");
		array_push($__requireView,$name);
	}
}
function requireLibrary($name) {
	global $__requireLibrary;
	if(!in_array($name,$__requireLibrary)) {
		include_once (ROOT . "/library/$name.php");
		array_push($__requireLibrary,$name);
	}
}

/** Autoload components */
class Autoload {
	private static $db = array(
		'POD','DBQuery');
	private static $data = array(
		'Attachment','BlogSetting','BlogStatistics','Category','Config','Context','Comment','CommentNotified',
		'CommentNotifiedSiteInfo','DailyStatistics','DataMaintenance','Feed',
		'Filter','GuestComment','Keyword','Link','Notice','PluginSetting','Post',
		'RefererLog','RefererStatistics','ServiceSetting','SkinSetting','SubscriptionLog',
		'SubscriptionStatistics','Tag','Trackback','TrackbackLog','UserInfo','UserSetting'
		);
	private static $model = array(
		'Message','Paging','PluginCustomConfig','Statistics','User'
		);
	private static $base = array(
		'Base64Stream','HTTPRequest','OutputWriter','XMLRPC','XMLRPCFault',
		'XMLCustomType','XMLTree','Pop3');
	private static $function = array(
		'Image','Setting','Respond','Misc');
	private static $openid = array(
		'OpenID', 'OpenIDSession', 'OpenIDConsumer');
	private static $control = array(
		'Session','RSS');
	public static function load($name) {
		global $service;
		$name = ucfirst($name);
		if(in_array($name,self::$data)) {
			require_once(ROOT . "/library/components/Textcube.Data.".$name.".php");
		} else if (in_array($name,self::$model)) {
			require_once(ROOT . "/library/components/Textcube.Model.".$name.".php");
		} else if (in_array($name,self::$base)) {
			if(in_array($name, array('XMLRPC','XMLRPCFault','XMLCustomType')))
				 require_once(ROOT . "/library/components/Needlworks.PHP.XMLRPC.php");
			else require_once(ROOT . "/library/components/Needlworks.PHP.".$name.".php");
		} else if (in_array($name,self::$function)) {
			require_once(ROOT . "/library/components/Textcube.Function.".$name.".php");
		} else if (in_array($name,self::$openid)) {
			require_once(ROOT . "/library/components/Textcube.Control.Openid.php");
		} else if (in_array($name,self::$control)) {
			if($name == 'Session' && isset($service['memcached']) && $service['memcached'] == true) 
				require_once(ROOT . "/library/components/Textcube.Control.".$name.".Memcached.php");
			else require_once(ROOT . "/library/components/Textcube.Control.".$name.".php");
		} else if (in_array($name,array('POD'))) {
			require_once(ROOT . "/library/components/POD.Core.Legacy.php");
		} else if (in_array($name,array('DBQuery'))) {
			if (!isset($service['dbms'])) $service['dbms'] = 'mysql';
			switch(strtolower($service['dbms'])) {
				case 'postgresql':
					require_once(ROOT . '/library/components/Needlworks.DBMS.PostgreSQL.php'); break;
				case 'mysqli':
					require_once(ROOT . '/library/components/Needlworks.DBMS.MySQLi.php');     break;
				case 'mysql':
				default:
					require_once(ROOT . '/library/components/Needlworks.DBMS.MySQL.php');     break;
			}
			require_once(ROOT . "/library/components/Needlworks.Database.php");
		} else if (in_array($name,array('Syndication'))) {
			require_once(ROOT . "/library/components/Eolin.API.Syndication.php");
		}
		else {
			if(defined('TCDEBUG')) print "TC: Unregisterred auto load class: $name<br/>\n";
		}
	}
}
spl_autoload_register(array('Autoload', 'load'));
?>
