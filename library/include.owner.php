<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$__requireComponent = array();
$__requireBasics = array(
	'function/file',
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail');
$__requireLibrary = array(
	'blog.skin',
	'auth');
$__requireModel = array(
	'blog.service',			// Models
	'blog.blogSetting',
	'blog.user',
	'blog.category',
	'blog.skin',
	'blog.tag',
	'blog.keyword',
	'blog.archive',
	'blog.notice',
	'blog.link',
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
?>
