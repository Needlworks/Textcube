<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class URIError extends Exception {};

final class Context extends Singleton
{
	public $accessInfo, $suri;
	public static $blogid;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		$this->__URIInterpreter();
	}
	public function URIParser() { self::__URIParser();}
	public function globalVariableParser() { self::__GValParser();}
	
	private function __URIInterpreter() {
		$config = Config::getInstance();

		// TEMPORARY: copyed from rewrite.php
		// Workaround for IIS environment
		if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
				$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
		}
		// IIS 7.0 and URL Rewrite Module CTP, but non-ASCII URLs are NOT supported.
		if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		} // IIS 5.x/6.0/7.0 and Ionics ISAPI Rewrite Filter
		else if (isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['REQUEST_URI'], 'dispatcher.php') !== FALSE) {
			$_SERVER['REQUEST_URI'] = urldecode($_SERVER['HTTP_X_REWRITE_URL']);
		}
		/* Retrieve Access Parameter Information. */
		$accessInfo = array(
			'host'      => $_SERVER['HTTP_HOST'],
			'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]), // SUGGEST: change the name 'fullpath' to 'fullQuery'
			'position'  => $_SERVER["SCRIPT_NAME"],
			'root'      => rtrim(str_replace('dispatcher.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
			);
		if (strpos($accessInfo['fullpath'],$accessInfo['root']) !== 0)
			$accessInfo['fullpath'] = $accessInfo['root'].substr($accessInfo['fullpath'], strlen($accessInfo['root']) - 1);
		// Workaround for compartibility with fastCGI / Other environment
		$accessInfo['input'] = ltrim(substr($accessInfo['fullpath'],
			strlen($accessInfo['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),'/');
		// DEPRECATE?: Support for Tattertools 0.9x legacy address
		$part = strtok($accessInfo['input'], '/');
		if (in_array($part, array('resources','plugins','cache','skin','attach','thumbnail'))) {
			$part = ltrim(rtrim($part == 'thumbnail' ?
				  preg_replace('/thumbnail/', 'cache/thumbnail', $accessInfo['input'], 1) :
				  $accessInfo['input']), '/');
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
		$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
		if (isset($accessInfo['URLfragment'][0]) && isset($accessInfo['URLfragment'][1])
		   && $accessInfo['URLfragment'][0] == 'owner' && $accessInfo['URLfragment'][1] == 'reader') {
			$accessInfo['interfaceType'] = 'reader';
		} else if (isset($accessInfo['URLfragment'][0]) && $accessInfo['URLfragment'][0] == 'owner') {
			$accessInfo['interfaceType'] = 'owner';
		} else {
			$accessInfo['interfaceType'] = 'blog';
		}
		unset($part);

		/* Determine that which interface should be loaded. */
		if (defined('__TEXTCUBE_NO_FANCY_URL__')) 
			$config->service['type'] = 'single';
		switch ($config->service['type']) {
			case 'path': // For path-based multi blog.
				array_splice($accessInfo['URLfragment'], 0, 1); 
				$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'), '/'), '/');
				break;
			case 'single':
				$pathPart = (strpos($accessInfo['input'],'?') !== 0 ?
					ltrim(rtrim(strtok($accessInfo['input'], '?'), '/'), '/') :
					'');
				break;
			case 'domain': default: 
				$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'), '/'), '/');
				if (!empty($config->service['path']))
					$pathPart = ltrim($pathPart, $config->service['path']);
				break;
		}
		$pathPart = strtok($pathPart,'&');
		/* Load interface. */
		$interfacePath = null;
		if (in_array($pathPart, array('favicon.ico','index.gif'))) {
			$accessInfo['interfacePath'] = 'interface/'.$pathPart.'.php';
			$accessInfo['prehandler'] = TRUE;
		} else {
			$accessInfo['prehandler'] = FALSE;
			if (!empty($accessInfo['URLfragment']) &&
				in_array($accessInfo['URLfragment'][0],
						 array('api','archive','attachment','author','category','checkup','cover','cron','entry','feeder','foaf','guestbook','iMazing','keylog','location','logout','notice','page','plugin','pluginForOwner','search','suggest','sync','tag','ttxml')))
			{
				$pathPart = $accessInfo['URLfragment'][0];
				$interfacePath = 'interface/blog/'.$pathPart.'.php';
			} else if (is_numeric(strtok(end($accessInfo['URLfragment']), '&'))) {
				$pathPart = count($accessInfo['URLfragment'])==1 ?
					null :
					implode('/', array_slice($accessInfo['URLfragment'], 0, count($accessInfo['URLfragment']) - 1));
			}
			if (empty($interfacePath))
				$interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
			define('PATH', 'interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
			unset($pathPart);
			if (!file_exists($interfacePath)) {
				throw new URIError("No such interface");
			}
			$accessInfo['interfacePath'] = $interfacePath;
		}
		// TODO: Add these to debug-mode output
		//echo "<b>\$accessInfo : </b>";
		//var_dump($accessInfo);
		//echo "<br /><b>\$pathPart : </b>";
		//var_dump($pathPart);
		//echo "<br />\n";

		// TODO: Parse $_GET, $_POST, and etc./
		
		/// Validate URI information.
		if(isset($accessInfo)) {
			$basicIV = array(
				'fullpath' => array('string'),
				'interfacePath' => array('string'),
				'input'    => array('string'),
				'position' => array('string'),
				'root'     => array('string'),
				'interfaceType' => array('string'),
				'interfacePath' => array('string'),
				'input'    => array('string', 'mandatory' => false)
			);
			$accessInfo['fullpath'] = urldecode($accessInfo['fullpath']);
			Validator::validateArray($accessInfo, $basicIV);
		}
		$this->accessInfo = $accessInfo;
	}
	
	private function __URIParser() {
		if(!isset($this->accessInfo)) $this->__URIInterpreter();
		$config = Config::getInstance();
		$url = $this->accessInfo['fullpath'];
		$defaultblogid = Setting::getServiceSetting("defaultBlogId",1);
		$suri            = array('url' => $url, 'value' => '');
		$this->blogid          = null;
		$isStrictBlogURL = true;
		$depth           = substr_count($config->service['path'], '/');

		if ($depth > 0) {
			if (preg_match('@^((/+[^/]+){' . $depth . '})(.*)$@', $url, $matches))
				$url = $matches[3];
			else
				Respond::NotFoundPage();
		}
		if ($config->service['type'] == 'single') {
			$this->blogid = $defaultblogid;
		} else {
			if ($config->service['type'] == 'domain') {
				if ($_SERVER['HTTP_HOST'] == $config->service['domain']) {
					$this->blogid = $defaultblogid;
				} else {
					$domain = explode('.', $_SERVER['HTTP_HOST'], 2);
					if ($domain[1] == $service['domain']) {
						$this->blogid = self::__getBlogidByName($domain[0]);
						if ($this->blogid === null) 
							$this->blogid = self::__getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
						} else {
							$this->blogid = self::__getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
						}
				}
			} else {
				if ($url == '/') {
					$this->blogid = $defaultblogid;
				} else if (preg_match('@^/+([^/]+)(.*)$@', $url, $matches)) {
					$this->blogid = self::__getBlogidByName(strtok($matches[1],'?'));
					if ($this->blogid === null) {
						$this->blogid = $defaultblogid;
						$isStrictBlogURL = false;
					}
					$url = $matches[2];
				} else {
					Respond::NotFoundPage();
				}
			}
			if ($this->blogid === null)
				Respond::NotFoundPage();
		}
		if(isset($this->accessInfo['interfacePath'])) {
			$depth = substr_count($this->accessInfo['interfacePath'], '/') - 1;
		} else {
			$depth = substr_count(ROOT, '/');
		}
		if ($depth > 0) {
			if($config->service['fancyURL'] === 0 || $config->service['fancyURL'] === 1) $url = '/'.$self->accessInfo['input']; // Exclude /blog path.
			if (preg_match('@^((/+[^/]+){' . $depth . '})/*(.*)$@', $url, $matches)) {
				$suri['directive'] = $matches[1];
				if ($matches[3] !== false) {
					$suri['value'] = $matches[3];
				}
			} else {
				Respond::NotFoundPage();
			}
		} else {
			$suri['directive'] = '/';
			$suri['value'] = ltrim($url, '/');
		}
		if(strpos($suri['value'],'?') === 0) $suri['value'] = '';
		else $suri['value'] = strtok($suri['value'], '?');
		$suri['directive'] = strtok($suri['directive'], '?');
		if (is_numeric($suri['value'])) {
			$suri['id'] = $suri['value'];
		} else {
			$suri['value'] = URL::decode(str_replace('index.php','',$suri['value']));
			if(is_numeric($isValue = strtok($suri['value'],'&'))) $suri['id'] = $isValue;
			unset($isValue);
		}
		/*
		if( function_exists( 'mb_detect_encoding' ) && function_exists('iconv') ) {
			$encoding = mb_detect_encoding($suri['value'], 'UTF-8,EUC-KR,SHIFT_JIS,EUC-JP,BIG5,EUC-CN,EUC-TW,GBK');
			$suri['value'] = @iconv( $encoding, 'UTF-8', $suri['value'] );
		}*/

		// Parse page.
		$suri['page'] = empty($_POST['page']) ? (empty($_GET['page']) ? true : $_GET['page']) : $_POST['page'];
		$this->suri = $suri;
	}
	
	private function __GValParser() {
		global $serviceURL, $pathURL, $defaultURL, $baseURL, $pathURL, $hostURL, $folderURL, $blogURL;
		global $suri, $blog, $blogid, $skinSetting;
		$config = Config::getInstance();
		
		$suri = $this->suri;
		$blog = Setting::getBlogSettingsGlobal($this->blogid);
		$skinSetting = Setting::getSkinSetting($this->blogid);
		$blogid = $this->blogid;
		
		if (!isset($serviceURL))
			$serviceURL = 'http://' . $config->service['domain'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '') . $config->service['path'];
		switch ($config->service['type']) {
			case 'domain':
				$pathURL = $config->service['path'];
				$blog['primaryBlogURL'] = 'http://' . $blog['name'] . '.' . $config->service['domain'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '') . $pathURL;
				if( !empty($blog['secondaryDomain']) )
					$blog['secondaryBlogURL'] = 'http://' . $blog['secondaryDomain'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '') . $pathURL;
				else
					$blog['secondaryBlogURL'] = null;
				if ($blog['defaultDomain']) {
					$defaultURL = $blog['secondaryBlogURL'];
					if ($_SERVER['HTTP_HOST'] == $blog['secondaryDomain'])
						$baseURL = $config->service['path'];
					else
						$baseURL = $defaultURL;
				} else {
					$defaultURL = $blog['primaryBlogURL'];
					if ($_SERVER['HTTP_HOST'] == ($blog['name'] . '.' . $config->service['domain']))
						$baseURL = $config->service['path'];
					else
						$baseURL = $defaultURL;
				}
				break;
			case 'path':
				$pathURL = $config->service['path'] . '/' . $blog['name'];
				$blog['primaryBlogURL'] = 'http://' . $config->service['domain'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '') . $pathURL;
				$blog['secondaryBlogURL'] = null;
				$defaultURL = $blog['primaryBlogURL'];
				if ($_SERVER['HTTP_HOST'] == $config->service['domain'])
					$baseURL = $config->service['path'] . '/' . $blog['name'];
				else
					$baseURL = $defaultURL;
				break;
			case 'single':
			default:
				$pathURL = $config->service['path'];
				$blog['primaryBlogURL'] = 'http://' . $config->service['domain'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '') . $pathURL;
				$blog['secondaryBlogURL'] = null;
				$defaultURL = $blog['primaryBlogURL'].($this->__getFancyURLpostfix());
				if ($_SERVER['HTTP_HOST'] == $config->service['domain'])
					$baseURL = $config->service['path'];
				else
					$baseURL = $defaultURL;
				break;
		}
		$hostURL = 'http://' . $_SERVER['HTTP_HOST'] . (isset($config->service['port']) ? ':' . $config->service['port'] : '');
		$blogURL = $pathURL.$this->__getFancyURLpostfix();
		$folderURL = rtrim($blogURL . $suri['directive'], '/');
		if (defined('__TEXTCUBE_MOBILE__')) {
			$blogURL .= '/m';
		}else if (defined('__TEXTCUBE_IPHONE__')) {
			$blogURL .= '/i';
		}
	}
	
	private function __getBlogIdByName($name) {
		global $database;
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		$query->setQualifier('name','name',true);
		$query->setQualifier('value', $name, true);
		return $query->getCell('blogid');
		return false;	
	}
	private function __getBlogIdBySecondaryDomain($name) {
		global $database;
 		return POD::queryCell("SELECT blogid FROM {$database['prefix']}BlogSettings WHERE name = 'secondaryDomain' AND (value = '$domain' OR  value = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) ."')");	
	}
	private function __getFancyURLpostfix() {	
		$config = Config::getInstance();
		switch($config->service['fancyURL']) {
			case 0: return '/index.php?';
			case 1: return '/?';
			case 2:default: return '';
		}
	}
	
	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}

/** Support functions */

function getBlogId() {
	global $blogid;
	return $blogid;	
}

?>
