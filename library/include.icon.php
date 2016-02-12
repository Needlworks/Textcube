<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION', true);
define('NO_INITIALIZATION', true);

$context->setProperty('import.library', array(
    'function.file',
    'auth',
    'model.blog.service',
//	'model.common.plugin', // Usually do not require for icons (no events).
    'model.common.setting'));
?>
