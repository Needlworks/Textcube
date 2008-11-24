<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/// Singleton implementation.
abstract class Singleton {
	private static $instances = array();

	protected function __construct() {
	}

	final protected static function _getInstance($className) {
		if (!array_key_exists($className, self::$instances)) {
			self::$instances[$className] = new $className();
		}
		return self::$instances[$className];
	}

	/*
	// You should implement this method to the final class. (An example is below.)
	// This is mainly because "late static bindings" is supported after PHP 5.3.

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
	*/
	abstract public static function getInstance();
}
?>
