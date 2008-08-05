<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class FrameworkAutoloader
{
	private static $classPaths = array(
			'Context' => './context.php',
			'Debug' => './debug.php',
			'IModel' => './data/IAdapter.php'
			'DBException' => './data/IAdapter.php',
			'DBConnectionError' => './data/IAdapter.php',
			'DBQueryError' => './data/IAdapter.php',
			'IAdapter' => './data/IModel.php',
			'ICache' => './cache/ICache.php',
			'Entry' => './model/Entry.phpp',
		);

	static function init() {
		$config = Config::getInstance();

		// Set paths for DB classes according to the current backend configuration.
		self::$classPaths['Adapter'] = './data/' . $config->backend_name . '/Adapter.php';
		self::$classPaths['Model'] = './data/' . $config->backend_name . '/Model.php';
	}

	static function autoload($name) {
		if (isset(self::$classPaths[$name]))
			require_once(self::$classPaths[$name]);
		// Because multiple autoload functions can be defined, we don't throw any exception here.
		// If PHP finally fails finding the class, it will say FATAL error.
	}
}

FrameworkAutoloader::init();
spl_autoload_register(array('FrameworkAutoloader', 'autoload'));
?>
