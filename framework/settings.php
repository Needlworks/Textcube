<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Config {
	private static $instance;
	private $database, $service;

	function __construct() {
		$this->settings = array();

		// TODO: Temporary implementation: just import from config.php's global variables
		global $database, $service;
		$this->database = $database;
		$this->service = $service;
	}

	function __get($name) {
		$val = NULL;
		switch ($name) {
			case 'database':
				$val = $this->database;
				break;
			case 'service':
				$val = $this->service;
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
