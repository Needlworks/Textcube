<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class FrameworkAutoloader
{
	private static $classInfo = array(
		'auth'        => array('Auth','OpenID',array('Privilege'=>'Auth','Acl'=>'Auth')),
		'cache'       => array('PageCache','ICache'),
		'data'        => array('BlogSetting','BlogStatistics','DailyStatistics',
			'DataMaintenance','Filter','Image','POD','RSS',
			'RefererLog','RefererStatistics','ServiceSetting','Setting',
			'Statistics','SubscriptionLog','SubscriptionStatistics','Syndication',
			'TData','UserInfo','UserSetting',
			array('IAdapter'=>'IModel','DBQueryError'=>'IAdapter',
				'DBConnectionError'=>'IAdapter','DBException'=>'IAdapter')
				),
		'entry'       => array(
			'Attachment','Category','Comment','CommentNotified','CommentNotifiedSiteInfo',
			'Feed','GuestComment','Keyword','Link','Notice','PluginSetting',
			'Post','RemoteResponse','SkinSetting','Tag','Trackback','TrackbackLog'),
	    'environment' => array(
			'Base64Stream','HTTPRequest','OutputWriter','XMLRPC',
			'XMLTree','Pop3',
			array('XMLRPCFault'=>'XMLRPC','XMLCustomType'=>'XMLRPC',
				'Validator'=>'Needlworks.PHP.Core')),
		'plugin'      => array('Misc','PluginCustomConfig'),
		'session'     => array('Session'),
		'skin'        => array('BlogSkin'),
		'view'        => array('BlogView','Paging','Respond'),
		'root'        => array('Context','Debug')
		);
		
	static function init() {
		$config = Config::getInstance();
		// Set paths for DB classes according to the current backend configuration.
		array_push(self::$classInfo['data'],array('IAdapter'=>'data/'.$config->backend_name.'/Adapter.php'));
		array_push(self::$classInfo['data'],array('IModel'=>'data/'.$config->backend_name.'/Model.php'));
	}

	private static $relation = array();
	
	public static function register() {
		foreach (self::$classInfo as $namespace => $classes) {
			if(!empty($classes)) { 
				foreach($classes as $class) {
					if($namespace == 'root') self::$relation[$class] = $class; 
					else if(is_array($class)) {
						foreach($class as $module => $file) self::$relation[$module] = $namespace.'/'.$file;
					} else self::$relation[$class] = $namespace.'/'.$class;
				}
			}
		}	
	}
	public static function autoload($name) {
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
			if(defined('TCDEBUG')) print "Textcube: Unregisterred auto load class: $name<br/>\n";
		} else {
			require_once(ROOT . "/library/".self::$relation[$name].".php");
		}
	}
}
FrameworkAutoloader::init();
spl_autoload_register(array('FrameworkAutoloader', 'autoload'));
?>
