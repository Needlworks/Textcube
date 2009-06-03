<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Dispatcher {
	private static $instances = array();
	
	public $URLInfo, $interfacePath;
	
	protected function __construct() {
		$this->URIinterpreter();
	}
	
	final protected static function _getInstance($className) {
		if (!array_key_exists($className, self::$instances)) {
			self::$instances[$className] = new $className();
		}
		return self::$instances[$className];
	}
	
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
    }
    
	private function URIinterpreter() {
		global $service;		
		/* Workaround for IIS environment */
		if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
		}
		if (!empty($_SERVER['PRELOAD_CONFIG']) && file_exists('config.php')) require_once ROOT."/config.php";
		// IIS 7.0 and URL Rewrite Module CTP, but non-ASCII URLs are NOT supported.
		if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		} // IIS 5.x/6.0/7.0 and Ionics ISAPI Rewrite Filter
		else if (isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['REQUEST_URI'], 'rewrite.php') !== FALSE) {
			$_SERVER['REQUEST_URI'] = urldecode($_SERVER['HTTP_X_REWRITE_URL']);
		}
		/* Retrieve Access Parameter Information. */
		$URLInfo = array(
			'host'     => $_SERVER['HTTP_HOST'],
			'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]),
			'position' => $_SERVER["SCRIPT_NAME"],
			'root'     => rtrim(str_replace('rewrite.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
			);
		if (strpos($URLInfo['fullpath'],$URLInfo['root']) !== 0)
			$URLInfo['fullpath'] = $URLInfo['root'].substr($URLInfo['fullpath'], strlen($URLInfo['root']) - 1);
		// Workaround for compartibility with fastCGI / Other environment
		$URLInfo['input'] = ltrim(substr($URLInfo['fullpath'],
			strlen($URLInfo['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),'/');
		// Support Tattertools 0.9x legacy address (for upgrade users)
		if (array_key_exists('pl', $_GET) && strval(intval($_GET['pl'])) == $_GET['pl']) { header("Location: ".$URLInfo['root'].$_GET['pl']); exit;}
		$part = strtok($URLInfo['input'], '/');
		if (in_array($part, array('resources','plugins','cache','skin','attach','thumbnail'))) {
			$part = ltrim(rtrim($part == 'thumbnail' ?
				  preg_replace('/thumbnail/', 'cache/thumbnail', $URLInfo['input'], 1) :
				  $URLInfo['input']), '/');
			$part = (($qpos = strpos($part, '?')) !== false) ? substr($part, 0, $qpos) : $part;
			if(file_exists($part)) {
				require_once ROOT.'/library/function/file.php';
				dumpWithEtag($part);
				exit;
			} else {
				header("HTTP/1.0 404 Not Found");exit;
			}
		}
		if (strtok($part, '?') == 'setup.php') {require 'setup.php'; exit;}
		$URLInfo['fragment'] = explode('/',strtok($URLInfo['input'],'?'));
		unset($part);
	
		/* Check the existence of config.php (whether installed or not) */
		if (!file_exists('config.php')) {
			if (file_exists('.htaccess')) {print "<html><body>Remove '.htaccess' file first!</body></html>";exit;}
			header("Location: " . rtrim($_SERVER["REQUEST_URI"],"/") . "/setup.php");
		}
		/* Determine that which interface should be loaded. */
		require_once 'config.php';
		if(defined('__TEXTCUBE_NO_FANCY_URL__')) $service['type'] = 'single';
		switch ($service['type']) {
			case 'path': // For path-based multi blog.
				array_splice($URLInfo['fragment'],0,1); 
				$pathPart = ltrim(rtrim(strtok(strstr($URLInfo['input'],'/'), '?'), '/'), '/');
				break;
			case 'single':
				$pathPart = (strpos($URLInfo['input'],'?') !== 0 ? ltrim(rtrim(strtok($URLInfo['input'], '?'), '/'), '/') : '');
				break;
			case 'domain': default: 
				$pathPart = ltrim(rtrim(strtok($URLInfo['fullpath'], '?'), '/'), '/');
				if(!empty($service['path'])) $pathPart = ltrim($pathPart,$service['path']);
				break;
		}
		$pathPart = strtok($pathPart,'&');
		// Determine interface Type
		if (isset($URLInfo['fragment'][0]) && $URLInfo['fragment'][0] == 'feeder') {
			$URLInfo['interfaceType'] = 'feeder';
		} else if (isset($URLInfo['fragment'][0]) && isset($URLInfo['fragment'][1]) &&
			($URLInfo['fragment'][0] == 'owner') &&
			($URLInfo['fragment'][1] == 'reader' || ($URLInfo['fragment'][1] == 'network' && isset($URLInfo['fragment'][2]) && $URLInfo['fragment'][2] == 'reader'))) {
			$URLInfo['interfaceType'] = 'reader';
		} else if (isset($URLInfo['fragment'][0]) && $URLInfo['fragment'][0] == 'owner') {
			$URLInfo['interfaceType'] = 'owner';
		} else if (isset($URLInfo['fragment'][0])
			&& ($URLInfo['fragment'][0] == 'favicon.ico' || $URLInfo['fragment'][0] == 'index.gif')) {
			$URLInfo['interfaceType'] = 'icon';
		} else {
			$URLInfo['interfaceType'] = 'blog';
		}
		
		/* Load interface. */
		$interfacePath = null;
		if (in_array($pathPart, array('favicon.ico','index.gif'))) {
			$URLInfo['interfacePath'] = $this->interfacePath = 'interface/'.$pathPart.'.php';
			$this->URLInfo = $URLInfo;
		} else {
			if (!empty($URLInfo['fragment'])) {
				if(	in_array($URLInfo['fragment'][0],
					 array('api','archive','attachment','author','category','checkup','cover','cron','entry','feeder','foaf','guestbook','iMazing','keylog','location','locationSuggest','logout','notice','page','plugin','pluginForOwner','search','suggest','sync','tag','ttxml')))
				{
					$pathPart = $URLInfo['fragment'][0];
					$interfacePath = 'interface/blog/'.$pathPart.'.php';
				} else if (in_array($URLInfo['fragment'][0],array('rss','atom')) &&
						$URLInfo['fragment'][1] == 'category') {
					$pathPart = $URLInfo['fragment'][0].'/category';
					$interfacePath = 'interface/'.$pathPart.'/index.php';
				} else if (is_numeric(strtok(end($URLInfo['fragment']), '&'))) {
					$pathPart = count($URLInfo['fragment'])==1 ? null : implode('/', array_slice($URLInfo['fragment'], 0, count($URLInfo['fragment']) - 1));
				}
			}
			if (empty($interfacePath)) $interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
			define('PATH', 'interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
			unset($pathPart);
			if (!file_exists($interfacePath)) { 
				header("HTTP/1.0 404 Not Found");exit;
			}
			$URLInfo['interfacePath'] = $this->interfacePath = $interfacePath;
			$this->URLInfo = $URLInfo;
		}
	}
}
?>
