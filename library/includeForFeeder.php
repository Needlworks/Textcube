<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('NO_LOCALE',true);

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

$codeName = 'includeForFeeder.php';
require ROOT.'/library/include.php';
header('Content-Type: text/html; charset=utf-8');
?>
