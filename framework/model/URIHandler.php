<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class URIError extends Exception {};

final class Model_URIHandler extends Singleton
{
	public $uri, $suri;
	public static $blogid;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		$this->__URIInterpreter();
		$this->context = Model_Context::getInstance();
	}

	public function URIParser() { $this->__URIParser();}
	public function VariableParser() { $this->__URIvariableParser();}

	private function __URIInterpreter() {
		$dispatcher = Dispatcher::getInstance();
		$this->uri = $dispatcher->uri;
	}
	
	private function __URIParser() {
		if(!isset($this->uri)) $this->__URIInterpreter();
		$this->context->useNamespace('service');
		
		$url             = $this->uri['fullpath'];
		$defaultblogid   = Setting::getServiceSetting("defaultBlogId",1,true);
		$this->suri            = array('url' => $url, 'value' => '');
		$this->blogid    = null;
		$this->uri['isStrictBlogURL'] = true;
		$depth           = substr_count($this->context->getProperty('path'), '/');
		if ($depth > 0) {
			if (preg_match('@^((/+[^/]+){' . $depth . '})(.*)$@', $url, $matches))
				$url = $matches[3];
			else
				Respond::NotFoundPage();
		}
		if ($this->context->getProperty('type') == 'single') {
			$this->blogid = $defaultblogid;
		} else {
			if ($this->context->getProperty('type') == 'domain') {	// Domain-based service
				if ($_SERVER['HTTP_HOST'] == $this->context->getProperty('domain')) {
					$this->blogid = $defaultblogid;
				} else {
					$domain = explode('.', $_SERVER['HTTP_HOST'], 2);
					if ($domain[1] == $this->context->getProperty('domain')) {
						$this->blogid = $this->__getBlogIdByName($domain[0]);
						if ($this->blogid === null) 
							$this->blogid = $this->__getBlogIdBySecondaryDomain($_SERVER['HTTP_HOST']);
						} else {
							$this->blogid = $this->__getBlogIdBySecondaryDomain($_SERVER['HTTP_HOST']);
						}
				}
			} else {	// Path-based service
				if ($url == '/') {
					$this->blogid = $defaultblogid;
				} else if (preg_match('@^/+([^/]+)(.*)$@', $url, $matches)) {
					$this->blogid = $this->__getBlogIdByName(strtok($matches[1],'?'));
					if ($this->blogid === null) {
						$this->blogid = $defaultblogid;
						$this->uri['isStrictBlogURL']= false;
					}
					$url = $matches[2];
				} else {
					Respond::NotFoundPage();
				}
			}
			if ($this->blogid === null)
				Respond::NotFoundPage();
		}
		if(isset($this->uri['interfacePath'])) {
			if(strpos($this->uri['interfacePath'],'interface/blog/comment') === 0 ||
				strpos($this->uri['interfacePath'],'interface/blog/trackback') === 0) {
				$depth = substr_count($this->uri['interfacePath'], '/') - 2;
			} else {
				$depth = substr_count($this->uri['interfacePath'], '/') - 1;
			}
		} else {
			$depth = substr_count(ROOT, '/');
		}
		if ($depth > 0) {
			if($this->context->getProperty('fancyURL') === 0 || $this->context->getProperty('fancyURL') === 1) $url = '/'.$this->uri['input']; // Exclude /blog path.
			if (preg_match('@^((/+[^/]+){' . $depth . '})/*(.*)$@', $url, $matches)) {
				$this->suri['directive'] = $matches[1];
				if ($matches[3] !== false) {
					$this->suri['value'] = $matches[3];
				}
			} else {
				Respond::NotFoundPage();
			}
		} else {
			$this->suri['directive'] = '/';
			$this->suri['value'] = ltrim($url, '/');
		}
		if(strpos($this->suri['value'],'?') === 0) $this->suri['value'] = '';
		else $this->suri['value'] = strtok($this->suri['value'], '?');
		$this->suri['directive'] = strtok($this->suri['directive'], '?');
		if (is_numeric($this->suri['value'])) {
			$this->suri['id'] = $this->suri['value'];
		} else {
			$this->suri['value'] = URL::decode(str_replace('index.php','',$this->suri['value']));
			if(is_numeric($isValue = strtok($this->suri['value'],'&'))) $this->suri['id'] = $isValue;
			unset($isValue);
		}

		// Parse page.
		$this->suri['page'] = empty($_POST['page']) ? (empty($_GET['page']) ? true : $_GET['page']) : $_POST['page'];
	}
	
	private function __URIvariableParser() {
		global $suri, $blog, $blogid, $skinSetting, $gCacheStorage;
		$blogid        = $this->blogid;
		$gCacheStorage = new globalCacheStorage; // Initialize global cache

		$suri        = $this->suri;
		$blog        = Setting::getBlogSettingsGlobal($this->blogid);
		$blog['id']  = $this->blogid;
		$skinSetting = Setting::getSkinSettings($this->blogid);

		if(!is_null($this->context->getProperty('service.serviceURL'))) {
			$this->uri['service'] = $this->context->getProperty('service.serviceURL');
		}
		if (!isset($this->uri['service'])) {
			$this->uri['service'] = 'http://' . $this->context->getProperty('service.domain') . (!is_null($this->context->getProperty('service.port')) ? ':' . $this->context->getProperty('service.port') : '') . $this->context->getProperty('service.path');
		}
		$this->context->useNamespace('service');
		switch ($this->context->getProperty('service.type')) {
			case 'domain':
				$this->uri['path'] = $this->context->getProperty('path');
				$blog['primaryBlogURL'] = 'http://' . $blog['name'] . '.' . $this->context->getProperty('domain') . (!is_null($this->context->getProperty('port')) ? ':' . $this->context->getProperty('port') : '') . $this->uri['path'];
				if( !empty($blog['secondaryDomain']) )
					$blog['secondaryBlogURL'] = 'http://' . $blog['secondaryDomain'] . (!is_null($this->context->getProperty('port')) ? ':' . $this->context->getProperty('port') : '') . $this->uri['path'];
				else
					$blog['secondaryBlogURL'] = null;
				if ($blog['defaultDomain']) {
					$this->uri['default'] = $blog['secondaryBlogURL'];
					if ($_SERVER['HTTP_HOST'] == $blog['secondaryDomain'])
						$this->uri['base'] = $this->context->getProperty('path');
					else
						$this->uri['base'] = $this->uri['default'];
				} else {
					$this->uri['default'] = $blog['primaryBlogURL'];
					if ($_SERVER['HTTP_HOST'] == ($blog['name'] . '.' . $this->context->getProperty('domain')))
						$this->uri['base'] = $this->context->getProperty('path');
					else
						$this->uri['base'] = $this->uri['default'];
				}
				break;
			case 'path':
				$this->uri['path'] = $this->context->getProperty('path') . '/' . $blog['name'];
				$blog['primaryBlogURL'] = 'http://' . $this->context->getProperty('domain') . (!is_null($this->context->getProperty('port')) ? ':' . $this->context->getProperty('port') : '') . $this->uri['path'];
				$blog['secondaryBlogURL'] = null;
				$this->uri['default'] = $blog['primaryBlogURL'];
				if ($_SERVER['HTTP_HOST'] == $this->context->getProperty('domain'))
					$this->uri['base'] = $this->context->getProperty('path') . '/' . $blog['name'];
				else
					$this->uri['base'] = $this->uri['default'];
				break;
			case 'single':
			default:
				$this->uri['path'] = $this->context->getProperty('path');
				$blog['primaryBlogURL'] = 'http://' . $this->context->getProperty('domain') . (!is_null($this->context->getProperty('port')) ? ':' . $this->context->getProperty('port') : '') . $this->uri['path'];
				$blog['secondaryBlogURL'] = null;
				$this->uri['default'] = $blog['primaryBlogURL'].($this->__getFancyURLpostfix());
				if ($_SERVER['HTTP_HOST'] == $this->context->getProperty('domain'))
					$this->uri['base'] = $this->context->getProperty('path');
				else
					$this->uri['base'] = $this->uri['default'];
				break;
		}
		$this->uri['host'] = 'http://' . $_SERVER['HTTP_HOST'] . (!is_null($this->context->getProperty('port')) ? ':' . $this->context->getProperty('port') : '');
		$this->uri['blog'] = $this->uri['path'].$this->__getFancyURLpostfix();
		$this->uri['folder'] = rtrim($this->uri['blog'] . $suri['directive'], '/');

		if (defined('__TEXTCUBE_MOBILE__')) {
			$this->uri['blog'] .= '/m';
		} else if (defined('__TEXTCUBE_IPHONE__')) {
			$this->uri['blog'] .= '/i';
		}

		$this->blog = $blog;
		$this->skin = $skinSetting;
		$this->updateContext();
	}
	
	function updateContext($ns = null) {
		$this->context = Model_Context::getInstance();
		if(!is_null($ns)) {
			$info = array($ns);
		} else {
			$info = array('uri','blog','skin','suri');
		}
		foreach ($info as $namespace) {
			if(!empty($this->$namespace) && is_array($this->$namespace)) {
				foreach ($this->$namespace as $key => $value) {
					$this->context->setProperty($key,$value,$namespace);
				}
			}
		}
	}
	private function __getBlogIdByName($name) {
		$query = DBModel::getInstance();
		$query->reset('BlogSettings');
		$query->setQualifier('name','equals','name',true);
		$query->setQualifier('value', 'equals', $name, true);
		return $query->getCell('blogid');
		return false;	
	}

	private function __getBlogIdBySecondaryDomain($domain) {
 		return POD::queryCell("SELECT blogid FROM {$this->context->getProperty('database.prefix')}BlogSettings WHERE name = 'secondaryDomain' AND (value = '$domain' OR  value = '" . (substr($domain, 0, 4) == 'www.' ? substr($domain, 4) : 'www.' . $domain) ."')");	
	}

	private function __getFancyURLpostfix() {	
		switch($this->context->getProperty('service.fancyURL')) {
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
