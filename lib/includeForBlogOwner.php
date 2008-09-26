<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_ADMINPANEL__',true);

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


$codeName = 'includeForBlogOwner.php';
require ROOT.'/lib/include.php';
header('Content-Type: text/html; charset=utf-8');
requireOwnership();		// Check access control list
require ROOT .'/lib/pageACL.php';
?>
