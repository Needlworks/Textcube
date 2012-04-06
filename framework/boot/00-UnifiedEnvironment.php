<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// @brief Environment unifier.
ini_set('session.use_trans_sid', '0');
ini_set('zend.ze1_compatibility_mode', 0);
if (intval(ini_get("session.auto_start")) == 1) {
   @session_destroy();
   @ini_set('session.auto_start', '0');
}
if (intval(ini_get("memory_limit")) < 24)
	@ini_set('memory_limit','24M');

if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) {
	function stripSlashesRecursively($value) {
		if (is_array($value))
			return array_map('stripSlashesRecursively', $value);
		else if (is_string($value))
			return stripslashes($value);
		else
			return $value;
	}

	$_GET = array_map('stripSlashesRecursively', $_GET);
	$_POST = array_map('stripSlashesRecursively', $_POST);
	$_COOKIE = array_map('stripSlashesRecursively', $_COOKIE);
	$_ENV = array_map('stripSlashesRecursively', $_ENV);
	//$_FILES = array_map('stripSlashesRecursively', $_FILES);
	$_REQUEST = array_map('stripSlashesRecursively', $_REQUEST);
	$_SERVER = array_map('stripSlashesRecursively', $_SERVER);
}

if (!isset($_SERVER['REQUEST_TIME']))
	$_SERVER['REQUEST_TIME'] = time();

$host = explode(':', $_SERVER['HTTP_HOST']);
if (count($host) > 1) {
	$_SERVER['HTTP_HOST'] = $host[0];
	$_SERVER['SERVER_PORT'] = $host[1];
}
unset($host);

if(isset($_SERVER['HTTP_CLIENT_IP'])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$firstIP = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
	$_SERVER['REMOTE_ADDR'] = $firstIP[0];
}
/* Workaround for REMOTE_ADDR Handling of IPv6 */
if(in_array($_SERVER['REMOTE_ADDR'],array('fe80::1','::1'))) {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
/* Workaround for iconv-absent environment. (contributed by Papacha) */
if (!function_exists('iconv')) {
	if (function_exists('mb_convert_encoding')) {
		function iconv($in, $out, $str) {
			return mb_convert_encoding($str, $out, $in);
		}
	} else {
		include_once(ROOT . '/library/function/iconv.php');
	}
}
/* Workaround for NCR treatment. (contributed by Laziel) */
if (!function_exists('mb_decode_numericentity')) { 
	function mb_decode_numericentity($str, $dumb = null, $dumber = null) {
		if (!function_exists('_mb_decode_numericentity_callback') ) {
			function _mb_decode_numericentity_callback($t) {
				$decode = $t[1];
					if ($decode < 128) {
						$str = chr($decode);
					} else if ($decode < 2048) {
						$str = chr(192 + (($decode - ($decode % 64)) / 64));
						$str .= chr(128 + ($decode % 64));
					} else {
						$str = chr(224 + (($decode - ($decode % 4096)) / 4096));
						$str .= chr(128 + ((($decode % 4096) - ($decode % 64)) / 64));
						$str .= chr(128 + ($decode % 64));
					}
					return $str;
			}
		}
		return preg_replace_callback('/&#([0-9]{1,});/', '_mb_decode_numericentity_callback', $str);
	}
}
?>
