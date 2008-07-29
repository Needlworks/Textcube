<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$classPaths = array(
	'Debug' => './debug.php',
	'IModel' => './data/IAdapter.php',
	'IAdapter' => './data/IModel.php',
	'ICache' => './cache/ICache.php',
	'Entry' => './model/Entry.php'
);

$config = Config::getInstance();
// Set paths for DB classes according to the current backend configuration.
$classPaths['Adapter'] = './data/' . $config->backend_name . '/Adapter.php';
$classPaths['Model'] = './data/' . $config->backend_name . '/Model.php';

function __autoload($classname) {
	global $classPaths;
	if (isset($classPaths[$classname])) {
		require_once($classPaths[$classname]);
	}
	// TODO: error handling or find out?
}

?>
