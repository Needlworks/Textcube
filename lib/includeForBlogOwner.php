<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$__requireComponent = array();
$__requireBasics = array(
	'config',				// Basics
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail');
$__requireLibrary = array(
	'functions',
	'database',				// Library
	'locale',
	'auth');
$__requireModel = array(
	'blog.service',			// Models
	'blog.blogSetting',
	'blog.user',
	'blog.category',
	'blog.skin',
	'blog.fx',
	'common.plugin',
	'common.module',
	'common.setting',
	'common.legacysupport');
$__requireView = array(
	'html',					// Views
	'ownerView',
	'paging',
	'view');
$__requireInit = array(
	'initialize',			// Initializing environment.
	'plugins');

foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
foreach($__requireModel as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/model/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}

foreach($__requireView as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/view/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}

foreach($__requireInit as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
header('Content-Type: text/html; charset=utf-8');

requireOwnership();		// Check access control list
require ROOT .'/lib/pageACL.php';
?>
