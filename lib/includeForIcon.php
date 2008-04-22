<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION',true);
define('NO_INITIALIZATION',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'config',
	'function/file');
$__requireLibrary = array(		// Library
	'database',
	'auth');
$__requireModel = array(		// Model
	'blog.service',
//	'common.plugin', // Usually do not require for icons (no events).
	'common.setting');
$__requireView = array();
$__requireInit = array(		// Initialize
	'initialize');
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
?>
