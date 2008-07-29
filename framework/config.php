<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$_configInstance = NULL;

class Config {
	function __construct() {
		$this->settings = array();
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

	static function getInstance() {
		global $_configInstance;
		if ($_configInstance == NULL)
			$_configInstance = new Config();
		return $_configInstance;
	}
}

?>
