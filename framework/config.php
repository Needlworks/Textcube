<?php

$_configInstance = NULL;

class Config {
	function __construct() {

	}

	function __get($name) {
	}

	function __set($name) {
	}

	static function getInstance() {
		global $_configInstance;
		if ($_configInstance == NULL)
			$_configInstance = new Config();
		return $_configInstance;
	}
}
