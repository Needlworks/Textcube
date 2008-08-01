<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Config {
	private static $instance;

	function __construct() {
		$this->settings = array();
		// TODO: load settings
	}

	function __get($name) {
		$val = NULL;
		switch ($name) {
			case 'database':
				break;
			case 'service':
				break;
			default:
				$val = $this->settings[$name];
				break;
		}
		return $val;
	}

	public static function getInstance() {
		if (!isset(self::$instance))
			self::$instance = new Config();
		return self::$instance;
	}
}

?>
