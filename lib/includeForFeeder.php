<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('NO_LOCALE',true);
define('NO_ADMINPANEL',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'config',
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail',
	'functions');
$__requireLibrary = array(		// Library
	'database',
//	'locale',
	'auth');
$__requireModel = array(		// Model
	'blog.service',
	'blog.blogSetting',
//	'blog.user',
	'common.setting',
	'common.plugin',
	'reader.common');
$__requireView = array();
$__requireInit = array(		// Initializing environment.
	'initialize');

if($service['reader'] === false) exit;
foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
foreach($__requireModel as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/model/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}

foreach($__requireInit as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
header('Content-Type: text/html; charset=utf-8');
?>
