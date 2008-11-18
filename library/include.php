<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/** initialization process. 
       (humans are lazy... Aren't you?)      */
if(!isset($__requireBasics)) $__requireBasics = array();
if(!isset($__requireLibrary)) $__requireLibrary = array();
if(!isset($__requireComponent)) $__requireComponent = array();
if(!isset($__requireModel)) $__requireModel = array();
if(!isset($__requireView)) $__requireView = array();
if(!isset($__requireInit)) $__requireInit = array();
if(!isset($service)) $service = array();

/** Define binders */
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

/***** Autoload components *****/
class Autoload {
	private static $drivers = array(
		'auth'        => array('Auth','OpenID'),
		'cache'       => array('PageCache'),
		'data'        => array('BlogSetting','BlogStatistics','DailyStatistics',
			'DataMaintenance','Filter','Image','MySQL','MySQLi','POD','RSS',
			'RefererLog','RefererStatistics','ServiceSetting','Setting',
			'Statistics','SubscriptionLog','SubscriptionStatistics','Syndication',
			'TData','UserInfo','UserSetting'),
		'entry'       => array(
			'Attachment','Category','Comment','CommentNotified','CommentNotifiedSiteInfo',
			'Feed','GuestComment','Keyword','Link','Notice','PluginSetting',
			'Post','RemoteResponse','SkinSetting','Tag','Trackback','TrackbackLog'),
	    'environment' => array(
			'Base64Stream','HTTPRequest','OutputWriter','XMLRPC','XMLRPCFault',
			'XMLCustomType','XMLTree','Pop3'),
		'plugin'      => array('Misc','PluginCustomConfig'),
		'session'     => array('Session'),
		'skin'        => array('BlogSkin'),
		'view'        => array('BlogView','Paging','Respond'));
	private static $relation = array();
	public static function register() {
		foreach (self::$drivers as $namespace => $classes) {
			if(!empty($classes)) foreach($classes as $class) {
				self::$relation[$class] = $namespace;
			}
		}	
	}
	public static function load($name) {
		global $service;
		$name = ucfirst($name);
		if(empty(self::$relation)) {self::register();}
		if (in_array($name,array('DBQuery'))) {
			if (!isset($service['dbms'])) $service['dbms'] = 'mysql';
			require_once(ROOT . "/library/data/".$service['dbms']."/Adapter.php");
			require_once(ROOT . "/library/data/Database.php");
		} else if(self::$relation[$name] == 'session' && isset($service['memcached']) && $service['memcached'] == true) {
			require_once(ROOT . "/library/session/Session_Memcached.php");
		} else if(empty(self::$relation[$name])) {
			if(defined('TCDEBUG')) print "TC: Unregisterred auto load class: $name<br/>\n";
		} else {	
			require_once(ROOT . "/library/".self::$relation[$name]."/".$name.".php");
		}

	}
}
spl_autoload_register(array('Autoload', 'load'));

/***** Pre-define basic components *****/
$__coreLibrary = array(
	'environment/Needlworks.PHP.UnifiedEnvironment',
	'environment/Needlworks.PHP.Core',
	'environment/Locale',
	'data/Core',
	'auth/Auth',
	'cache/PageCache');
foreach($__coreLibrary as $lib) {
	require ROOT .'/library/'.$lib.'.php';
} 
/***** Loading code pieces *****/
if(isset($service['codecache']) && ($service['codecache'] == true) && file_exists(ROOT.'/cache/code/'.$codeName)) {
	$codeCacheRead = true;
	require(ROOT.'/cache/code/'.$codeName);
} else {
	$codeCacheRead = false;
	foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	foreach($__requireModel as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/model/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	
	foreach($__requireView as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/view/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	
	foreach($__requireInit as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
}
if(isset($service['codecache'])
		&& $service['codecache'] == true && $codeCacheRead == false) {
	$libCode = new CodeCache();
	$libCode->name = $codeName;
	$libCode->save();
	unset($libCode);
}
?>
