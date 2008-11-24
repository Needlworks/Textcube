<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/** Pre-define basic components */
global $__requireBasics, $__requireComponent, $__requireLibrary, $__requireModel, $__requireView;
$__requireComponent = array(
	'Needlworks.PHP.UnifiedEnvironment',
	'Needlworks.PHP.Core',
	'Needlworks.Core.Locale',
	'Textcube.Core',
	'Textcube.Control.Auth',
	'Needlworks.Cache.PageCache');
foreach($__requireComponent as $lib) {
	require ROOT .'/library/components/'.$lib.'.php';
} 
/***** Loading code pieces *****/
if(isset($service['codecache']) && ($service['codecache'] == true) && file_exists(ROOT.'/cache/code/'.$codeName)) {
	$codeCacheRead = true;
	require(ROOT.'/cache/code/'.$codeName);
} else {
	$codeCacheRead = false;
	foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	foreach($__requireModel as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/model/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
	
	foreach($__requireView as $lib) {
		if(strpos($lib,'DEBUG') === false) require ROOT .'/library/view/'.$lib.'.php';
		else if(defined('TCDEBUG')) __tcSqlLogPoint($lib);
	}
}
if(isset($service['codecache'])
		&& $service['codecache'] == true && $codeCacheRead == false) {
	requireComponent('Needlworks.Cache.PageCache');
	$libCode = new CodeCache();
	$libCode->name = $codeName;
	$libCode->save();
	unset($libCode);
}
?>
