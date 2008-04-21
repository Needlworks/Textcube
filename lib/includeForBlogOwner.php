<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$__requireLibrary = array(
	'config',				// Basics
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail',
	'functions',
	'database',				// Library
	'locale',
	'auth',
	'model/blog.service',	// Models
	'model/blog.blogSetting',
	'model/blog.user',
	'model/blog.category',
	'model/blog.skin',
	'model/blog.fx',
	'model/common.plugin',
	'model/common.module',
	'model/common.setting',
	'model/common.legacysupport',
	'view/html',			// Views
	'view/ownerView',
	'view/paging',
	'view/view',
	'initialize',			// Initializing environment.
	'plugins'
	);
foreach($__requireLibrary as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}

header('Content-Type: text/html; charset=utf-8');

requireOwnership();		// Check access control list
require ROOT .'/lib/pageACL.php';
?>
