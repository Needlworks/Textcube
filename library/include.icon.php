<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('NO_SESSION',true);
define('NO_INITIALIZATION',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'function/file');
$__requireLibrary = array(		// Library
	'auth');
$__requireModel = array(		// Model
	'blog.service',
//	'common.plugin', // Usually do not require for icons (no events).
	'common.setting');
$__requireView = array();
?>
