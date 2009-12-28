<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_LegacySupport extends Singleton {
	public $database, $service;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
	
	}

	public function addSupport($parameters) {
		if(!is_array($parameters)) {
			$parameters = array($parameters);
		}
		$context = Model_Context::getInstance();
		foreach ($parameters as $p) {
			switch ($p) {
				case 'URLglobals':
					global $serviceURL, $pathURL, $defaultURL, $baseURL, $pathURL, $hostURL, $folderURL, $blogURL;
					$context->useNamespace('uri');
					$serviceURL = $context->getProperty('service');
					$pathURL    = $context->getProperty('path');
					$defaultURL = $context->getProperty('default');
					$baseURL    = $context->getProperty('base');
					$hostURL    = $context->getProperty('host');
					$folderURL  = $context->getProperty('folder');
					$blogURL    = $context->getProperty('blog');
					$context->useNamespace();
					break;
				case 'globals':
					global $database, $service, $suri;
					$database = $context->getAllFromNamespace('database');
					$service  = $context->getAllFromNamespace('service');
					$suri     = $context->getAllFromNamespace('suri');
					break;
				default:
			}
		}
	}
}

?>
