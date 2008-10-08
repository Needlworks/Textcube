<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/// Singleton implementation.
class Singleton {
	private static $instance;

	private function __construct() {
	}
	
	public static function getInstance() {
		if (!isset(self::$instance)) {
			$className = get_class($this);
			self::$instance = new $className();
		}
		return self::$instance;
	}
}

?>
