<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if($context->getProperty('service.reader') === false) exit;

define('__TEXTCUBE_ADMINPANEL__',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail');
$__requireLibrary = array(		// Library
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
?>
