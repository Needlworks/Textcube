<?php

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
	
	/*@static@*/
	function lessen($str, $chars, $tail = '..') {
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
	function lessenAsByte($str, $bytes, $tail = '..') {
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
	function lessenAsEm($str, $ems, $tail = '..') {
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
		if ($string === null)
			return null;
		return ($escape ? htmlspecialchars($string) : str_replace('&amp;', '&', preg_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string)));
	}
}


class Locale {
	function get() {
		global $__locale;
		return $__locale['locale'];
	}
	
	function set($locale) {
		global $__locale, $__text;
		list($common) = explode('-', $locale, 2);
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
				if (empty($locale))
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

$__locale = array(
	'locale' => null,
	'directory' => './locale',
	'domain' => null,
);

function _t($t) {
	global $__locale, $__text;
	if (isset($__locale['domain']) && isset($__text[$__locale['domain']][$t]))
		return $__text[$__locale['domain']][$t];
	else if (isset($__text[$t]))
		return $__text[$t];
	return $t;
}

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
		if (@strncmp($_ENV['OS'], 'Windows', 7) == 0)
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
			'Asia/Seoul',
			'Asia/Tokyo',
			'Asia/Shanghai',
			'Asia/Taipei',
			'Europe/Berlin',
			'Europe/Paris',
			'Europe/London',
			'GMT',
			'America/New_York',
			'America/Chicago',
			'America/Denver',
			'America/Los_Angeles',
			'Australia/Sydney',
			'Australia/Melbourne',
			'Australia/Adelaide',
			'Australia/Darwin',
			'Australia/Perth',
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
	function getISO8601($time = null) {
		return ((isset($time) ? date('Y-m-d\TH:i:s', $time) : date('Y-m-d\TH:i:s')) . Timezone::getISO8601());
	}
}


class DBQuery {	
	/*@static@*/ 
	function queryExistence($query) {
		if ($result = mysql_query($query)) {
			if (mysql_num_rows($result) > 0) {
				mysql_free_result($result);
				return true;
			}
			mysql_free_result($result);
		}
		return false;
	}
	
	/*@static@*/
	function queryCount($query) {
		$count = 0;
		if ($result = mysql_query($query)) {
			$count = mysql_num_rows($result);
			mysql_free_result($result);
		}
		return $count;
	}
	
	/*@static@*/
	function queryCell($query, $field = 0) {
		if ($result = mysql_query($query)) {
			if (is_numeric($field)) {
				$row = mysql_fetch_row($result);
				$cell = @$row[$field];
			} else {
				$row = mysql_fetch_assoc($result);
				$cell = @$row[$field];
			}
			mysql_free_result($result);
			return $cell;
		}
		return null;
	}
	
	/*@static@*/
	function queryRow($query, $type = MYSQL_BOTH) {
		if ($result = mysql_query($query)) {
			if ($row = mysql_fetch_array($result, $type)) {
				mysql_free_result($result);
				return $row;
			}
			mysql_free_result($result);
		}
		return null;
	}

	/*@static@*/
	function queryColumn($query) {
		$column = array();
		if ($result = mysql_query($query)) {
			while ($row = mysql_fetch_row($result))
				array_push($column, $row[0]);
			mysql_free_result($result);
			return $column;
		}
		return null;
	}

	/*@static@*/
	function queryAll($query, $type = MYSQL_BOTH) {
		$all = array();
		if ($result = mysql_query($query)) {
			while ($row = mysql_fetch_array($result, $type))
				array_push($all, $row);
			mysql_free_result($result);
			return $all;
		}
		return null;
	}

	/*@static@*/
	function execute($query) {
		return mysql_query($query) ? true : false;
	}
}


class TableQuery {
	function TableQuery($table = null) {
		$this->reset($table);
	}
	
	function reset($table = null) {
		$this->table = $table;
		$this->id = null;
		$this->_attributes = array();
		$this->_qualifiers = array();
	}
	
	function resetAttributes() {
		$this->_attributes = array();
	}
	
	function getAttributesCount() {
		return count($this->_attributes);
	}
	
	function hasAttribute($name) {
		return isset($this->_attributes[$name]);
	}
	
	function getAttribute($name) {
		return $this->_attributes[$name];
	}

	function setAttribute($name, $value, $escape = null) {
		if ($value === null)
			$this->_attributes[$name] = 'NULL';
		else
			$this->_attributes[$name] = ($escape === null ? $value : ($escape ? '\'' . mysql_real_escape_string($value) . '\'' : "'" . $value . "'"));
	}
	
	function unsetAttribute($name) {
		unset($this->_attributes[$name]);
	}
	
	function resetQualifiers() {
		$this->_qualifiers = array();
	}
	
	function getQualifiersCount() {
		return count($this->_qualifiers);
	}
	
	function hasQualifier($name) {
		return isset($this->_qualifiers[$name]);
	}
	
	function getQualifier($name) {
		return $this->_qualifiers[$name];
	}

	function setQualifier($name, $value, $escape = null) {
		if ($value === null)
			$this->_qualifiers[$name] = 'NULL';
		else
			$this->_qualifiers[$name] = ($escape === null ? $value : ($escape ? '\'' . mysql_real_escape_string($value) . '\'' : "'" . $value . "'"));
	}
	
	function unsetQualifier($name) {
		unset($this->_qualifiers[$name]);
	}
	
	function doesExist() {
		return DBQuery::queryExistence('SELECT * FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getCell($field = '*') {
		return DBQuery::queryCell('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getRow($field = '*') {
		return DBQuery::queryRow('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function getColumn($field = '*') {
		return DBQuery::queryColumn('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause() . ' LIMIT 1');
	}
	
	function getAll($field = '*') {
		return DBQuery::queryAll('SELECT ' . $field . ' FROM ' . $this->table . $this->_makeWhereClause());
	}
	
	function insert() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'INSERT INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (mysql_query($this->_query)) {
			$this->id = mysql_insert_id();
			return true;
		}
		return false;
	}
	
	function update() {
		if (empty($this->table) || empty($this->_attributes))
			return false;
		$attributes = array();
		foreach ($this->_attributes as $name => $value)
			array_push($attributes, $name . '=' . $value);
		$this->_query = 'UPDATE ' . $this->table . ' SET ' . implode(',', $attributes) . $this->_makeWhereClause();
		if (mysql_query($this->_query))
			return true;
		return false;
	}

	function replace() {
		$this->id = null;
		if (empty($this->table))
			return false;
		$attributes = array_merge($this->_qualifiers, $this->_attributes);
		if (empty($attributes))
			return false;
		$this->_query = 'REPLACE INTO ' . $this->table . '(' . implode(',', array_keys($attributes)) . ') VALUES(' . implode(',', $attributes) . ')';
		if (mysql_query($this->_query)) {
			$this->id = mysql_insert_id();
			return true;
		}
		return false;
	}
	
	function delete() {
		if (empty($this->table))
			return false;
		$this->_query = 'DELETE FROM ' . $this->table . $this->_makeWhereClause();
		if (mysql_query($this->_query))
			return true;
		return false;
	}
	
	function _makeWhereClause() {
		$clause = '';
		foreach ($this->_qualifiers as $name => $value)
			$clause .= (strlen($clause) ? ' AND ' : '') . $name . '=' . $value;
		return (strlen($clause) ? ' WHERE ' . $clause : '');
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
		if (!$dir = dir($directory))
			return false;
		while ($file = $dir->read()) {
			if (is_file(Path::combine($directory, $file)))
				unlink(Path::combine($directory, $file));
		}
		return true;
	}
}


class XMLStruct {
	var $struct, $error;
	
	function XMLStruct() {
	}
	
	function open($xml, $encoding = null) {
		if (!empty($encoding) && (strtolower($encoding) != 'utf-8') && !UTF8::validate($xml)) {
			if (preg_match('/^<\?xml[^<]*\s+encoding=["\']?([\w-]+)["\']?/', $xml, $matches)) {
				$encoding = $matches[1];
				$xml = preg_replace('/^(<\?xml[^<]*\s+encoding=)["\']?[\w-]+["\']?/', '$1"utf-8"', $xml, 1);
			}
			if (strcasecmp($encoding, 'utf-8')) {
				$xml = UTF8::bring($xml, $encoding);
				if ($xml === null) {
					$this->error = XML_ERROR_UNKNOWN_ENCODING;
					return false;
				}
			}
		} else {
			if (substr($xml, 0, 3) == "\xEF\xBB\xBF")
				$xml = substr($xml, 3);
		}
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
				if (isset($cursor[$name][$matches[3]])) {
					$cursor = &$cursor[$name][$matches[3]];
				} else {
					$null = null;
					return $null;
				}
			} else { // lang() expression.
				for ($i = 0; $i < count($cursor[$name]); $i++) {
					switch (Locale::match(@$cursor[$name][$i]['.attributes']['xml:lang'])) {
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
		if ($path{strlen($path) - 1} == ']') {
			$null = null;
			return $null;
		}
		$p = explode('/', $path);
		if (array_shift($p) != '') {
			$null = null;
			return $null;
		}
		$c = &$this->struct;
		
		while ($d = array_shift($p)) {
			$o = 0;
			if ($d{strlen($d) - 1} == ']') {
				@list($d, $o) = split('\[', $d, 2);
				if ($o === null) {
					$null = null;
					return $null;
				}
				$o = substr($o, 0, strlen($o) - 1);
				if (!is_numeric($o)) {
					$null = null;
					return $null;
				}
			}
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
		return ($this->selectNode($path) !== null);
	}
	
	function getAttribute($path, $name, $default = null) {
		$n = &$this->selectNode($path);
		if (($n !== null) && isset($n['.attributes'][$name]))
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

class Image {
	function Image() {
		$this->extraPadding = 0;
		$this->imageFile = 
		$this->resultImageDevice = 
			NULL;
	}
	
	function resample($width, $height, $padding=NULL) {
		if (empty($width) && empty($height))
			return false;
		if (empty($this->imageFile) || !file_exists($this->imageFile))
			return false;
		
		// validate padding values.
		if (!is_null($padding)) {
			if (!isset($padding['top']) || !is_int($padding['top'])) {
				$padding['top'] = 0;
			}
			if (!isset($padding['right']) || !is_int($padding['right'])) {
				$padding['right'] = 0;
			}
			if (!isset($padding['bottom']) || !is_int($padding['bottom'])) {
				$padding['bottom'] = 0;
			}
			if (!isset($padding['left']) || !is_int($padding['left'])) {
				$padding['left'] = 0;
			}
		} else {
			$padding['top'] = 0;
			$padding['right'] = 0;
			$padding['bottom'] = 0;
			$padding['left'] = 0;
		}
		
		// validate background color.
		if (isset($padding['bgColor'])) {
			if (!eregi("^[0-9A-F]{3,6}$", $padding['bgColor']))
				$padding['bgColor'] = "FFFFFF";
		} else {
			$padding['bgColor'] = "FFFFFF";
		}
		
		// |---------------------------- $width ---------------------------|
		// |-- $padding['left'] --|-- $coreWidth --|-- $padding['right'] --|
		$coreWidth = $width - $padding['left'] - $padding['right'];
		$coreHeight = $height - $padding['top'] - $padding['bottom'];
		if ($coreWidth < 0)
			$coreWidth = 0;
		if ($coreHeight < 0)
			$coreHeight = 0;
		
		// create an image device as image format.
		switch ($this->getImageType($this->imageFile)) {
			case "gif":
				if (imagetypes() & IMG_GIF) {
					$originImageDevice = imagecreatefromgif($this->imageFile);
				} else {
					return false;
				}
				break;
			case "jpg":
				if (imagetypes() & IMG_JPG) {
					$originImageDevice = imagecreatefromjpeg($this->imageFile);
				} else {
					return false;
				}
				break;
			case "png":
				if (imagetypes() & IMG_PNG) {
					$originImageDevice = imagecreatefrompng($this->imageFile);
				} else {
					return false;
				}
				break;
			case "wbmp":
				if (imagetypes() & IMG_WBMP) {
					$originImageDevice = imagecreatefromwbmp($this->imageFile);
				} else {
					return false;
				}
				break;
			case "xpm":
				if (imagetypes() & IMG_XPM) {
					$originImageDevice = imagecreatefromxpm($this->imageFile);
				} else {
					return false;
				}
				break;
			default:
				return false;
				break;
		}
		
		if (Path::getExtension($this->imageFile) == ".gif") {
			$this->resultImageDevice = imagecreate($width, $height);
		} else {
			$this->resultImageDevice = imagecreatetruecolor($width, $height);
		}
		
		$bgColorBy16 = $this->hexRGB($padding['bgColor']);
		$temp = imagecolorallocate($this->resultImageDevice, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
		imagefilledrectangle($this->resultImageDevice, 0, 0, $width, $height, $temp);
		imagecopyresampled($this->resultImageDevice, $originImageDevice, $padding['left'], $padding['top'], 0, 0, $coreWidth, $coreHeight, imagesx($originImageDevice), imagesy($originImageDevice));
		imagedestroy($originImageDevice);
		
		return true;
	}
	
	function impressWaterMark($waterMarkFile, $position="left=10|bottom=10", $gamma=100) {
		if ($this->getImageType($waterMarkFile) == "png")
			return $this->_impressWaterMarkCore("PNG", $waterMarkFile, $position);
		else
			return $this->_impressWaterMarkCore("GIF", $waterMarkFile, $position, $gamma);
	}
	
	function _impressWaterMarkCore($type, $waterMarkFile, $position, $gamma=100) {
		if (empty($waterMarkFile))
			return false;
		if (!file_exists($waterMarkFile))
			return false;
		if (empty($this->resultImageDevice))
			return false;
		
		// validate gamma.
		if (!is_int($gamma)) {
			$gamma = 100;
		} else if ($gamma < 0) {
			$gamma = 0;
		} else if ($gamma > 100) {
			$gamma = 100;
		}
		
		list($waterMarkWidth, $waterMarkHeight, $waterMakrType) = getimagesize($waterMarkFile);
		
		// position of watermark.
		if (eregi("^(\-?[0-9A-Z]+) (\-?[0-9A-Z]+)$", $position, $temp)) {
			$resultWidth = imagesx($this->resultImageDevice);
			$resultHeight = imagesy($this->resultImageDevice);
			
			switch ($temp[1]) {
				case "left":
					$xPosition = $this->extraPadding;
					break;
				case "center":
					$xPosition = $resultWidth / 2 - $waterMarkWidth / 2;
					break;
				case "right":
					$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
					break;
				default:
					// if positive, calculate x value from left.
					if (eregi("^([1-9][0-9]*)$", $temp[1], $extra)) {
						if ($extra[1] > $resultWidth - $waterMarkWidth) {
							$xPosition = $resultWidth - $waterMarkWidth;
						} else {
							$xPosition = $extra[1];
						}
					// if negative, calculate x value from right.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[1], $extra)) {
						if ($resultWidth - $waterMarkWidth - abs($extra[1]) < 0) {
							$xPosition = 0;
						} else {
							$xPosition = $resultWidth - $waterMarkWidth - abs($extra[1]);
						}
					// in the case of 0.
					} else if ($temp[1] == "0") {
						$xPosition = 0;
					// the others. calculate x value from left.
					} else {
						$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
					}
			}
			
			switch ($temp[2]) {
				case "top":
					$yPosition = $this->extraPadding;
					break;
				case "middle":
					$yPosition = $resultHeight / 2 - $waterMarkHeight / 2;
					break;
				case "bottom":
					$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
					break;
				default:
					// if positive, calculate y value from top.
					if (eregi("^([1-9][0-9]*)$", $temp[2], $extra)) {
						if ($extra[1] > $resultHeight - $waterMarkHeight) {
							$yPosition = $resultHeight - $waterMarkHeight;
						} else {
							$yPosition = $extra[1];
						}
					// if negative, calculate y value from bottom.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[2], $extra)) {
						if ($resultHeight - $waterMarkHeight - abs($extra[1]) < 0) {
							$yPosition = 0;
						} else {
							$yPosition = $resultHeight - $waterMarkHeight - abs($extra[1]);
						}
					// in the case of 0.
					} else if ($temp[1] == "0") {
						$yPosition = 0;
					// the others. calculate y value from bottom.
					} else {
						$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
					}
			}
		} else {
			$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
			$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
		}
		
		// create watermark image device.
		switch ($waterMakrType) {
			case 1:
				$waterMarkDevice = imagecreatefromgif($waterMarkFile);
				break;
			case 2:
				$waterMarkDevice = imagecreatefromjpeg($waterMarkFile);
				break;
			case 3:
				$waterMarkDevice = imagecreatefrompng($waterMarkFile);
				break;
		}
		
		// PHP >= 4.0.6
		if (strtolower($type) == "png" && function_exists("imagealphablending")) {
			imagealphablending($this->resultImageDevice, true);
			imagecopy($this->resultImageDevice, $waterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight);
		} else {
			// if not support alpha channel, support GIF transparency.
			$tempWaterMarkDevice = imagecreatetruecolor($waterMarkWidth, $waterMarkHeight);
			
			$bgColorBy16 = $this->hexRGB("FF00FF");
			$temp = imagecolorallocate($tempWaterMarkDevice, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
			imagecolortransparent($this->resultImageDevice, $temp);
			imagefill($tempWaterMarkDevice, 0, 0, $temp);
			imagecopy($tempWaterMarkDevice, $waterMarkDevice, 0, 0, 0, 0, $waterMarkWidth, $waterMarkHeight);
			
			if (function_exists("imagecopymerge"))
				imagecopymerge($this->resultImageDevice, $tempWaterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight, $gamma);
			else
				imagecopy($this->resultImageDevice, $tempWaterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight);
			
			imagedestroy($tempWaterMarkDevice);
		}
		
		imagedestroy($waterMarkDevice);
		return true;
	}
	
	function createThumbnailIntoFile($fileName) {
		if (empty($this->resultImageDevice))
			return false;
		
		imageinterlace($this->resultImageDevice);
		switch ($this->getImageType($this->imageFile)) {
			case "gif":
				imagegif($this->resultImageDevice, $fileName);
				break;
			case "png":
				imagepng($this->resultImageDevice, $fileName);
				break;
			case "wbmp":
				imagewbmp($this->resultImageDevice, $fileName);
				break;
			case "jpg":
			default:
				imagejpeg($this->resultImageDevice, $fileName, 80);
				break;
		}
		
		$this->resultImageDevice = NULL;
		
		return true;
	}
	
	function createThumbnailIntoCache() {
		header("Content-type: image/jpeg");
		imagejpeg($this->resultImageDevice);
		
		$originImageDevice = NULL;
		$this->resultImageDevice = NULL;
		
		return true;
	}
	
	function calcOptimizedImageSize($argWidth, $argHeight, $boxWidth=NULL, $boxHeight=NULL, $resizeFlag="reduce") {
		if (empty($boxWidth) && empty($boxHeight)) {
			return array($argWidth, $argHeight);
		} else if (!empty($boxWidth) && empty($boxHeight)) {
			if ($argWidth > $boxWidth) {
				$newWidth = $boxWidth;
				$newHeight = floor($argHeight * $newWidth / $argWidth);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
		} else if (empty($boxWidth) && !empty($boxHeight)) {
			if ($argHeight > $boxHeight) {
				$newHeight = $boxHeight;
				$newWidth = floor($argWidth * $newHeight / $argHeight);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
		} else {
			if ($argWidth > $boxWidth) {
				$newWidth = $boxWidth;
				$newHeight = floor($argHeight * $newWidth / $argWidth);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
			
			if ($newHeight > $boxHeight) {
				$tempHeight = $newHeight;
				$newHeight = $boxHeight;
				$newWidth = floor($newWidth * $newHeight / $tempHeight);
			}
		}
		
		if ($argWidth * $argHeight > $newWidth * $newHeight) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgWidth = $newWidth;
				$imgHeight = $newHeight;
			} else {
				$imgWidth = $argWidth;
				$imgHeight = $argHeight;
			}
		} else if ($argWidth * $argHeight == $newWidth * $newHeight) {
			$imgWidth = $argWidth;
			$imgHeight = $argHeight;
		} else if ($argWidth * $argHeight < $newWidth * $newHeight) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgWidth = $newWidth;
				$imgHeight = $newHeight;
			} else {
				$imgWidth = $argWidth;
				$imgHeight = $argHeight;
			}
		}
		
		return array($imgWidth, $imgHeight);
	}
	
	function checkExistingThumbnail($originSrc, $thumbnailSrc, $argWidth, $argHeight, $paddingArray=NULL, $waterMarkArray=NULL) {
		if (!file_exists($originSrc)) {
			return 0;
		}
		$originImageInfo = getimagesize($originSrc);
		list($tempWidth, $tempHeight) = Image::calcOptimizedImageSize($originImageInfo[0], $originImageInfo[1], $argWidth, $argHeight);
		
		if (!file_exists($thumbnailSrc)) {
			// in the case of a reduced image.
			if ($originImageInfo[0] > $tempWidth || $originImageInfo[1] > $tempHeight) {
				return 2;
			// even if a original size image or a magnified image, create a thumbnail in the case of existing a watermark file or padding value.
			} else if (($originImageInfo[0] <= $tempWidth || $originImageInfo[1] <= $tempHeight) && (file_exists($waterMarkArray['path']) || !empty($paddingArray))) {
				return 2;
			}
		} else {
			$thumbnailImageInfo = getimagesize($thumbnailSrc);
			$resizedWidth = $tempWidth;
			$resizedHeight = $tempHeight;
			
			// in the case of a reduced image.
			if ($thumbnailImageInfo[0] > $resizedWidth || $thumbnailImageInfo[1] > $resizedHeight) {
				return 1;
			// no process.
			} else {
				return 0;
			}
		}
	}
	
	function getImageType($filename) {
		if (file_exists($filename)) {
			if (function_exists("exif_imagetype")) {
				$imageType = exif_imagetype($filename);
			} else {
				$tempInfo = getimagesize($filename);
				$imageType = $tempInfo[2];
			}
			
			switch ($imageType) {
				case IMAGETYPE_GIF:
					$extension = 'gif';
					break;
				case IMAGETYPE_JPEG:
					$extension = 'jpg';
					break;
				case IMAGETYPE_PNG:
					$extension = 'png';
					break;
				case IMAGETYPE_SWF:
					$extension = 'swf';
					break;
				case IMAGETYPE_PSD:
					$extension = 'psd';
					break;
				case IMAGETYPE_BMP:
					$extension = 'bmp';
					break;
				case IMAGETYPE_TIFF_II:
				case IMAGETYPE_TIFF_MM:
					$extension = 'tiff';
					break;
				case IMAGETYPE_JPC:
					$extension = 'jpc';
					break;
				case IMAGETYPE_JP2:
					$extension = 'jp2';
					break;
				case IMAGETYPE_JPX:
					$extension = 'jpx';
					break;
				case IMAGETYPE_JB2:
					$extension = 'jb2';
					break;
				case IMAGETYPE_SWC:
					$extension = 'swc';
					break;
				case IMAGETYPE_IFF:
					$extension = 'aiff';
					break;
				case IMAGETYPE_WBMP:
					$extension = 'wbmp';
					break;
				case IMAGETYPE_XBM:
					$extension = 'xbm';
					break;
				default:
					$extension = false;
			}
		} else {
			$extension = false;
		}
		
		return $extension;
	}
	
	/* "FFFFFF" => array(255, 255, 255) */
	function hexRGB($hexstr) {
		$int = hexdec($hexstr);
		return array('R' => 0xFF & ($int >> 0x10), 'G' => 0xFF & ($int >> 0x8), 'B' => 0xFF & $int);
	}
}
?>
