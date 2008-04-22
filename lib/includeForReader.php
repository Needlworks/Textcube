<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

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
	'locale',
	'auth');
$__requireModel = array(	
	'model/blog.service',
	'model/blog.blogSetting',
	'model/blog.user',
	'model/blog.fx',
	'model/common.legacysupport',
	'model/common.setting',
	'model/common.plugin',
	'model/reader.common');
$__requireView = array(		// View
	'view/html',
	'view/ownerView',
	'view/paging',
	'view/view');
$__requireInit = array(		// Initializing environment.
	'initialize',
	'plugins');

if($service['reader'] === false) exit;
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
// Check access control list
requireOwnership();
require ROOT.'/lib/pageACL.php';
?>
