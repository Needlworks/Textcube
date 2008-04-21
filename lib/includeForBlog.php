<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$__requireLibrary = array(
	'config',					// Basics
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail',
	'functions',
		'DEBUG : End of basic function loading',
	'database',					// Library
	'locale',
	'auth',
	'blog.skin',
		'DEBUG : End of blog.skin.php',
	'model/blog.service',		// Models
	'model/blog.archive',
	'model/blog.attachment',
	'model/blog.blogSetting',
	'model/blog.category',
	'model/blog.comment',
	'model/blog.entry',
	'model/blog.keyword',
	'model/blog.notice',
	'model/blog.link',
	'model/blog.locative',
	'model/blog.sidebar',
	'model/blog.trackback',
	'model/blog.tag',
	'model/blog.user',
	'model/common.setting',
	'model/common.plugin',
	'model/common.module',
	'model/common.legacysupport',
		'DEBUG : End of model loading',
	'view/html',				// Views
	'view/paging',
	'view/view',
		'DEBUG : End of view loading',
	'initialize',				// Initializing environment.
		'DEBUG : End of initializing',
	'plugins',
		'DEBUG : End of plugin parsing'
	);

require ROOT .'/components/Textcube.Function.Setting.php';	//Setting component
foreach($__requireLibrary as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}

header('Content-Type: text/html; charset=utf-8');

if(!defined('__TEXTCUBE_LOGIN__')) {
	$blogVisibility = setting::getBlogSettingGlobal('visibility',2);
	if($blogVisibility == 0) requireOwnership();
	else if($blogVisibility == 1) requireMembership();
}
?>
