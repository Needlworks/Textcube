<?php

$config = NULL;

class Config {
	function __construct() {
	}

	function __get($name) {
	}

	function __set($name) {
	}

	static function getInstance() {
		global $config;
		if ($config == NULL)
			$config = new Config();
		return $config;
	}
}
