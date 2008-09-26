<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__NO_ADMINPANEL__',true);
$__requireComponent = array();
$__requireBasics = array(
	'config',					// Basics
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail',
	'DEBUG : Basic functions loaded.');
$__requireLibrary = array(
	'functions',
	'database',					// Library
	'locale',
	'auth',
	'blog.skin',
	'DEBUG : Default library loaded.');
$__requireModel = array(
	'blog.service',				// Models
	'blog.archive',
	'blog.attachment',
	'blog.blogSetting',
	'blog.category',
	'blog.comment',
	'blog.entry',
	'blog.keyword',
	'blog.notice',
	'blog.link',
	'blog.locative',
	'blog.sidebar',
	'blog.trackback',
	'blog.tag',
	'blog.user',
	'common.setting',
	'common.plugin',
	'common.module',
	'common.legacysupport',
	'DEBUG : Models loaded.');
$__requireView = array(
	'html',						// Views
	'paging',
	'view',
	'DEBUG : Views loaded.');
$__requireInit = array(
	'initialize',				// Initializing environment.
	'DEBUG : Initialization finished.',
	'plugins',
	'DEBUG : Plugin module loaded.');

$codeName = 'includeForBlog.php';
require ROOT.'/lib/include.php';

header('Content-Type: text/html; charset=utf-8');

if(!defined('__TEXTCUBE_LOGIN__')) {
	$blogVisibility = setting::getBlogSettingGlobal('visibility',2);
	if($blogVisibility == 0) requireOwnership();
	else if($blogVisibility == 1) requireMembership();
}
?>
