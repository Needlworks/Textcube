<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if($context->getProperty('service.reader') === false) exit;

define('NO_LOCALE',true);

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
?>
