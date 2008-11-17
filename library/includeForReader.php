<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_ADMINPANEL__',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'environment/config',
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
	'blog.service',
	'blog.blogSetting',
	'blog.user',
	'blog.fx',
	'common.legacysupport',
	'common.setting',
	'common.plugin',
	'reader.common');
$__requireView = array(		// View
	'html',
	'ownerView',
	'paging',
	'view');
$__requireInit = array(		// Initializing environment.
	'initialize',
	'plugins');

if(isset($service['reader']) && $service['reader'] === false) exit;

$codeName = 'includeForReader.php';
require ROOT.'/library/include.php';

header('Content-Type: text/html; charset=utf-8');
// Check access control list
requireOwnership();
require ROOT.'/library/pageACL.php';
?>
