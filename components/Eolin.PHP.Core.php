<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class String {
	/*@static@*/
	function endsWith($string, $end) {
		$longer = strlen($string) - strlen($end);
		if ($longer < 0)
			return false;
		return (strcmp(substr($string, $longer), $end) == 0);
	}

	/*@static@*/
	function startsWith($string, $start) {
		return (strncmp($string, $start, strlen($start)) == 0);
	}
}


class UTF8 {
	/*@static@*/
	function validate($str, $truncated = false) {
		$length = strlen($str);
		if ($length == 0)
			return true;
		for ($i = 0; $i < $length; $i++) {
			$high = ord($str{$i});
			if ($high < 0x80) {
				continue;
			} else if ($high <= 0xC1) {
				return false;
			} else if ($high < 0xE0) {
				if (++$i >= $length)
					return $truncated;
				else if (($str{$i} & "\xC0") == "\x80")
					continue;
			} else if ($high < 0xF0) {
				if (++$i >= $length) {
					return $truncated;
				} else if (($str{$i} & "\xC0") == "\x80") {
						if (++$i >= $length)
							return $truncated;
						else if (($str{$i} & "\xC0") == "\x80")
							continue;
					}
			} else if ($high < 0xF5) {
					if (++$i >= $length) {
						return $truncated;
					} else if (($str{$i} & "\xC0") == "\x80") {
							if (++$i >= $length) {
								return $truncated;
							} else if (($str{$i} & "\xC0") == "\x80")  {
									if (++$i >= $length)
										return $truncated;
									else if (($str{$i} & "\xC0") == "\x80")
										continue;
								}
						}
				} // F5~FF is invalid by RFC 3629
			return false;
		}
		return true;
	}

	/*@static@*/
	function correct($str, $broken = '') {
		$corrected = '';
		$strlen = strlen($str);
		for ($i = 0; $i < $strlen; $i++) {
			switch ($str{$i}) {
				case "\x09":
				case "\x0A":
				case "\x0D":
					$corrected .= $str{$i};
					break;
				case "\x7F":
					$corrected .= $broken;
					break;
				default:
					$high = ord($str{$i});
					if ($high < 0x20) { // Special Characters.
						$corrected .= $broken;
					} else if ($high < 0x80) { // 1byte.
						$corrected .= $str{$i};
					} else if ($high <= 0xC1) {
						$corrected .= $broken;
					} else if ($high < 0xE0) { // 2byte.
						if (($i + 1 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80"))
							$corrected .= $broken;
						else
							$corrected .= $str{$i} . $str{$i + 1};
						$i += 1;
					} else if ($high < 0xF0) { // 3byte.
						if (($i + 2 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80") || (($str{$i + 2} & "\xC0") != "\x80"))
							$corrected .= $broken;
						else
							$corrected .= $str{$i} . $str{$i + 1} . $str{$i + 2};
						$i += 2;
					} else if ($high < 0xF5) { // 4byte.
							if (($i + 3 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80") || (($str{$i + 2} & "\xC0") != "\x80") || (($str{$i + 3} & "\xC0") != "\x80"))
								$corrected .= $broken;
							else
								$corrected .= $str{$i} . $str{$i + 1} . $str{$i + 2} . $str{$i + 3};
							$i += 3;
						} else { // F5~FF is invalid by RFC3629.
							$corrected .= $broken;
						}
					break;
			}
		}
		if (preg_match('/&#([0-9]{1,});/', $corrected))
			$corrected = mb_decode_numericentity($corrected, array(0x0, 0x10000, 0, 0xfffff), 'UTF-8');
		return $corrected;
	}

	/*@static@*/
	function bring($str, $encoding = null) {
		global $service;
		return @iconv((isset($encoding) ? $encoding : $service['encoding']), 'UTF-8', $str);
	}

	/*@static@*/
	function convert($str, $encoding = null) {
		global $service;
		return @iconv('UTF-8', (isset($encoding) ? $encoding : $service['encoding']), $str);
	}

	/*@static@*/
	function length($str) {
		$len = strlen($str);
		for ($i = $length = 0; $i < $len; $length++) {
			$high = ord($str{$i});
			if ($high < 0x80)
				$i += 1;
			else if ($high < 0xE0)
				$i += 2;
			else if ($high < 0xF0)
				$i += 3;
			else
				$i += 4;
		}
		return $length;
	}

	/*@static@*/
	function lengthAsEm($str) {
		$len = strlen($str);
		for ($i = $length = 0; $i < $len; ) {
			$high = ord($str{$i});
			if ($high < 0x80) {
				$i += 1;
				$length += 1;
			} else {
				if ($high < 0xE0)
					$i += 2;
				else if ($high < 0xF0)
					$i += 3;
				else
					$i += 4;
				$length += 2;
			}
		}
		return $length;
	}

	function lessenAsEncoding($str, $length = 255, $tail = '...') {
		global $database;
		if(!isset($database['utf8']) || empty($database['utf8']) || $database['utf8'] == true)
			return UTF8::lessen($str, $length, $tail);
		else
			return UTF8::lessenAsByte($str, $length, $tail);
	}

	/*@static@*/
	function lessen($str, $chars, $tail = '...') {
		if (UTF8::length($str) <= $chars)
			$tail = '';
		else
			$chars -= UTF8::length($tail);
		$len = strlen($str);
		for ($i = $adapted = 0; $i < $len; $adapted = $i) {
			$high = ord($str{$i});
			if ($high < 0x80)
				$i += 1;
			else if ($high < 0xE0)
				$i += 2;
			else if ($high < 0xF0)
				$i += 3;
			else
				$i += 4;
			if (--$chars < 0)
				break;
		}
		return trim(substr($str, 0, $adapted)) . $tail;
	}

	/*@static@*/
	function lessenAsByte($str, $bytes, $tail = '...') {
		if (strlen($str) <= $bytes)
			$tail = '';
		else
			$bytes -= strlen($tail);
		$len = strlen($str);
		for ($i = $adapted = 0; $i < $len; $adapted = $i) {
			$high = ord($str{$i});
			if ($high < 0x80)
				$i += 1;
			else if ($high < 0xE0)
				$i += 2;
			else if ($high < 0xF0)
				$i += 3;
			else
				$i += 4;
			if ($i > $bytes)
				break;
		}
		return substr($str, 0, $adapted) . $tail;
	}

	/*@static@*/
	function lessenAsEm($str, $ems, $tail = '...') {
		if (UTF8::lengthAsEm($str) <= $ems)
			$tail = '';
		else
			$ems -= strlen($tail);
		$len = strlen($str);
		for ($i = $adapted = 0; $i < $len; $adapted = $i) {
			$high = ord($str{$i});
			if ($high < 0x80) {
				$i += 1;
				$ems -= 1;
			} else {
				if ($high < 0xE0)
					$i += 2;
				else if ($high < 0xF0)
					$i += 3;
				else
					$i += 4;
				$ems -= 2;
			}
			if ($ems < 0)
				break;
		}
		return trim(substr($str, 0, $adapted)) . $tail;
	}
}


class Validator {
	/**
		Date-Time		::= RFC-1123 (the modification of RFC-822)
		Language Code	::= ISO-639 2-letter
		Country Code	::= ISO-3166 alpha-2 country codes
		Language		::= RFC-1766 language tag & RFC-3066
							The used syntax in RFC-822 EBNF is:
								2*2ALPHA *( "-" 2*2ALPHA )
		Timezone		::= RFC-2822
							The used syntax in RFC-822 EBNF is:
								( "+" / "-" ) 4DIGIT
		E-Mail			::= RFC-2822
							The used syntax is
								addr-spec = local-part "@" domain
								local-part = dot-atom
	**/


	/*@static@*/
	function validate(&$iv) {
		if (isset($iv['GET'])) {
			if (!Validator::validateArray($_GET, $iv['GET']))
				return false;
			foreach (array_keys($_GET) as $key) {
				if (!array_key_exists($key, $iv['GET']))
					unset($_GET[$key]);
			}
		} else {
			$_GET = array();
		}

		if (isset($iv['POST'])) {
			if (!Validator::validateArray($_POST, $iv['POST']))
				return false;
			foreach (array_keys($_POST) as $key) {
				if (!array_key_exists($key, $iv['POST']))
					unset($_POST[$key]);
			}
		} else {
			$_POST = array();
		}

		if (isset($iv['REQUEST'])) {
			if (!Validator::validateArray($_REQUEST, $iv['REQUEST']))
				return false;
			foreach (array_keys($_REQUEST) as $key) {
				if (!array_key_exists($key, $iv['REQUEST']))
					unset($_REQUEST[$key]);
			}
		} else {
			$_REQUEST = array();
		}

		if (isset($iv['SERVER'])) {
			if (!Validator::validateArray($_SERVER, $iv['SERVER']))
				return false;
		}

		if (isset($iv['FILES'])) {
			if (!Validator::validateArray($_FILES, $iv['FILES']))
				return false;
			foreach (array_keys($_FILES) as $key) {
				if (!array_key_exists($key, $iv['FILES']))
					unset($_FILES[$key]);
			}
		} else {
			$_FILES = array();
		}
		return true;
	}

	/*@static@*/
	function validateArray(&$array, &$rules) {
		// Workaround for non Fancy-URL user.
		$cropArray = array();
		foreach($array as $name => $value) {
			$doesHaveRequest = strpos($name,'?');
			if($doesHaveRequest !== false) {$name = substr($name,$doesHaveRequest+1);}
			$cropArray[$name] = $value;
		}
		$array = $cropArray;
		foreach ($rules as $key => $rule) {
			if (!isset($rule[0])) {
				trigger_error("Validator: The type of '$key' is not defined", E_USER_WARNING);
				continue;
			}
			if (isset($array[$key]) && (($rule[0] == 'file') || (strlen($array[$key]) > 0))) {
				$value = &$array[$key];
				if (isset($rule['min']))
					$rule[1] = $rule['min'];
				if (isset($rule['max']))
					$rule[2] = $rule['max'];

				switch ($rule[0]) {
					case 'any':
						if (isset($rule[1]) && (strlen($value) < $rule[1]))
							return false;
						if (isset($rule[2]) && (strlen($value) > $rule[2]))
							return false;
						break;
					case 'bit':
						$array[$key] = Validator::getBit($value);
						break;
					case 'bool':
						$array[$key] = Validator::getBool($value);
						break;
					case 'number':
						if (!Validator::number($value, (isset($rule[1]) ? $rule[1] : null), (isset($rule[2]) ? $rule[2] : null)))
							return false;
						break;
					case 'int':
						if (!Validator::isInteger($value, (isset($rule[1]) ? $rule[1] : -2147483648), (isset($rule[2]) ? $rule[2] : 2147483647)))
							return false;
						break;
					case 'id':
						if (!Validator::id($value, (isset($rule[1]) ? $rule[1] : 1), (isset($rule[2]) ? $rule[2] : 2147483647)))
							return false;
						break;
					case 'url':
					case 'string':
						if (!UTF8::validate($value)) {
							$value = UTF8::bring($value);
							if (!UTF8::validate($value))
								return false;
						}
						$value = $array[$key] = UTF8::correct($value);

						if (isset($rule[1]) && (UTF8::length($value) < $rule[1]))
							return false;
						if (isset($rule[2]) && (UTF8::length($value) > $rule[2]))
							return false;
						break;
					case 'list':
						if (!Validator::isList($value))
							return false;
						break;
					case 'timestamp':
						if (!Validator::timestamp($value))
							return false;
						break;
					case 'period':
						if (!Validator::period($value))
							return false;
						break;
					case 'ip':
						if (!Validator::ip($value))
							return false;
						break;
					case 'domain':
						if (!Validator::domain($value))
							return false;
						break;
					case 'email':
						if (!Validator::email($value))
							return false;
						break;
					case 'language':
						if (!Validator::language($value))
							return false;
						break;
					case 'filename':
						if (!Validator::filename($value))
							return false;
						break;
					case 'directory':
						if (!Validator::directory($value))
							return false;
						break;
					case 'path':
						if (!Validator::path($value))
							return false;
						break;
					case 'file':
						if (!isset($value['name']) || preg_match('@[/\\\\]@', $value['name']))
							return false;
						break;
					default:
						if (is_array($rule[0])) {
							if (!in_array($value, $rule[0]))
								return false;
						} else {
							trigger_error("Validator: The type of '$key' is unknown", E_USER_WARNING);
						}
						break;
				}

				if (isset($rule['check']))
					$rule[5] = $rule['check'];
				if (isset($rule[5])) {
					if (function_exists($rule[5])) {
						if (!call_user_func($rule[5], $value))
							return false;
					} else {
						trigger_error("Validator: The check function of '$key' is not defined", E_USER_WARNING);
					}
				}
			} else {
				if (array_key_exists(3, $rule))
					$array[$key] = $rule[3];
				else if (array_key_exists('default', $rule))
					$array[$key] = $rule['default'];
				else if ((!isset($rule[4]) || $rule[4]) && (!isset($rule['mandatory']) || $rule['mandatory']))
					return false;
			}
		}
		return true;
	}

	/*@static@*/
	function number($value, $min = null, $max = null) {
		if (!is_numeric($value))
			return false;
		if (isset($min) && ($value < $min))
			return false;
		if (isset($max) && ($value > $max))
			return false;
		return true;
	}

	/*@static@*/
	function isInteger($value, $min = -2147483648, $max = 2147483647) {
		if (!preg_match('/^(0|-?[1-9][0-9]{0,9})$/', $value))
			return false;
		if (($value < $min) || ($value > $max))
			return false;
		return true;
	}

	/*@static@*/
	function id($value, $min = 1, $max = 2147483647) {
		return Validator::isInteger($value, $min, $max);
	}

	/*@static@*/
	function isList($value) {
		if (!preg_match('/^[1-9][0-9]{0,9}(,[1-9][0-9]{0,9})*,?$/', $value))
			return false;
		return true;
	}

	/**
	 *	Valid: Jan 1 1971 ~ Dec 31 2037 GMT
	 */
	/*@static@*/
	function timestamp($value) {
		return (Validator::isInteger($value) && ($value >= 31536000) && ($value < 2145916800));
	}

	/*@static@*/
	function period($value, $length = null) {
		if (preg_match('/\\d+/', $value)) {
			if (isset($length) && (strlen($value) != $length))
				return false;
			$year = 0;
			$month = 1;
			$day = 1;
			switch (strlen($value)) {
				case 8:
					$day = substr($value, 6, 2);
				case 6:
					$month = substr($value, 4, 2);
				case 4:
					$year = substr($value, 0, 4);
					return checkdate($month, $day, $year);
			}
		}
		return false;
	}

	/*@static@*/
	function ip($value) {
		return preg_match('/^\\d{1,3}(\\.\\d{1,3}){3}$/', $value);
	}

	/*@static@*/
	function domain($value) {
		return ((strlen($value) <= 64) && preg_match('/^([[:alnum:]]+(-[[:alnum:]]+)*\\.)+[[:alnum:]]+(-[[:alnum:]]+)*$/', $value));
	}

	/*@static@*/
	function email($value) {
		if (strlen($value) > 64)
			return false;
		$parts = explode('@', $value, 2);
		return ((count($parts) == 2) && preg_match('@[\\w!#\-\'*+/=?^`{-~-]+(\\.[\\w!#-\'*+/=?^`{-~-]+)*@', $parts[0]) && Validator::domain($parts[1]));
	}

	/*@static@*/
	function language($value) {
		return preg_match('/^[[:alpha:]]{2}(\-[[:alpha:]]{2})?$/', $value);
	}

	/*@static@*/
	function filename($value) {
		return preg_match('/^\w+(\.\w+)*$/', $value);
	}

	/*@static@*/
	function directory($value) {
		return preg_match('/^[\-\w]+( [\-\w]+)*$/', $value);
	}

	/*@static@*/
	function path($value) {
		return preg_match('/^[\-\w]+( [\-\w]+)*(\/[\-\w]+( [\-\w]+)*)*$/', $value);
	}

	/*@static@*/
	function getBit($value) {
		return (Validator::getBool($value) ? 1 : 0);
	}

	/*@static@*/
	function getBool($value) {
		return (!empty($value) && (!is_string($value) || (strcasecmp('false', $value) && strcasecmp('off', $value) && strcasecmp('no', $value))));
	}

	/*@static@*/
	function escapeXML($string, $escape = true) {
		if (is_null($string))
			return null;
		return ($escape ? htmlspecialchars($string) : str_replace('&amp;', '&', preg_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string)));
	}
}

global $__locale, $__text, $__skinText;

class Locale {
	// Requires $__locale as global variable. (language resource information)
	function get() {
		global $__locale;
		return $__locale['locale'];
	}

	function set($locale) {
		global $__locale, $__text;
		list($common) = explode('-', $locale, 2);
		Locale::refreshLocaleResource($locale);
		if (file_exists($__locale['directory'] . '/' . $locale . '.php')) {
			include($__locale['directory'] . '/' . $locale . '.php');
			$__locale['locale'] = $locale;
			return true;
		} else if (($common != $locale) && file_exists($__locale['directory'] . '/' . $common . '.php')) {
			include($__locale['directory'] . '/' . $common . '.php');
			$__locale['locale'] = $common;
			return true;
		}
		return false;
	}

	function setSkinLocale($locale) {
		global $__locale, $__skinText;
		list($common) = explode('-', $locale, 2);
		Locale::refreshLocaleResource($locale);
		if (file_exists($__locale['directory'] . '/' . $locale . '.php')) {
			$__skinText = Locale::includeLocaleFile($__locale['directory'] . '/' . $locale . '.php');
			return true;
		} else if (($common != $locale) && file_exists($__locale['directory'] . '/' . $common . '.php')) {
			$__skinText = Locale::includeLocaleFile($__locale['directory'] . '/' . $common . '.php');
			return true;
		}
		return false;
	}

	function includeLocaleFile($languageFile) {
		include($languageFile);
		return $__text;
	}

	function refreshLocaleResource($locale) {
		global $__locale;
		// po파일과 php파일의 auto convert 지원을 위한 루틴.
		$lang_php = $__locale['directory'] . '/' . $locale . ".php";
		$lang_po = $__locale['directory'] . '/po/' . $locale . ".po";
		// 두 파일 중 최근에 갱신된 것을 찾는다.
		$time_po = filemtime( $lang_po );
		$time_php = filemtime( $lang_php );
		// po파일이 더 최근에 갱신되었으면 php파일을 갱신한다.
		if($time_po && $time_po > $time_php ) {
			requireComponent('Needlworks.Core.Locale');
			$langConvert = new Po2php;
			$langConvert->open($lang_po);
			$langConvert->save($lang_php);
		}
		return false;
	}

	function setDirectory($directory) {
		global $__locale;
		if (!is_dir($directory))
			return false;
		$__locale['directory'] = $directory;
		return true;
	}

	function setDomain($domain) {
		global $__locale;
		$__locale['domain'] = $domain;
		return true;
	}

	function match($locale) {
		global $__locale;
		if (strcasecmp($locale, $__locale['locale']) == 0)
			return 3;
		else if (strncasecmp($locale, $__locale['locale'], 2) == 0)
			return 2;
		else if (strncasecmp($locale, 'en', 2) == 0)
			return 1;
		return 0;
	}

	function getSupportedLocales() {
		global $__locale;
		$locales = array();
		if ($dir = dir($__locale['directory'])) {
			while (($entry = $dir->read()) !== false) {
				if (!is_file($__locale['directory'] . '/' . $entry))
					continue;
				$locale = substr($entry, 0, strpos($entry, '.'));
				if (empty($locale) || $locale == 'messages')
					continue;
				if ($fp = fopen($__locale['directory'] . '/' . $entry, 'r')) {
					$desc = fgets($fp);
					if (preg_match('/<\?(php)?\s*\/\/\s*(.+)/', $desc, $matches))
						$locales[$locale] = _t(trim($matches[2]));
					else
						$locales[$locale] = $locale;
					fclose($fp);
				}
			}
			$dir->close();
		}
		return $locales;
	}
}

class Timezone {
	/*@static@*/
	function isGMT() {
		return (date('Z') == 0);
	}

	/*@static@*/
	function get() {
		$timezone = getenv('TZ');
		if (empty($timezone))
			$timezone = date('T');
		return (empty($timezone) ? 'UTC' : $timezone);
	}

	/*@static@*/
	function getOffset() {
		return (int)date('Z');
	}

	/*@static@*/
	function getCanonical() {
		return sprintf("%+03d:%02d", intval(Timezone::getOffset() / 3600), abs((Timezone::getOffset() / 60) % 60));
	}

	/*@static@*/
	function getRFC822() {
		if (Timezone::isGMT())
			return 'GMT';
		else
			return sprintf("%+05d", intval(Timezone::getOffset() / 3600) * 100 + ((Timezone::getOffset() / 60) % 60));
	}

	/*@static@*/
	function getISO8601($timezone = null) {
		if (Timezone::isGMT())
			return 'Z';
		else
			return sprintf("%+03d:%02d", intval(Timezone::getOffset() / 3600), abs((Timezone::getOffset() / 60) % 60));
	}

	/*@static@*/
	function set($timezone) {
		if ( isset( $_ENV['OS'] ) && strncmp($_ENV['OS'], 'Windows', 7) == 0)
			$timezone = Timezone::getAlternative($timezone);

		return putenv('TZ=' . $timezone);
	}

	/*@static@*/
	function setOffset($offset) {
		return Timezone::setISO8601(sprintf("%+02d:%02d", floor($offset / 3600), abs(($offset / 60) % 60)));
	}

	/*@static@*/
	function setRFC822($timezone) {
		if (($timezone == 'GMT') || ($timezone == 'UT'))
			return Timezone::set('GMT');
		else if (!is_numeric($timezone) || (strlen($timezone) != 5))
			return false;
		else if ($timezone{0} == '+')
			return Timezone::set('UTC-' . substr($timezone, 1, 2) . ':' . substr($timezone, 3, 2));
		else if ($timezone{0} == '-')
			return Timezone::set('UTC+' . substr($timezone, 1, 2) . ':' . substr($timezone, 3, 2));
		else
			return false;
	}

	/*@static@*/
	function setISO8601($timezone) {
		if ($timezone == 'Z')
			return Timezone::set('GMT');
		if (!preg_match('/^([-+])(\d{1,2})(:)?(\d{2})?$/', $timezone, $matches))
			return false;
		$matches[0] = 'GMT';
		$matches[1] = ($matches[1] == '+' ? '-' : '+');
		if (strlen($matches[2]) == 1)
			$matches[2] = '0' . $matches[2];
		if (empty($matches[3]))
			$matches[3] = ':';
		if (empty($matches[4]))
			$matches[4] = '00';
		return Timezone::set(implode('', $matches));
	}

	/*@static@*/
	function getList() {
		return array(
			_t_noop('Asia/Seoul'),
			_t_noop('Asia/Tokyo'),
			_t_noop('Asia/Shanghai'),
			_t_noop('Asia/Taipei'),
			_t_noop('Asia/Calcutta'),
			_t_noop('Europe/Berlin'),
			_t_noop('Europe/Paris'),
			_t_noop('Europe/London'),
			_t_noop('GMT'),
			_t_noop('America/New_York'),
			_t_noop('America/Chicago'),
			_t_noop('America/Denver'),
			_t_noop('America/Los_Angeles'),
			_t_noop('Australia/Sydney'),
			_t_noop('Australia/Melbourne'),
			_t_noop('Australia/Adelaide'),
			_t_noop('Australia/Darwin'),
			_t_noop('Australia/Perth'),
			);
	}

	/*@static@*/
	function getAlternative($timezone) {
		switch ($timezone) {
			case 'Asia/Seoul':
				return 'KST-9';
			case 'Asia/Tokyo':
				return 'JST-9';
			case 'Asia/Shanghai':
				return 'CST-8';
			case 'Asia/Taipei':
				return 'CST-8';
			case 'Asia/Calcutta':
				return 'UTC-5:30';
			case 'Europe/Berlin':
			case 'Europe/Paris':
				return 'UTC-1CES';
			case 'Europe/London':
				return 'UTC0BST';
			case 'America/New_York':
				return 'EST5EDT';
			case 'America/Chicago':
				return 'CST6CDT';
			case 'America/Denver':
				return 'MST7MDT';
			case 'America/Los_Angeles':
				return 'PST8PDT';
			case 'Australia/Sydney':
			case 'Australia/Melbourne':
				return 'EST-10EDT';
			case 'Australia/Adelaide':
			case 'Australia/Darwin':
				return 'CST-9:30';
			case 'Australia/Perth':
				return 'WST-8';
		}
		return $timezone;
	}
}


class Timestamp {
	/*@static@*/
	function format($format = '%c', $time = null) {
		if (isset($time))
			return strftime(_t($format), $time);
		else
			return strftime(_t($format));
	}

	/*@static@*/
	function formatGMT($format = '%c', $time = null) {
		if (isset($time))
			return gmstrftime(_t($format), $time);
		else
			return gmstrftime(_t($format));
	}

	/*@static@*/
	function format2($time) {
		if (date('Ymd', $time) == date('Ymd'))
			return strftime(_t('%H:%M'), $time);
		else if (date('Y', $time) == date('Y', time()))
			return strftime(_t('%m/%d'), $time);
		else
			return strftime(_t('%Y'), $time);
	}

	/*@static@*/
	function format3($time) {
		if (date('Ymd', $time) == date('Ymd'))
			return strftime(_t('%H:%M:%S'), $time);
		else
			return strftime(_t('%Y/%m/%d'), $time);
	}

	/*@static@*/
	function format5($time = null) {
		return (isset($time) ? strftime(_t('%Y/%m/%d %H:%M'), $time) : strftime(_t('%Y/%m/%d %H:%M')));
	}

	/*@static@*/
	function formatDate($time = null) {
		return (isset($time) ? strftime(_t('%Y/%m/%d'), $time) : strftime(_t('%Y/%m/%d')));
	}

	/*@static@*/
	function formatDate2($time = null) {
		return (isset($time) ? strftime(_t('%Y/%m'), $time) : strftime(_t('%Y/%m')));
	}

	/*@static@*/
	function formatTime($time = null) {
		return (isset($time) ? strftime(_t('%H:%M:%S'), $time) : strftime(_t('%H:%M:%S')));
	}

	/*@static@*/
	function get($format = 'YmdHis', $time = null) {
		return (isset($time) ? date($format, $time) : date($format));
	}

	/*@static@*/
	function getGMT($format = 'YmdHis', $time = null) {
		return (isset($time) ? gmdate($format, $time) : gmdate($format));
	}

	/*@static@*/
	function getDate($time = null) {
		return (isset($time) ? date('Ymd', $time) : date('Ymd'));
	}

	/*@static@*/
	function getYearMonth($time = null) {
		return (isset($time) ? date('Ym', $time) : date('Ym'));
	}

	/*@static@*/
	function getYear($time = null) {
		return (isset($time) ? date('Y', $time) : date('Y'));
	}

	/*@static@*/
	function getTime($time = null) {
		return (isset($time) ? date('His', $time) : date('His'));
	}

	/*@static@*/
	function getRFC1123($time = null) {
		return (isset($time) ? date('r', $time) : date('r'));
	}

	/*@static@*/
	function getRFC1123GMT($time = null) {
		return (isset($time) ? gmdate('D, d M Y H:i:s \G\M\T', $time) : gmdate('D, d M Y H:i:s \G\M\T'));
	}

	/*@static@*/
	function getRFC1036($time = null) {
		return ((isset($time) ? date('l, d-M-Y H:i:s ', $time) : date('l, d-M-Y H:i:s ')) . Timezone::getRFC822());
	}

	/*@static@*/
	function getRFC1036($time = null) {
		return ((isset($time) ? date('l, d-M-Y H:i:s ', $time) : date('l, d-M-Y H:i:s ')) . Timezone::getRFC822());
	}

	/*@static@*/
	function getISO8601($time = null) {
		return ((isset($time) ? date('Y-m-d\TH:i:s', $time) : date('Y-m-d\TH:i:s')) . Timezone::getISO8601());
	}

	/*@static@*/
	function getUNIXtime($time = null) {
		return (isset($time) ? date('U', $time) : date('U'));
	}

}

class Path {
	/*@static@*/
	function getBaseName($path) {
		$pattern = (strncasecmp(PHP_OS, 'WIN', 3) ? '/([^\/]+)[\/]*$/' : '/([^\/\\\\]+)[\/\\\\]*$/');
		if (preg_match($pattern, $path, $matches))
			return $matches[1];
		return '';
	}

	/*@static@*/
	function getExtension($path) {
		if (preg_match('/.{1}(\.[[:alnum:]]+)$/', $path, $matches))
			return strtolower($matches[1]);
		else
			return '';
	}

	/*@static@*/
	function getExtension2($path) {
		if (preg_match('/.{1}(\.[[:alnum:]]+(\.[[:alnum:]]+)?)$/', $path, $matches))
			return strtolower($matches[1]);
		else
			return '';
	}

	/*@static@*/
	function combine($path) {
		$args = func_get_args();
		return implode('/', $args);
	}

	/*@static@*/
	function removeFiles($directory) {
		if (!is_dir($directory))
			return false;
		$dir = dir($directory);
		while (($file = $dir->read()) !== false) {
			if (is_file(Path::combine($directory, $file)))
				unlink(Path::combine($directory, $file));
		}
		return true;
	}
}


class XMLStruct {
	var $struct, $error;

	/* static helper function */

	/*@static@*/
	function getValueByLocale($param)
	{
		if (!is_array($param)) return $param;
		for ($i = 0; $i < count($param); $i++) {
			if( isset($param[$i]['.attributes']['xml:lang'])) {
				$lang = $param[$i]['.attributes']['xml:lang'];
			} else {
				$lang = "";
			}
			switch (Locale::match($lang)) {
				case 3:
					$matched = $param[$i];
					unset($secondBest);
					unset($thirdBest);
					$i = count($param); // for exit loop
					break;
				case 2:
					$secondBest = $param[$i];
					break;
				case 1:
					$thirdBest = $param[$i];
					break;
				case 0:
					if (!isset($thirdBest))
						$thirdBest = $param[$i];
					break;
			}
		}
		if (isset($secondBest)) {
			$matched = $secondBest;
		} else if (isset($thirdBest)) {
			$matched = $thirdBest;
		}

		if (!isset($matched))
			return null;

		if (isset($matched['.value']))
			return $matched['.value'];
		return null;
	}

	function XMLStruct() {
		$this->ns = array();
		$this->baseindex = 0;
	}

	function setXPathBaseIndex($baseindex=1) {
		$this->baseindex = $baseindex;
	}

	function setNameSpacePrefix( $prefix, $url ) {
		$this->ns[$prefix] = $url;
	}

	function expandNS($item) {
		if( !$this->nsenabled ) {
			return $item;
		}
		foreach( $this->ns as $prefix => $url ) {
			if( substr( $item, 0, strlen($prefix) + 1) == "$prefix:" ) {
				return "$url:" . substr( $item, strlen($prefix) + 1 );
			}
		}
		return $item;
	}

	function open($xml, $encoding = null, $nsenabled = false) {
		if (!empty($encoding) && (strtolower($encoding) != 'utf-8') && !UTF8::validate($xml)) {
			if (preg_match('/^<\?xml[^<]*\s+encoding=["\']?([\w-]+)["\']?/', $xml, $matches)) {
				$encoding = $matches[1];
				$xml = preg_replace('/^(<\?xml[^<]*\s+encoding=)["\']?[\w-]+["\']?/', '$1"utf-8"', $xml, 1);
			}
			if (strcasecmp($encoding, 'utf-8')) {
				$xml = UTF8::bring($xml, $encoding);
				if (is_null($xml)) {
					$this->error = XML_ERROR_UNKNOWN_ENCODING;
					return false;
				}
			}
		} else {
			if (substr($xml, 0, 3) == "\xEF\xBB\xBF")
				$xml = substr($xml, 3);
		}
		$this->nsenabled = $nsenabled;
		if( $nsenabled ) {
			$p = xml_parser_create_ns();
		} else {
			$p = xml_parser_create();
		}
		xml_set_object($p, $this);
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($p, 'o', 'c');
		xml_set_character_data_handler($p, 'd');
		xml_set_default_handler($p, 'x');
		$this->struct = array();
		$this->_cursor = &$this->struct;
		$this->_path = array('');
		$this->_cdata = false;
		if (!xml_parse($p, $xml))
			return $this->_error($p);
		unset($this->_cursor);
		unset($this->_cdata);
		if (xml_get_error_code($p) != XML_ERROR_NONE)
			return $this->_error($p);
		xml_parser_free($p);
		return true;
	}

	function openFile($filename, $correct = false) {
		if (!$fp = fopen($filename, 'r'))
			return false;
		$p = xml_parser_create();
		xml_set_object($p, $this);
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($p, 'o', 'c');
		xml_set_character_data_handler($p, 'd');
		xml_set_default_handler($p, 'x');
		$this->struct = array();
		$this->_cursor = &$this->struct;
		$this->_path = array('');
		$this->_cdata = false;
		if ($correct) {
			$remains = '';
			while (!feof($fp)) {
				$chunk = $remains . fread($fp, 10240);
				$remains = '';
				if (strlen($chunk) >= 10240) {
					for ($c = 1; $c <= 4; $c++) {
						switch ($chunk{strlen($chunk) - $c} & "\xC0") {
							case "\x00":
							case "\x40":
								if ($c > 1) {
									$remains = substr($chunk, strlen($chunk) - $c + 1);
									$chunk = substr($chunk, 0, strlen($chunk) - $c + 1);
								}
								$c = 5;
								break;
							case "\xC0":
								$remains = substr($chunk, strlen($chunk) - $c);
								$chunk = substr($chunk, 0, strlen($chunk) - $c);
								$c = 5;
								break;
						}
					}
				}
				if (!xml_parse($p, UTF8::correct($chunk, '?'), false)) {
					fclose($fp);
					return $this->_error($p);
				}
			}
		} else {
			while (!feof($fp)) {
				if (!xml_parse($p, fread($fp, 10240), false)) {
					fclose($fp);
					return $this->_error($p);
				}
			}
		}
		fclose($fp);
		if (!xml_parse($p, '', true))
			return $this->_error($p);
		unset($this->_cursor);
		unset($this->_cdata);
		if (xml_get_error_code($p) != XML_ERROR_NONE)
			return $this->_error($p);
		xml_parser_free($p);
		return true;
	}

	function close() {
	}

	function setStream($path) {
		$this->_streams[$path] = true;
	}

	function setConsumer($consumer) {
		$this->_consumer = $consumer;
	}

	function & selectNode($path, $lang = null) {
		$path = explode('/', $path);
		if (array_shift($path) != '') {
			$null = null;
			return $null;
		}
		$cursor = &$this->struct;

		while (is_array($cursor) && ($step = array_shift($path))) {
			$step = $this->expandNS($step);
			if (!preg_match('/^([^[]+)(\[(\d+|lang\(\))\])?$/', $step, $matches)) {
				$null = null;
				return $null;
			}
			$name = $matches[1];
			if (!isset($cursor[$name][0])) {
				$null = null;
				return $null;
			}

			if (count($matches) != 4) { // Node name only.
				if (isset($cursor[$name][0])) {
					$cursor = &$cursor[$name][0];
				} else {
					$null = null;
					return $null;
				}
			} else if ($matches[3] != 'lang()') { // Position.
				/* see http://dev.textcube.org/ticket/430 */
				$index = $matches[3];
				$index -= $this->baseindex;

				if (isset($cursor[$name][$index])) {
					$cursor = &$cursor[$name][$index];
				} else {
					$null = null;
					return $null;
				}
			} else { // lang() expression.
				for ($i = 0; $i < count($cursor[$name]); $i++) {
					if( isset($cursor[$name][$i]['.attributes']['xml:lang'])) {
						$lang = $cursor[$name][$i]['.attributes']['xml:lang'];
					} else {
						$lang = "";
					}
					switch (Locale::match($lang)) {
						case 3:
							$cursor = &$cursor[$name][$i];
							return $cursor;
						case 2:
							$secondBest = &$cursor[$name][$i];
							break;
						case 1:
							$thirdBest = &$cursor[$name][$i];
							break;
						case 0:
							if (!isset($thirdBest))
								$thirdBest = &$cursor[$name][$i];
							break;
					}
				}
				if (isset($secondBest)) {
					$cursor = &$secondBest;
				} else if (isset($thirdBest)) {
					$cursor = &$thirdBest;
				} else {
					$null = null;
					return $null;
				}
			}
		}
		return $cursor;
	}

	function & selectNodes($path) {
		/*
		if ($path{strlen($path) - 1} == ']') {
			$null = null;
			return $null;
		}
		*/
		$p = explode('/', $path);
		if (array_shift($p) != '') {
			$null = null;
			return $null;
		}
		$c = &$this->struct;

		while ($d = array_shift($p)) {
			$o = 0;
			if ($d{strlen($d) - 1} == ']') {
				@list($d, $o) = explode('[', $d, 2);
				if (is_null($o)) {
					$null = null;
					return $null;
				}
				$o = substr($o, 0, strlen($o) - 1);
				if (!is_numeric($o)) {
					$null = null;
					return $null;
				}

				$o -= $this->baseindex; /* see http://dev.textcube.org/ticket/430 */
			}
			$d = $this->expandNS($d);
			if (empty($p)) {
				if (isset($c[$d])) {
					return $c[$d];
				} else {
					$null = null;
					return $null;
				}
			}
			if (isset($c[$d][$o]))
				$c = &$c[$d][$o];
			else
				break;
		}
		$null = null;
		return $null;
	}

	function doesExist($path) {
		return (!is_null($this->selectNode($path)));
	}

	function getAttribute($path, $name, $default = null) {
		$n = &$this->selectNode($path);
		if ((!is_null($n)) && isset($n['.attributes'][$name]))
			return $n['.attributes'][$name];
		else
			return $default;
	}

	function getValue($path) {
		$n = &$this->selectNode($path);
		return (isset($n['.value']) ? $n['.value'] : null);
	}

	function getNodeCount($path) {
		return count($this->selectNodes($path));
	}

	function o($p, $n, $a) {
		if (!isset($this->_cursor[$n]))
			$this->_cursor[$n] = array();
		if (empty($a))
			$this->_cursor = &$this->_cursor[$n][array_push($this->_cursor[$n], array('.value' => '', '_' => &$this->_cursor)) - 1];
		else
			$this->_cursor = &$this->_cursor[$n][array_push($this->_cursor[$n], array('.attributes' => $a, '.value' => '', '_' => &$this->_cursor)) - 1];
		$this->_cdata = null;
		array_push($this->_path, $n);
		if (isset($this->_streams[implode('/', $this->_path)]))
			$this->_cursor['.stream'] = tmpfile();
	}

	function c($p, $n) {
		if (count($this->_cursor) != (2 + isset($this->_cursor['.attributes'])))
			unset($this->_cursor['.value']);
		else
			$this->_cursor['.value'] = rtrim($this->_cursor['.value']);
		$c = &$this->_cursor;
		$this->_cursor = &$this->_cursor['_'];
		unset($c['_']);
		if (isset($this->_consumer)) {
			if (call_user_func($this->_consumer, implode('/', $this->_path), $c, xml_get_current_line_number($p))) {
				if (count($this->_cursor[$n]) == 1)
					unset($this->_cursor[$n]);
				else
					array_pop($this->_cursor[$n]);
			}
		}
		array_pop($this->_path);
	}

	function d($p, $d) {
		if (count($this->_cursor) != (1 + isset($this->_cursor['.value']) + isset($this->_cursor['.attributes']) + isset($this->_cursor['.stream'])))
			return;
		if (!$this->_cdata) {
			if (isset($this->_cdata))
				$this->_cursor['.value'] = rtrim($this->_cursor['.value']);
			$this->_cdata = true;
			$d = ltrim($d);
		}
		if (strlen($d) == 0)
			return;
		if (empty($this->_cursor['.stream']))
			$this->_cursor['.value'] .= $d;
		else
			fwrite($this->_cursor['.stream'], $d);
	}

	function x($p, $d) {
		if ($d == '<![CDATA[')
			$this->_cdata = true;
		else if (($d == ']]>') && $this->_cdata)
			$this->_cdata = false;
	}

	function _error($p) {
		$this->error = array(
			'code' => xml_get_error_code($p),
			'offset' => xml_get_current_byte_index($p),
			'line' => xml_get_current_line_number($p),
			'column' => xml_get_current_column_number($p)
		);
		xml_parser_free($p);
		return false;
	}
}

class URL {
	function encode($url,$useEncodedURL = true) {
		$postfix = '';
		if(substr($url,strlen($url)-1) == '?') {
			$url = substr($url,0,strlen($url)-1);
			$postfix = '?';
		}
		if ($useEncodedURL == true)
			return str_replace('%2F', '/', rawurlencode($url)).$postfix;
		else
			return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?', '+'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F', '%2B'), $url).$postfix;
	}

	function decode($url,$useEncodedURL = true) {
		if ($useEncodedURL == true)
			return rawurldecode($url);
		else
			return urldecode($url);
	}
}
?>
