<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Locales extends Singleton {
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
	
/// Core Locale-specific methods	
	public function get() {
		return $this->locale;
	}
	
	public function reset() {
		$this->defaultLanguage = null;
		$this->resource = $this->locale = array();
		$this->directory = null;	
	}

	public function set($locale, $domain = 'global') {
		list($common) = explode('-', $locale, 2);
		if(!isset($this->resource)) $this->resource = array();
		if(!isset($this->locale)) $this->locale = array();
		if(!isset($this->directory)) $this->directory = null;
		if (file_exists($this->directory . '/' . $locale .'.php')) { // If locale file exists
			if(!isset($this->resource[$domain])) {
				$this->resource[$domain] = $this->includeLocaleFile($this->directory . '/' . $locale . '.php');
				$this->locale[$domain] = $locale;
			}
			return true;
		} else if (($common != $locale) && file_exists($this->directory . '/' . $common . '.php')) {
			if(!isset($this->resource[$domain])) {
				$this->resource[$domain] = $this->includeLocaleFile($this->directory . '/' . $common . '.php');
				$this->locale[$domain] = $common;
			}
			return true;
		}
		return false;
	}

	private function includeLocaleFile($languageFile) {
		$__text = array();
		include_once($languageFile);
		return $__text;
	}

/// This method need to be refreshed.
/*	public function refreshLocaleResource($locale) {
		// po파일과 php파일의 auto convert 지원을 위한 루틴.
		$lang_php = $this->directory . '/' . $locale . ".php";
		$lang_po = $this->directory . '/po/' . $locale . ".po";
		// 두 파일 중 최근에 갱신된 것을 찾는다.
		$time_po = filemtime( $lang_po );
		$time_php = filemtime( $lang_php );
		// po파일이 더 최근에 갱신되었으면 php파일을 갱신한다.
		if($time_po && $time_po > $time_php ) {
			$langConvert = new Po2php;
			$langConvert->open($lang_po);
			$langConvert->save($lang_php);
		}
		return false;
	}*/

	public function setDirectory($directory) {
		if (!is_dir($directory)) {
			return false;
		}
		$this->directory = $directory;
		return true;
	}
	
	public function setDomain($domain = 'global') {
		$this->domain = $domain;
		return true;
	}
	public function setDefaultLanguage($language) {
		$this->defaultLanguage = $language;
		return true;	
	}
	public function match($locale, $domain = null) {
		if(is_null($domain)) $domain = $this->domain;
		if (strcasecmp($locale, $this->locale[$domain]) == 0)
			return 3;
		else if (strncasecmp($locale, $this->locale[$domain], 2) == 0)
			return 2;
		else if (strncasecmp($locale, 'en', 2) == 0)
			return 1;
		return 0;
	}

	public function getSupportedLocales() {
		$locales = array();
		if (is_dir($this->directory)) {
			foreach(new DirectoryIterator($this->directory) as $locale) {
				if($locale->isDir()) continue;
				if(strpos($locale->getFilename(),'.') === 0) continue;
				
				$entry = strtok($locale->getFilename(),'.');
				if ($fp = fopen($locale->getPathname(), 'r')) {
					$desc = fgets($fp);
					if (preg_match('/<\?(php)?\s*\/\/\s*(.+)/', $desc, $matches)) {
						$locales[$entry] = _t(trim($matches[2]));
					} else {
						$locales[$entry] = $entry;
					}
					fclose($fp);
				}
			}
		}
		return $locales;
	}
}

/// Functions related to Locale object.

function _t_noop($t) {
	/* just for extracting by xgettext */
	return $t;
}

/**
   Direct locale call functions.
 */
function _t($t) {
	$locale = Locales::getInstance();	
	if(isset($locale->resource[$locale->domain]) && isset($locale->resource[$locale->domain][$t])) {
		return $locale->resource[$locale->domain][$t];	
	} else return $t;
}

// Text with parameters.
function _f($t) {
	$t = _t($t);
	if (func_num_args() <= 1)
		return $t;
	for ($i = 1; $i < func_num_args(); $i++) {
		$arg = func_get_arg($i);
		$t = str_replace('%' . $i, $arg, $t);
	}
	return $t;
}

// Function for skin language resource.
// _t() follows the admin panel locale setting, however _text() follows the skin locale setting.
function _text($t) {
	$locale = Locales::getInstance();
	if(isset($locale->resource[$locale->domain]) && isset($locale->resource[$locale->domain][$t])) {
		return $locale->resource[$locale->domain][$t];	
	} else return $t;
}

function _textf($t) {
	$t = _text($t);
	if (func_num_args() <= 1)
		return $t;
	for ($i = 1; $i < func_num_args(); $i++) {
		$arg = func_get_arg($i);
		$t = str_replace('%' . $i, $arg, $t);
	}
	return $t;
}
?>
