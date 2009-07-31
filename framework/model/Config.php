<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Model_Config extends Singleton {
	public $database, $service;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		$this->__basicConfigLoader();
	}
	
	private function __basicConfigLoader() {
		global $database, $service;
		$this->settings = array();
		require_once(ROOT.'/library/config.default.php');	// Loading default configuration
		if (file_exists(ROOT.'/config.php')) @include(ROOT.'/config.php');	// Override configuration
		// Map port setting.
		if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
			$service['port'] = $_SERVER['SERVER_PORT'];
		
		// Include installation configuration.
		$service['session_cookie_path'] = '/';
		if(!defined('__TEXTCUBE_SETUP__')) @include_once ROOT . '/config.php';
		// Set service path.
		if(isset($serviceURL)) $service['serviceURL'] = $serviceURL;
		// Set resource path.
		if($service['externalresources']) {
			if(isset($service['resourceURL']) && !empty($service['resourceURL'])) 
				$service['resourcepath'] = $service['resourceURL'];
			else 
				$service['resourcepath'] = TEXTCUBE_RESOURCE_URL;
		} else {
			$service['resourcepath'] = $service['path'].'/resources';
		}
		
		// Database setting.
		if(isset($service['dbms'])) {
			if($service['dbms'] == 'mysql' && class_exists('mysqli')) $service['dbms'] = 'mysqli';
		}
		
		// Session cookie patch.
		if(!empty($service['domain']) && strstr( $_SERVER['HTTP_HOST'], $service['domain'] ) ) {
			$service['session_cookie_domain'] = $service['domain'];
		} else {
			$service['session_cookie_domain'] = $_SERVER['HTTP_HOST'];
		}

		$this->database = $database;
		$this->service = $service;
		$this->backend_name = isset($service['dbms']) ? $service['dbms'] : 'mysql';
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
	
	public function set($category, $name, $value) {
		$this->$category[$name] = $value;	
	}
}

?>
