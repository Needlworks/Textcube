<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class URIError extends Exception {};

final class Context extends Singleton
{
	public $URLInfo, $suri;
	public static $blogid, $isStrictURL;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		$this->__URIInterpreter();
	}
	public function URIParser() { $this->__URIParser();}
	public function globalVariableParser() { $this->__globalVariableParser();}
	
	private function __URIInterpreter() {
		global $URLInfo;
		// URI is parsed at rewrite.php. Thus skip it.
		$this->URLInfo = $URLInfo;
	}
	
	private function __URIParser() {
		if(!isset($this->URLInfo)) $this->__URIInterpreter();
		$config = Config::getInstance();
		$url = $this->URLInfo['fullpath'];
		$defaultblogid = Setting::getServiceSetting("defaultBlogId",1);
		$suri            = array('url' => $url, 'value' => '');
		$this->blogid    = null;
		$this->isStrictBlogURL = true;
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
					if ($domain[1] == $config->service['domain']) {
						$this->blogid = $this->__getBlogidByName($domain[0]);
						if ($this->blogid === null) 
							$this->blogid = $this->__getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
						} else {
							$this->blogid = $this->__getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
						}
				}
			} else {
				if ($url == '/') {
					$this->blogid = $defaultblogid;
				} else if (preg_match('@^/+([^/]+)(.*)$@', $url, $matches)) {
					$this->blogid = $this->__getBlogidByName(strtok($matches[1],'?'));
					if ($this->blogid === null) {
						$this->blogid = $defaultblogid;
						$this->isStrictBlogURL = false;
					}
					$url = $matches[2];
				} else {
					Respond::NotFoundPage();
				}
			}
			if ($this->blogid === null)
				Respond::NotFoundPage();
		}
		if(isset($this->URLInfo['interfacePath'])) {
			$depth = substr_count($this->URLInfo['interfacePath'], '/') - 1;
		} else {
			$depth = substr_count(ROOT, '/');
		}
		if ($depth > 0) {
			if($config->service['fancyURL'] === 0 || $config->service['fancyURL'] === 1) $url = '/'.$this->URLInfo['input']; // Exclude /blog path.
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
	
	private function __globalVariableParser() {
		global $serviceURL, $pathURL, $defaultURL, $baseURL, $pathURL, $hostURL, $folderURL, $blogURL;
		global $suri, $blog, $blogid, $skinSetting, $gCacheStorage;
		$blogid = $this->blogid;
		$gCacheStorage = new globalCacheStorage; // Initialize global cache

		$config = Config::getInstance();

		$suri = $this->suri;
		$blog = Setting::getBlogSettingsGlobal($this->blogid);
		$skinSetting = Setting::getSkinSetting($this->blogid);
		
		if(isset($config->service['serviceURL'])) $serviceURL = $config->service['serviceURL'];
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
/*
function getBlogId() {
	global $blogid;
	return $blogid;	
}*/

?>
