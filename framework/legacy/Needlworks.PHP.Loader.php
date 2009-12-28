<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

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
class Autoload_Legacy {
	private static $db = array(
		'POD','DBQuery');
	private static $data = array(
		'Attachment','BlogSetting','BlogStatistics','Category','Comment','CommentNotified',
		'CommentNotifiedSiteInfo','DailyStatistics','DataMaintenance','Feed',
		'Filter','GuestComment','Keyword','Link','LinkCategories','Notice','PluginSetting','Post',
		'RefererLog','RefererStatistics','ServiceSetting','SkinSetting','SubscriptionLog',
		'SubscriptionStatistics','Tag','Trackback','TrackbackLog','UserInfo','UserSetting'
		);
	private static $model = array(
		'Message','Paging','PluginCustomConfig','Statistics','User'
		);
	private static $base = array(
		'Base64Stream','HTTPRequest','OutputWriter','XMLRPC','XMLRPCFault',
		'XMLCustomType','XMLTree','Pop3','CommunicationFeed');
	private static $function = array(
		'Image','Setting','Respond','Misc');
	private static $openid = array(
		'OpenID', 'OpenIDSession', 'OpenIDConsumer');
	private static $control = array(
		'Session','RSS');
	public static function load($name) {
		global $service, $database;
		$name = ucfirst($name);
		if(in_array($name,self::$data)) {
			require_once(ROOT . "/framework/legacy/Textcube.Data.".$name.".php");
		} else if (in_array($name,self::$model)) {
			require_once(ROOT . "/framework/legacy/Textcube.Model.".$name.".php");
		} else if (in_array($name,self::$base)) {
			if(in_array($name, array('XMLRPC','XMLRPCFault','XMLCustomType')))
				 require_once(ROOT . "/framework/legacy/Needlworks.PHP.XMLRPC.php");
			else require_once(ROOT . "/framework/legacy/Needlworks.PHP.".$name.".php");
		} else if (in_array($name,self::$function)) {
			require_once(ROOT . "/framework/legacy/Textcube.Function.".$name.".php");
		} else if (in_array($name,self::$openid)) {
			require_once(ROOT . "/framework/legacy/Textcube.Control.Openid.php");
		} else if (in_array($name,self::$control)) {
			if($name == 'Session' && isset($service['memcached']) && $service['memcached'] == true) 
				require_once(ROOT . "/framework/legacy/Textcube.Control.".$name.".Memcached.php");
			else require_once(ROOT . "/framework/legacy/Textcube.Control.".$name.".php");
		} else if (in_array($name,array('Syndication'))) {
			require_once(ROOT . "/framework/legacy/Eolin.API.Syndication.php");
		} else {
//			if(defined('TCDEBUG')) print "TC: Unregisterred auto load class from legacy repository : $name<br/>\n";
		}
	}
}
spl_autoload_register(array('Autoload_Legacy', 'load'));
?>