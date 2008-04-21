<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$__requireLibrary = array(
// Basics
	'config',
	'function/string',
	'function/time',
	'function/javascript',
	'function/html',
	'function/xml',
	'function/misc',
	'function/image',
	'function/mail',
	'functions',
// Library
	'database',
	'locale',
	'auth',
// Model
	'model/blog.service',
	'model/blog.blogSetting',
	'model/blog.user',
	'model/blog.fx',
	'model/common.legacysupport',
	'model/common.setting',
	'model/common.plugin',
	'model/reader.common',
// View
	'view/html',
	'view/ownerView',
	'view/paging',
	'view/view',

// Initializing environment.
	'initialize',
	'plugins'
	);

if($service['reader'] === false) exit;
foreach($__requireLibrary as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
header('Content-Type: text/html; charset=utf-8');
// Check access control list
requireOwnership();
require ROOT.'/lib/pageACL.php';
?>
