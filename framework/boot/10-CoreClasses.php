<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// Singleton implementation.
class Singleton {
    // If your model support higher than PHP 5.3, you do not need to implement getInstance method.
    private static $instances;

    public function __construct() {
        $c = get_class($this);
        if (isset(self::$instances[$c])) {
            throw new Exception('You can not create more than one copy of a singleton.');
        } else {
            self::$instances[$c] = $this;
        }
    }

    public static function _getInstance($p = null) {
        $c = get_called_class();
        if (!isset(self::$instances[$c])) {
            $args = func_get_args();
            $reflection_object = new ReflectionClass($c);
            self::$instances[$c] = $reflection_object->newInstanceArgs($args);
        }
        return self::$instances[$c];
    }

    public static function getInstance() {
        return self::_getInstance();
    }

    public function __clone() {
        throw new Exception('You can not clone a singleton.');
    }
}

/// String manipulation class
final class Utils_String {
    static function endsWith($string, $end) {
        $longer = strlen($string) - strlen($end);
        if ($longer < 0) {
            return false;
        }
        return (strcmp(substr($string, $longer), $end) == 0);
    }

    static function startsWith($string, $start) {
        return (strncmp($string, $start, strlen($start)) == 0);
    }
}

final class Validator {
    /**
     * Date-Time        ::= RFC-1123 (the modification of RFC-822)
     * Language Code    ::= ISO-639 2-letter
     * Country Code    ::= ISO-3166 alpha-2 country codes
     * Language        ::= RFC-1766 language tag & RFC-3066
     * The used syntax in RFC-822 EBNF is:
     * 2*2ALPHA *( "-" 2*2ALPHA )
     * Timezone        ::= RFC-2822
     * The used syntax in RFC-822 EBNF is:
     * ( "+" / "-" ) 4DIGIT
     * E-Mail            ::= RFC-2822
     * The used syntax is
     * addr-spec = local-part "@" domain
     * local-part = dot-atom
     **/

    private static $queue;

    static function addRule($iv) {
        if (empty(self::$queue)) {
            self::$queue = array('GET' => array(), 'POST' => array(), 'REQUEST' => array(), 'SERVER' => array(), 'FILES' => array());
        }
        if (isset($iv['GET'])) {
            self::$queue['GET'] = array_merge(self::$queue['GET'], $iv['GET']);
        }
        if (isset($iv['POST'])) {
            self::$queue['POST'] = array_merge(self::$queue['POST'], $iv['POST']);
        }
        if (isset($iv['REQUEST'])) {
            self::$queue['REQUEST'] = array_merge(self::$queue['REQUEST'], $iv['REQUEST']);
        }
        if (isset($iv['SERVER'])) {
            self::$queue['SERVER'] = array_merge(self::$queue['SERVER'], $iv['SERVER']);
        }
        if (isset($iv['FILES'])) {
            self::$queue['FILES'] = array_merge(self::$queue['FILES'], $iv['FILES']);
        }
    }

    static function isValid() {
        return self::validate(self::$queue);
    }

    static function validate(&$iv) {
        if (isset($iv['GET'])) {
            if (!Validator::validateArray($_GET, $iv['GET'])) {
                return false;
            }
            foreach (array_keys($_GET) as $key) {
                if (!array_key_exists($key, $iv['GET'])) {
                    unset($_GET[$key]);
                }
            }
        } else {
            $_GET = array();
        }

        if (isset($iv['POST'])) {
            if (!Validator::validateArray($_POST, $iv['POST'])) {
                return false;
            }
            foreach (array_keys($_POST) as $key) {
                if (!array_key_exists($key, $iv['POST'])) {
                    unset($_POST[$key]);
                }
            }
        } else {
            $_POST = array();
        }

        if (isset($iv['REQUEST'])) {
            if (!Validator::validateArray($_REQUEST, $iv['REQUEST'])) {
                return false;
            }
            foreach (array_keys($_REQUEST) as $key) {
                if (!array_key_exists($key, $iv['REQUEST'])) {
                    unset($_REQUEST[$key]);
                }
            }
        } else {
            $_REQUEST = array();
        }

        if (isset($iv['SERVER'])) {
            if (!Validator::validateArray($_SERVER, $iv['SERVER'])) {
                return false;
            }
        }

        if (isset($iv['FILES'])) {
            if (!Validator::validateArray($_FILES, $iv['FILES'])) {
                return false;
            }
            foreach (array_keys($_FILES) as $key) {
                if (!array_key_exists($key, $iv['FILES'])) {
                    unset($_FILES[$key]);
                }
            }
        } else {
            $_FILES = array();
        }
        return true;
    }

    static function validateArray(&$array, &$rules) {
        // Workaround for non Fancy-URL user.
        $cropArray = array();
        foreach ($array as $name => $value) {
            $doesHaveRequest = strpos($name, '?');
            if ($doesHaveRequest !== false) {
                $name = substr($name, $doesHaveRequest + 1);
            }
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
                if (isset($rule['min'])) {
                    $rule[1] = $rule['min'];
                }
                if (isset($rule['max'])) {
                    $rule[2] = $rule['max'];
                }
                if (isset($rule['bypass'])) {
                    $rule[3] = $rule['bypass'];
                }

                switch ($rule[0]) {
                    case 'any':
                        if (isset($rule[1]) && (strlen($value) < $rule[1])) {
                            return false;
                        }
                        if (isset($rule[2]) && (strlen($value) > $rule[2])) {
                            return false;
                        }
                        break;
                    case 'bit':
                        $array[$key] = Validator::getBit($value);
                        break;
                    case 'bool':
                        $array[$key] = Validator::getBool($value);
                        break;
                    case 'number':
                        if (!Validator::number($value, (isset($rule[1]) ? $rule[1] : null), (isset($rule[2]) ? $rule[2] : null), (isset($rule[3]) ? $rule[3] : false))) {
                            return false;
                        }
                        break;
                    case 'int':
                        if (!Validator::isInteger($value, (isset($rule[1]) ? $rule[1] : -2147483648), (isset($rule[2]) ? $rule[2] : 2147483647), (isset($rule[3]) ? $rule[3] : false))) {
                            return false;
                        }
                        break;
                    case 'id':
                        if (!Validator::id($value, (isset($rule[1]) ? $rule[1] : 1), (isset($rule[2]) ? $rule[2] : 2147483647))) {
                            return false;
                        }
                        break;
                    case 'url':
                    case 'string':
                        if (!Utils_Unicode::validate($value)) {
                            $value = Utils_Unicode::bring($value);
                            if (!Utils_Unicode::validate($value)) {
                                return false;
                            }
                        }
                        $value = $array[$key] = Utils_Unicode::correct($value);

                        if (isset($rule[1]) && (Utils_Unicode::length($value) < $rule[1])) {
                            return false;
                        }
                        if (isset($rule[2]) && (Utils_Unicode::length($value) > $rule[2])) {
                            return false;
                        }
                        break;
                    case 'list':
                        if (!Validator::isList($value)) {
                            return false;
                        }
                        break;
                    case 'timestamp':
                        if (!Validator::timestamp($value)) {
                            return false;
                        }
                        break;
                    case 'period':
                        if (!Validator::period($value)) {
                            return false;
                        }
                        break;
                    case 'ip':
                        if (!Validator::ip($value)) {
                            return false;
                        }
                        break;
                    case 'domain':
                        if (!Validator::domain($value)) {
                            return false;
                        }
                        break;
                    case 'email':
                        if (!Validator::email($value)) {
                            return false;
                        }
                        break;
                    case 'language':
                        if (!Validator::language($value)) {
                            return false;
                        }
                        break;
                    case 'filename':
                        if (!Validator::filename($value)) {
                            return false;
                        }
                        break;
                    case 'directory':
                        if (!Validator::directory($value)) {
                            return false;
                        }
                        break;
                    case 'path':
                        if (!Validator::path($value)) {
                            return false;
                        }
                        break;
                    case 'file':
                        if (!isset($value['name']) || preg_match('@[/\\\\]@', $value['name'])) {
                            return false;
                        }
                        break;
                    default:
                        if (is_array($rule[0])) {
                            if (!in_array($value, $rule[0])) {
                                return false;
                            }
                        } else {
                            trigger_error("Validator: The type of '$key' is unknown", E_USER_WARNING);
                        }
                        break;
                }

                if (isset($rule['check'])) {
                    $rule[5] = $rule['check'];
                }
                if (isset($rule[5])) {
                    if (function_exists($rule[5])) {
                        if (!call_user_func($rule[5], $value)) {
                            return false;
                        }
                    } else {
                        trigger_error("Validator: The check function of '$key' is not defined", E_USER_WARNING);
                    }
                }
            } else {
                if (array_key_exists(3, $rule)) {
                    $array[$key] = $rule[3];
                } else {
                    if (array_key_exists('default', $rule)) {
                        $array[$key] = $rule['default'];
                    } else {
                        if ((!isset($rule[4]) || $rule[4]) && (!isset($rule['mandatory']) || $rule['mandatory'])) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    static function number($value, $min = null, $max = null, $bypass = false) {
        if (($bypass === false) && !is_numeric($value)) {
            return false;
        }
        if (!is_null($value)) {
            if (isset($min) && ($value < $min)) {
                return false;
            }
            if (isset($max) && ($value > $max)) {
                return false;
            }
        }
        return true;
    }

    static function isInteger($value, $min = -2147483648, $max = 2147483647, $bypass = false) {
        if (($bypass === false) && !preg_match('/^(0|-?[1-9][0-9]{0,9})$/', $value)) {
            return false;
        }
        if (!is_null($value)) {
            if (($value < $min) || ($value > $max)) {
                return false;
            }
        }
        return true;
    }

    static function id($value, $min = 1, $max = 2147483647) {
        return Validator::isInteger($value, $min, $max);
    }

    static function isList($value) {
        if (!preg_match('/^[1-9][0-9]{0,9}(,[1-9][0-9]{0,9})*,?$/', $value)) {
            return false;
        }
        return true;
    }

    /**
     *    Valid: Jan 1 1971 ~ Dec 31 2037 GMT
     */

    static function timestamp($value) {
        return (Validator::isInteger($value) && ($value >= 31536000) && ($value < 2145916800));
    }

    static function period($value, $length = null) {
        if (preg_match('/\\d+/', $value)) {
            if (isset($length) && (strlen($value) != $length)) {
                return false;
            }
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

    static function ip($value) {
        return preg_match('/^\\d{1,3}(\\.\\d{1,3}){3}$/', $value);
    }

    static function domain($value) {
        return ((strlen($value) <= 64) && preg_match('/^([[:alnum:]]+(-[[:alnum:]]+)*\\.)+[[:alnum:]]+(-[[:alnum:]]+)*$/', $value));
    }

    static function email($value) {
        if (strlen($value) > 64) {
            return false;
        }
        $parts = explode('@', $value, 2);
        return ((count($parts) == 2) && preg_match('@[\\w!#\-\'*+/=?^`{-~-]+(\\.[\\w!#-\'*+/=?^`{-~-]+)*@', $parts[0]) && Validator::domain($parts[1]));
    }

    static function language($value) {
        return preg_match('/^[[:alpha:]]{2}(\-[[:alpha:]]{2})?$/', $value);
    }

    static function filename($value) {
        return preg_match('/^\w+(\.\w+)*$/', $value);
    }

    static function directory($value) {
        return preg_match('/^[\-\w]+( [\-\w]+)*$/', $value);
    }

    static function path($value) {
        return preg_match('/^[\-\w]+( [\-\w]+)*(\/[\-\w]+( [\-\w]+)*)*$/', $value);
    }

    static function getBit($value) {
        return (Validator::getBool($value) ? 1 : 0);
    }

    static function getBool($value) {
        return (!empty($value) && (!is_string($value) || (strcasecmp('false', $value) && strcasecmp('off', $value) && strcasecmp('no', $value))));
    }

    static function escapeXML($string, $escape = true) {
        if (is_null($string)) {
            return null;
        }
        return ($escape ? htmlspecialchars($string) : str_replace('&amp;', '&', preg_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string)));
    }
}

final class Timezone {
    static function isGMT() {
        return (date('Z') == 0);
    }

    static function get() {
        $mezone = getenv('TZ');
        if (empty($timezone)) {
            $timezone = date('T');
        }
        return (empty($timezone) ? 'UTC' : $timezone);
    }

    static function getOffset() {
        return (int)date('Z');
    }

    static function getCanonical() {
        return sprintf("%+03d:%02d", intval(Timezone::getOffset() / 3600), abs((Timezone::getOffset() / 60) % 60));
    }

    static function getRFC822() {
        if (Timezone::isGMT()) {
            return 'GMT';
        } else {
            return sprintf("%+05d", intval(Timezone::getOffset() / 3600) * 100 + ((Timezone::getOffset() / 60) % 60));
        }
    }

    static function getISO8601($timezone = null) {
        if (Timezone::isGMT()) {
            return 'Z';
        } else {
            return sprintf("%+03d:%02d", intval(Timezone::getOffset() / 3600), abs((Timezone::getOffset() / 60) % 60));
        }
    }

    static function set($timezone) {
//        if (isset($_ENV['OS']) && strncmp($_ENV['OS'], 'Windows', 7) == 0) {
//            $timezone = Timezone::getAlternative($timezone); // modern PHP timezone code is OS-independent.
//        }
        date_default_timezone_set($timezone);
        return putenv('TZ=' . $timezone);
    }

    static function setOffset($offset) {
        return Timezone::setISO8601(sprintf("%+02d:%02d", floor($offset / 3600), abs(($offset / 60) % 60)));
    }

    static function setRFC822($timezone) {
        if (($timezone == 'GMT') || ($timezone == 'UT')) {
            return Timezone::set('GMT');
        } else {
            if (!is_numeric($timezone) || (strlen($timezone) != 5)) {
                return false;
            } else {
                if ($timezone{0} == '+') {
                    return Timezone::set('UTC-' . substr($timezone, 1, 2) . ':' . substr($timezone, 3, 2));
                } else {
                    if ($timezone{0} == '-') {
                        return Timezone::set('UTC+' . substr($timezone, 1, 2) . ':' . substr($timezone, 3, 2));
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    static function setISO8601($timezone) {
        if ($timezone == 'Z') {
            return Timezone::set('GMT');
        }
        if (!preg_match('/^([-+])(\d{1,2})(:)?(\d{2})?$/', $timezone, $matches)) {
            return false;
        }
        $matches[0] = 'GMT';
        $matches[1] = ($matches[1] == '+' ? '-' : '+');
        if (strlen($matches[2]) == 1) {
            $matches[2] = '0' . $matches[2];
        }
        if (empty($matches[3])) {
            $matches[3] = ':';
        }
        if (empty($matches[4])) {
            $matches[4] = '00';
        }
        return Timezone::set(implode('', $matches));
    }

    static function getList() {
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

    static function getAlternative($timezone) {
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


final class Timestamp {
    static function format($format = '%c', $time = null) {
        if (isset($time)) {
            return strftime(_t($format), $time);
        } else {
            return strftime(_t($format));
        }
    }

    static function formatGMT($format = '%c', $time = null) {
        if (isset($time)) {
            return gmstrftime(_t($format), $time);
        } else {
            return gmstrftime(_t($format));
        }
    }

    static function format2($time) {
        if (date('Ymd', $time) == date('Ymd')) {
            return strftime(_t('%H:%M'), $time);
        } else {
            if (date('Y', $time) == date('Y', time())) {
                return strftime(_t('%m/%d'), $time);
            } else {
                return strftime(_t('%Y'), $time);
            }
        }
    }

    static function format3($time) {
        if (date('Ymd', $time) == date('Ymd')) {
            return strftime(_t('%H:%M:%S'), $time);
        } else {
            return strftime(_t('%Y/%m/%d'), $time);
        }
    }

    static function format5($time = null) {
        return (isset($time) ? strftime(_t('%Y/%m/%d %H:%M'), $time) : strftime(_t('%Y/%m/%d %H:%M')));
    }

    static function formatDate($time = null) {
        return (isset($time) ? strftime(_t('%Y/%m/%d'), $time) : strftime(_t('%Y/%m/%d')));
    }

    static function formatDate2($time = null) {
        return (isset($time) ? strftime(_t('%Y/%m'), $time) : strftime(_t('%Y/%m')));
    }

    static function formatTime($time = null) {
        return (isset($time) ? strftime(_t('%H:%M:%S'), $time) : strftime(_t('%H:%M:%S')));
    }

    static function get($format = 'YmdHis', $time = null) {
        return (isset($time) ? date($format, $time) : date($format));
    }

    static function getGMT($format = 'YmdHis', $time = null) {
        return (isset($time) ? gmdate($format, $time) : gmdate($format));
    }

    static function getDate($time = null) {
        return (isset($time) ? date('Ymd', $time) : date('Ymd'));
    }

    static function getYearMonth($time = null) {
        return (isset($time) ? date('Ym', $time) : date('Ym'));
    }

    static function getYear($time = null) {
        return (isset($time) ? date('Y', $time) : date('Y'));
    }

    static function getTime($time = null) {
        return (isset($time) ? date('His', $time) : date('His'));
    }

    static function getRFC1123($time = null) {
        return (isset($time) ? date('r', $time) : date('r'));
    }

    static function getRFC1123GMT($time = null) {
        return (isset($time) ? gmdate('D, d M Y H:i:s \G\M\T', $time) : gmdate('D, d M Y H:i:s \G\M\T'));
    }

    static function getRFC1036($time = null) {
        return ((isset($time) ? date('l, d-M-Y H:i:s ', $time) : date('l, d-M-Y H:i:s ')) . Timezone::getRFC822());
    }

    static function getISO8601($time = null) {
        return ((isset($time) ? date('Y-m-d\TH:i:s', $time) : date('Y-m-d\TH:i:s')) . Timezone::getISO8601());
    }

    static function getUNIXtime($time = null) {
        return intval(isset($time) ? date('U', $time) : date('U'));
    }

    static function getHumanReadablePeriod($time = null) {
        $deviation = abs(Timestamp::getUNIXtime($time));
        if ($deviation < 60) {
            return _f('%1초', $deviation);
        } else {
            if ($deviation < 3600) {
                return _f('%1분', intval($deviation / 60));
            } else {
                if ($deviation < 86400) {
                    return _f('%1시간', intval($deviation / 3600));
                } else {
                    if ($deviation < 604800) {
                        return _f('%1일', intval($deviation / 86400));
                    } else {
                        return _f('%1주', intval($deviation / 604800));
                    }
                }
            }
        }
    }

    static function getHumanReadable($time = null, $from = null) {
        if (is_null($from)) {
            $deviation = Timestamp::getUNIXtime() - Timestamp::getUNIXtime($time);
        } else {
            $deviation = Timestamp::getUNIXtime($from) - Timestamp::getUNIXtime($time);
        }

        if ($deviation > 0) { // Past.
            if ($deviation < 60) {
                return _f('%1초 전', $deviation);
            } else {
                if ($deviation < 3600) {
                    return _f('%1분 전', intval($deviation / 60));
                } else {
                    if ($deviation < 86400) {
                        return _f('%1시간 전', intval($deviation / 3600));
                    } else {
                        if ($deviation < 604800) {
                            return _f('%1일 전', intval($deviation / 86400));
                        } else {
                            return _f('%1주 전', intval($deviation / 604800));
                        }
                    }
                }
            }
        } else {
            $deviation = abs($deviation);
            if ($deviation < 60) {
                return _f('%1초 후', $deviation);
            } else {
                if ($deviation < 3600) {
                    return _f('%1분 후', intval($deviation / 60));
                } else {
                    if ($deviation < 86400) {
                        return _f('%1시간 후', intval($deviation / 3600));
                    } else {
                        if ($deviation < 604800) {
                            return _f('%1일 후', intval($deviation / 86400));
                        } else {
                            return _f('%1주 후', intval($deviation / 604800));
                        }
                    }
                }
            }
        }
    }
}

final class Timer {
    /**
     * Original code is written by Crizin (crizin@gmail.com)
     **/
    private $start, $stop;

    function __construct() {
        $this->start();
    }

    public function start() {
        $this->start = $this->getMicroTime();
    }

    public function pause() {
        $this->stop = $this->getMicroTime();
    }

    public function resume() {
        $this->start += $this->getMicroTime() - $this->stop;
        $this->stop = 0;
    }

    public function fetch($decimalPlaces = 3) {
        return sprintf('%.3f', round(($this->getMicrotime() - $this->start), $decimalPlaces));
    }

    public static function getMicroTime() {
        list($usec, $sec) = explode(' ', microtime());
        return (float)$usec + (float)$sec;
    }
}

final class Path {
    static function getBaseName($path) {
        $pattern = (strncasecmp(PHP_OS, 'WIN', 3) ? '/([^\/]+)[\/]*$/' : '/([^\/\\\\]+)[\/\\\\]*$/');
        if (preg_match($pattern, $path, $matches)) {
            return $matches[1];
        }
        return '';
    }

    static function getExtension($path) {
        if (preg_match('/.{1}(\.[[:alnum:]]+)$/', $path, $matches)) {
            return strtolower($matches[1]);
        } else {
            return '';
        }
    }

    static function getExtension2($path) {
        if (preg_match('/.{1}(\.[[:alnum:]]+(\.[[:alnum:]]+)?)$/', $path, $matches)) {
            return strtolower($matches[1]);
        } else {
            return '';
        }
    }

    static function combine($path) {
        $args = func_get_args();
        return implode('/', $args);
    }

    static function removeFiles($directory) {
        if (!is_dir($directory)) {
            return false;
        }
        $dir = dir($directory);
        while (($file = $dir->read()) !== false) {
            if (is_file(Path::combine($directory, $file))) {
                unlink(Path::combine($directory, $file));
            }
        }
        return true;
    }
}


class XMLStruct {
    var $struct, $error;

    function __construct() {
        $this->ns = array();
        $this->baseindex = 0;
    }

    /* static helper function */

    /*@static@*/
    static function getValueByLocale($param) {
        if (!is_array($param)) {
            return $param;
        }
        for ($i = 0; $i < count($param); $i++) {
            if (isset($param[$i]['.attributes']['xml:lang'])) {
                $lang = $param[$i]['.attributes']['xml:lang'];
            } else {
                $lang = "";
            }
            $locale = Locales::getInstance();
            switch ($locale->match($lang)) {
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
                    if (!isset($thirdBest)) {
                        $thirdBest = $param[$i];
                    }
                    break;
            }
        }
        if (isset($secondBest)) {
            $matched = $secondBest;
        } else {
            if (isset($thirdBest)) {
                $matched = $thirdBest;
            }
        }

        if (!isset($matched)) {
            return null;
        }

        if (isset($matched['.value'])) {
            return $matched['.value'];
        }
        return null;
    }

    public function setXPathBaseIndex($baseindex = 1) {
        $this->baseindex = $baseindex;
    }

    public function setNameSpacePrefix($prefix, $url) {
        $this->ns[$prefix] = $url;
    }

    public function expandNS($item) {
        if (!$this->nsenabled) {
            return $item;
        }
        foreach ($this->ns as $prefix => $url) {
            if (substr($item, 0, strlen($prefix) + 1) == "$prefix:") {
                return "$url:" . substr($item, strlen($prefix) + 1);
            }
        }
        return $item;
    }

    public function open($xml, $encoding = null, $nsenabled = false) {
        if (!empty($encoding) && (strtolower($encoding) != 'utf-8') && !Utils_Unicode::validate($xml)) {
            if (preg_match('/^<\?xml[^<]*\s+encoding=["\']?([\w-]+)["\']?/', $xml, $matches)) {
                $encoding = $matches[1];
                $xml = preg_replace('/^(<\?xml[^<]*\s+encoding=)["\']?[\w-]+["\']?/', '$1"utf-8"', $xml, 1);
            }
            if (strcasecmp($encoding, 'utf-8')) {
                $xml = Utils_Unicode::bring($xml, $encoding);
                if (is_null($xml)) {
                    $this->error = XML_ERROR_UNKNOWN_ENCODING;
                    return false;
                }
            }
        } else {
            if (substr($xml, 0, 3) == "\xEF\xBB\xBF") {
                $xml = substr($xml, 3);
            }
        }
        $this->nsenabled = $nsenabled;
        if ($nsenabled) {
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
        if (!xml_parse($p, $xml)) {
            return $this->_error($p);
        }
        unset($this->_cursor);
        unset($this->_cdata);
        if (xml_get_error_code($p) != XML_ERROR_NONE) {
            return $this->_error($p);
        }
        xml_parser_free($p);
        return true;
    }

    public function openFile($filename, $correct = false) {
        if (!$fp = fopen($filename, 'r')) {
            return false;
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
                if (!xml_parse($p, Utils_Unicode::correct($chunk, '?'), false)) {
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
        if (!xml_parse($p, '', true)) {
            return $this->_error($p);
        }
        unset($this->_cursor);
        unset($this->_cdata);
        if (xml_get_error_code($p) != XML_ERROR_NONE) {
            return $this->_error($p);
        }
        xml_parser_free($p);
        return true;
    }

    public function close() {
    }

    public function setStream($path) {
        $this->_streams[$path] = true;
    }

    public function setConsumer($consumer) {
        $this->_consumer = $consumer;
    }

    public function & selectNode($path, $lang = null) {
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
            } else {
                if ($matches[3] != 'lang()') { // Position.
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
                        if (isset($cursor[$name][$i]['.attributes']['xml:lang'])) {
                            $lang = $cursor[$name][$i]['.attributes']['xml:lang'];
                        } else {
                            $lang = "";
                        }
                        $locale = Locales::getInstance();

                        switch ($locale->match($lang)) {
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
                                if (!isset($thirdBest)) {
                                    $thirdBest = &$cursor[$name][$i];
                                }
                                break;
                        }
                    }
                    if (isset($secondBest)) {
                        $cursor = &$secondBest;
                    } else {
                        if (isset($thirdBest)) {
                            $cursor = &$thirdBest;
                        } else {
                            $null = null;
                            return $null;
                        }
                    }
                }
            }
        }
        return $cursor;
    }

    public function & selectNodes($path) {
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
            if (isset($c[$d][$o])) {
                $c = &$c[$d][$o];
            } else {
                break;
            }
        }
        $null = null;
        return $null;
    }

    public function doesExist($path) {
        return (!is_null($this->selectNode($path)));
    }

    public function getAttribute($path, $name, $default = null) {
        $n = &$this->selectNode($path);
        if ((!is_null($n)) && isset($n['.attributes'][$name])) {
            return $n['.attributes'][$name];
        } else {
            return $default;
        }
    }

    public function getValue($path) {
        $n = &$this->selectNode($path);
        return (isset($n['.value']) ? $n['.value'] : null);
    }

    public function getNodeCount($path) {
        return count($this->selectNodes($path));
    }

    private function o($p, $n, $a) {
        if (!isset($this->_cursor[$n])) {
            $this->_cursor[$n] = array();
        }
        if (empty($a)) {
            $this->_cursor = &$this->_cursor[$n][array_push($this->_cursor[$n], array('.value' => '', '_' => &$this->_cursor)) - 1];
        } else {
            $this->_cursor = &$this->_cursor[$n][array_push($this->_cursor[$n], array('.attributes' => $a, '.value' => '', '_' => &$this->_cursor)) - 1];
        }
        $this->_cdata = null;
        array_push($this->_path, $n);
        if (isset($this->_streams[implode('/', $this->_path)])) {
            $this->_cursor['.stream'] = tmpfile();
        }
    }

    private function c($p, $n) {
        if (count($this->_cursor) != (2 + isset($this->_cursor['.attributes']))) {
            unset($this->_cursor['.value']);
        } else {
            $this->_cursor['.value'] = rtrim($this->_cursor['.value']);
        }
        $c = &$this->_cursor;
        $this->_cursor = &$this->_cursor['_'];
        unset($c['_']);
        if (isset($this->_consumer)) {
            if (call_user_func($this->_consumer, implode('/', $this->_path), $c, xml_get_current_line_number($p))) {
                if (count($this->_cursor[$n]) == 1) {
                    unset($this->_cursor[$n]);
                } else {
                    array_pop($this->_cursor[$n]);
                }
            }
        }
        array_pop($this->_path);
    }

    private function d($p, $d) {
        if (count($this->_cursor) != (1 + isset($this->_cursor['.value']) + isset($this->_cursor['.attributes']) + isset($this->_cursor['.stream']))) {
            return;
        }
        if (!$this->_cdata) {
            if (isset($this->_cdata)) {
                $this->_cursor['.value'] = rtrim($this->_cursor['.value']);
            }
            $this->_cdata = true;
            $d = ltrim($d);
        }
        if (strlen($d) == 0) {
            return;
        }
        if (empty($this->_cursor['.stream'])) {
            $this->_cursor['.value'] .= $d;
        } else {
            fwrite($this->_cursor['.stream'], $d);
        }
    }

    private function x($p, $d) {
        if ($d == '<![CDATA[') {
            $this->_cdata = true;
        } else {
            if (($d == ']]>') && $this->_cdata) {
                $this->_cdata = false;
            }
        }
    }

    private function _error($p) {
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

final class URL {
    static function encode($url, $useEncodedURL = true) {
        $postfix = '';
        if (substr($url, strlen($url) - 1) == '?') {
            $url = substr($url, 0, strlen($url) - 1);
            $postfix = '?';
        }
        if ($useEncodedURL == true) {
            return str_replace('%2F', '/', rawurlencode($url)) . $postfix;
        } else {
            return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?', '+'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F', '%2B'), $url) . $postfix;
        }
    }

    static function decode($url, $useEncodedURL = true) {
        if ($useEncodedURL == true) {
            return rawurldecode($url);
        } else {
            return urldecode($url);
        }
    }
}
