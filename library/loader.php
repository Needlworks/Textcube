<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class FrameworkAutoloader
{
	private static $classPaths = array(
			'Context' => './library/context.php',
			'Debug' => './library/debug.php',
			'IModel' => './library/data/IAdapter.php',
			'DBException' => './library/data/IAdapter.php',
			'DBConnectionError' => './library/data/IAdapter.php',
			'DBQueryError' => './library/data/IAdapter.php',
			'IAdapter' => './library/data/IModel.php',
			'ICache' => './library/cache/ICache.php',
			'Entry' => './library/model/Entry.php',
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
