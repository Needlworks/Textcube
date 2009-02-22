<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__NO_ADMINPANEL__',true);
$__requireComponent = array();
$__requireBasics = array(
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
	'blog.response.remote',
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
?>
