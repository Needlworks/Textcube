<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION',true);
define('NO_INITIALIZATION',true);

$__requireLibrary = array(
// Basics
	'config',
	'database',
	'auth',
// Models
	'model/blog.service',
//	'model/common.plugin', // Usually do not require for icons (no events).
	'model/common.setting',
// Initialize
	'initialize',
	'function/file'
	);
foreach($__requireLibrary as $lib) {
	if(strpos($lib,'DEBUG') === false) require ROOT .'/lib/'.$lib.'.php';
	else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
}
?>
