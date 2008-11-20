<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Config extends Singleton {
	private $database, $service;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		self::__basicConfigLoader();
	}
	
	private function __basicConfigLoader() {
		global $database, $service;

		$this->settings = array();
		require_once(ROOT.'/library/environment/config.php');	// Loading default configuration
		if (file_exists(ROOT.'/config.php')) require_once(ROOT.'/config.php');	// Override configuration
		// Map port setting.
		if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
			$service['port'] = $_SERVER['SERVER_PORT'];
		
		// Include installation configuration.
		$service['session_cookie_path'] = '/';
		if(!defined('__TEXTCUBE_SETUP__')) @include_once ROOT . '/config.php';
		
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
//		if(isset($service['dbms'])) {
//			if($service['dbms'] == 'mysql' && class_exists('mysqli')) $service['dbms'] = 'mysqli';
//		}
		
		// Debug mode configuration.
		if($service['debugmode'] == true) {
			if(isset($service['dbms'])) {
				switch($service['dbms']) {
					case 'mysqli':         require_once(ROOT. "/library/debug/MySQLi.php"); break;
					case 'mysql': default: require_once(ROOT. "/library/debug/MySQL.php"); break;
				}
			} else requireLibrary("debug/MySQL"); 
		}
		
		// Session cookie patch.
		if(!empty($service['domain']) && strstr( $_SERVER['HTTP_HOST'], $service['domain'] ) ) {
			$service['session_cookie_domain'] = $service['domain'];
		} else {
			$service['session_cookie_domain'] = $_SERVER['HTTP_HOST'];
		}

		$this->database = $database;
		$this->service = $service;
		$this->backend_name = $service['dbms'];
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
}

?>
