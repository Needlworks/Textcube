<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

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
	'function/mail');
$__requireLibrary = array(
	'functions',
	'database',					// Library
	'locale',
	'auth',
	'blog.skin');
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
	'common.legacysupport');
$__requireView = array(
	'html',						// Views
	'paging',
	'view');
$__requireInit = array(
	'initialize',				// Initializing environment.
	'plugins');

$codeName = 'includeForBlog.php';
require ROOT.'/lib/include.php';

header('Content-Type: text/html; charset=utf-8');

if(!defined('__TEXTCUBE_LOGIN__')) {
	$blogVisibility = setting::getBlogSettingGlobal('visibility',2);
	if($blogVisibility == 0) requireOwnership();
	else if($blogVisibility == 1) requireMembership();
}
?>
