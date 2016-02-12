<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// @brief Environment unifier.
ini_set('session.use_trans_sid', '0');
ini_set('zend.ze1_compatibility_mode', 0);
if (intval(ini_get("session.auto_start")) == 1) {
    @session_destroy();
    @ini_set('session.auto_start', '0');
}
if (intval(ini_get("memory_limit")) < 24) {
    @ini_set('memory_limit', '24M');
}

if (!isset($_SERVER['REQUEST_TIME'])) {
    $_SERVER['REQUEST_TIME'] = time();
}

$host = explode(':', $_SERVER['HTTP_HOST']);
if (count($host) > 1) {
    $_SERVER['HTTP_HOST'] = $host[0];
    $_SERVER['SERVER_PORT'] = $host[1];
}
unset($host);

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
} else {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $firstIP = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = $firstIP[0];
    }
}
/* Workaround for REMOTE_ADDR Handling of IPv6 */
if (in_array($_SERVER['REMOTE_ADDR'], array('fe80::1', '::1'))) {
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
?>
