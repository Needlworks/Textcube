<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_Config extends Singleton {
	public $database, $service;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct($id = 'textcube') {
		$this->__basicConfigLoader($id);
	}
	
	private function __basicConfigLoader($id) {
		global $database, $service;	// For Legacy global variable support
		$this->settings = array();
		if (file_exists(ROOT.'/framework/id/load')) $id = trim(file_get_contents(ROOT.'/framework/id/load'));
		require_once(ROOT.'/framework/id/'.$id.'/config.default.php');	// Loading default configuration
		if (file_exists(ROOT.'/config.php')) @include(ROOT.'/config.php');	// Override configuration
		// Map port setting.
		if (@is_numeric($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
			$service['port'] = $_SERVER['SERVER_PORT'];
		
		// Include installation configuration.
		if(!isset($service['session_cookie_path'])) $service['session_cookie_path'] = '/';
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
		if ($service['session_cookie_domain'] == 'localhost')
			$service['session_cookie_domain'] = null;

		$this->database = $database;
		$this->service = $service;
		$this->backend_name = isset($service['dbms']) ? $service['dbms'] : 'mysql';
		$this->updateContext();
	}

	public function updateContext($ns = null) {
		$context = Model_Context::getInstance();
		if(!is_null($ns)) {
			$configs = array($ns);
		} else {
			$context->setProperty('backend_name',$this->backend_name);
			$configs = array('database','service');
		}
		foreach ($configs as $namespace):
			foreach ($this->$namespace as $k => $v):
				$context->setProperty($k,$v,$namespace);
			endforeach;
		endforeach;
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
