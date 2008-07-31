<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/***** initialization process. 
       (humans are lazy... Aren't you?)      *****/
if(!isset($__requireBasics)) $__requireBasics = array();
if(!isset($__requireLibrary)) $__requireLibrary = array();
if(!isset($__requireComponent)) $__requireComponent = array();
if(!isset($__requireModel)) $__requireModel = array();
if(!isset($__requireView)) $__requireView = array();
if(!isset($__requireInit)) $__requireInit = array();
if(!isset($service)) $service = array();

/***** Define binders *****/
function requireComponent($name) {
	global $__requireComponent;
	//if (!preg_match('/^[a-zA-Z0-9\.]+$/', $name))		return;
	$name = str_replace('Tattertools', 'Textcube',$name); // Legacy routine.
	if(!in_array($name,$__requireComponent)) {
		include_once (ROOT . "/library/components/$name.php");
		array_push($__requireComponent,$name);
	}
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
function __autoload($name) {
	$name = ucfirst($name);
	static $data = array(
		'Attachment','BlogSetting','BlogStatistics','Category','Comment','CommentNotified',
		'CommentNotifiedSiteInfo','DailyStatistics','DataMaintenance','Feed',
		'Filter','GuestComment','Keyword','Link','Notice','PluginSetting','Post',
		'RefererLog','RefererStatistics','ServiceSetting','SkinSetting','SubscriptionLog',
		'SubscriptionStatistics','Tag','Trackback','TrackbackLog','UserInfo','UserSetting'
		);
	static $model = array(
		'Paging','PluginCustomConfig','Statistics','User'
		);
	static $base = array(
		'Base64Stream','HTTPRequest','OutputWriter','XMLRPC','XMLTree');
	static $function = array(
		'Image','Setting','Respond');
	if(in_array($name,$data)) {
		require_once(ROOT . "/library/components/Textcube.Data.".$name.".php");
	} else if (in_array($name,$model)) {
		require_once(ROOT . "/library/components/Textcube.Model.".$name.".php");
	} else if (in_array($name,$base)) {
		require_once(ROOT . "/library/components/Needlworks.PHP.".$name.".php");
	} else if (in_array($name,$function)) {
		require_once(ROOT . "/library/components/Textcube.Function.".$name.".php");
	}
}

/***** Pre-define basic components *****/
global $__requireComponent;
$__requireComponent = array(
	'Needlworks.PHP.UnifiedEnvironment',
	'Needlworks.PHP.Core',
	'Textcube.Core',
	'Textcube.Control.Auth',
	'Needlworks.Cache.PageCache');
foreach($__requireComponent as $lib) {
	require ROOT .'/library/components/'.$lib.'.php';
} 
/***** Loading code pieces *****/
if(isset($service['codecache']) && ($service['codecache'] == true) && file_exists(ROOT.'/cache/code/'.$codeName)) {
	$codeCacheRead = true;
	require(ROOT.'/cache/code/'.$codeName);
} else {
	$codeCacheRead = false;
/*	foreach($__requireComponent as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/components/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}*/
	foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	//requireComponent('Textcube.Function.Setting');
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
	requireComponent('Needlworks.Cache.PageCache');
	$libCode = new CodeCache();
	$libCode->name = $codeName;
	$libCode->save();
	unset($libCode);
}
?>
