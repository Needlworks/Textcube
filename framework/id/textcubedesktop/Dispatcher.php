<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Dispatcher {
    private static $instances = array();

    public $uri, $service, $interfacePath;

    public function __construct() {
		if (!defined('__TEXTCUBE_CONFIG_FILE__')) {
			define('__TEXTCUBE_CONFIG_FILE__', ROOT . '/config.php');
		}
        $this->URIinterpreter();
        //$this->pathSelector();
    }

    final protected static function _getInstance($p = null) {
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

    private function pathSelector() {
        //define('__TEXTCUBE_CONFIG_FILE__', ROOT . '/config.php');
        if (!defined('__TEXTCUBE_CACHE_DIR__')) {
            define('__TEXTCUBE_CACHE_DIR__', ROOT . '/user/cache');
        }
        if (!defined('__TEXTCUBE_ATTACH_DIR__')) {
            define('__TEXTCUBE_ATTACH_DIR__', ROOT . '/user/attach');
        }
        //define('__TEXTCUBE_SKIN_DIR__',ROOT.'/user/skin/blog');
        //define('__TEXTCUBE_SKIN_CUSTOM_DIR__',__TEXTCUBE_SKIN_DIR__.'/customize');
    }

    private function URIinterpreter() {
        /* Workaround for IIS environment */
        if (!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        if (!empty($_SERVER['PRELOAD_CONFIG']) && file_exists(__TEXTCUBE_CONFIG_FILE__)) {
            require_once __TEXTCUBE_CONFIG_FILE__;
        }
        // IIS 7.0 and URL Rewrite Module CTP, but non-ASCII URLs are NOT supported.
        if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } // IIS 5.x/6.0/7.0 and Ionics ISAPI Rewrite Filter
        else {
            if (isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['REQUEST_URI'], 'rewrite.php') !== FALSE) {
                $_SERVER['REQUEST_URI'] = urldecode($_SERVER['HTTP_X_REWRITE_URL']);
            }
        }
        /* Retrieve Access Parameter Information. */
        $uri = array(
            'host' => $_SERVER['HTTP_HOST'],
            'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]),
            'position' => $_SERVER["SCRIPT_NAME"],
            'root' => rtrim(str_replace('rewrite.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
        );
        if (strpos($uri['fullpath'], $uri['root']) !== 0) {
            $uri['fullpath'] = $uri['root'] . substr($uri['fullpath'], strlen($uri['root']) - 1);
        }
        // Workaround for compartibility with fastCGI / Other environment
        $uri['input'] = ltrim(substr($uri['fullpath'],
            strlen($uri['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)), '/');
        // Support Tattertools 0.9x legacy address (for upgrade users)
        if (array_key_exists('pl', $_GET) && strval(intval($_GET['pl'])) == $_GET['pl']) {
            header("Location: " . $uri['root'] . $_GET['pl']);
            exit;
        }
        $part = strtok($uri['input'], '/');
        if (in_array($part, array('resources', 'plugins', 'cache', 'skin', 'attach', 'thumbnail'))) {
            $part = ltrim(rtrim($part == 'thumbnail' ?
                preg_replace('/thumbnail/', 'cache/thumbnail', $uri['input'], 1) :
                $uri['input']), '/');
            $part = (($qpos = strpos($part, '?')) !== false) ? substr($part, 0, $qpos) : $part;
            if (file_exists($part)) {
                require_once ROOT . '/library/function/file.php';
                dumpWithEtag($part);
                exit;
            } else {
                header("HTTP/1.0 404 Not Found");
                exit;
            }
        }
        if (strtok($part, '?') == 'setup.php') {
            require 'setup.php';
            exit;
        }
        $uri['fragment'] = explode('/', strtok($uri['input'], '?'));
        unset($part);

        /* Check the existence of config.php (whether installed or not) */
        if (!file_exists(ROOT . '/config.php')) {
            if (file_exists('.htaccess')) {
                print "<html><body>Remove '.htaccess' file first!</body></html>";
                exit;
            }
            header("Location: " . rtrim($_SERVER["REQUEST_URI"], "/") . "/setup.php");
        }
        /* Determine that which interface should be loaded. */
        require_once ROOT . '/config.php';
        if (defined('__TEXTCUBE_NO_FANCY_URL__')) {
            $service['type'] = 'single';
        }
        switch ($service['type']) {
            case 'path': // For path-based multi blog.
                array_splice($uri['fragment'], 0, 1);
                $pathPart = ltrim(rtrim(strtok(strstr($uri['input'], '/'), '?'), '/'), '/');
                break;
            case 'single':
                $pathPart = (strpos($uri['input'], '?') !== 0 ? ltrim(rtrim(strtok($uri['input'], '?'), '/'), '/') : '');
                break;
            case 'domain':
            default:
                $pathPart = ltrim(rtrim(strtok($uri['fullpath'], '?'), '/'), '/');
                if (!empty($service['path'])) {
                    $pathPart = ltrim($pathPart, $service['path']);
                }
                break;
        }
        $pathPart = strtok($pathPart, '&');
        // Determine interface Type
        if (isset($uri['fragment'][0])) {
            if (isset($uri['fragment'][1]) &&
                ($uri['fragment'][0] == 'owner') &&
                ($uri['fragment'][1] == 'reader' || ($uri['fragment'][1] == 'network' && isset($uri['fragment'][2]) && $uri['fragment'][2] == 'reader'))
            ) {
                $uri['interfaceType'] = 'reader';
            } else {
                switch ($uri['fragment'][0]) {
                    case 'feeder':
                        $uri['interfaceType'] = 'feeder';
                        break;
                    case 'owner':
                    case 'control':
                        $uri['interfaceType'] = 'owner';
                        break;
                    case 'favicon.ico':
                    case 'index.gif':
                        $uri['interfaceType'] = 'icon';
                        break;
                    //case 'i':case 'm':  -> overload at preprocessor.php
                    case 'checkup':
                        $uri['interfaceType'] = 'checkup';
                        break;
                    default:
                        $uri['interfaceType'] = 'blog';
                        break;
                }
            }
        } else {
            $uri['interfaceType'] = 'blog';
        }
        /* Load interface. */
        $interfacePath = null;
        if ($uri['interfaceType'] == 'icon') {
            $uri['interfacePath'] = $this->interfacePath = 'interface/' . $pathPart . '.php';
        } else {
            if (!empty($uri['fragment'])) {
                if (is_numeric(strtok(end($uri['fragment']), '&'))) {
                    array_pop($uri['fragment']);
                    $pathPart = count($uri['fragment']) == 1 ? null : implode('/', $uri['fragment']);
                }
                if (isset($uri['fragment'][0])) {
                    switch ($uri['fragment'][0]) {
                        case 'api':
                        case 'archive':
                        case 'attachment':
                        case 'author':
                        case 'category':
                        case 'cfeed':
                        case 'checkup':
                        case 'cover':
                        case 'cron':
                        case 'entry':
                        case 'feeder':
                        case 'foaf':
                        case 'guestbook':
                        case 'iMazing':
                        case 'keylog':
                        case 'line':
                        case 'location':
                        case 'locationSuggest':
                        case 'logout':
                        case 'notice':
                        case 'page':
                        case 'plugin':
                        case 'pluginForOwner':
                        case 'search':
                        case 'stream':
                        case 'suggest':
                        case 'tag':
                        case 'ttxml':
                        case 'imageResizer':
                            $pathPart = $uri['fragment'][0];
                            $interfacePath = 'interface/blog/' . $pathPart . '.php';
                            break;
                        case 'rss':
                        case 'atom':
                            if (isset($uri['fragment'][1]) && in_array($uri['fragment'][1], array('archive', 'author', 'category', 'comment', 'line', 'notifyComment', 'response', 'search', 'tag', 'trackback'))) {
                                $pathPart = $uri['fragment'][0] . '/' . $uri['fragment'][1];
                                $interfacePath = 'interface/' . $pathPart . '/index.php';
                            }
                            break;
                        case 'login':
                        case 'owner':
                        case 'control':
                            break;
                        case 'comment':
                        case 'trackback':
                            $pathPart = implode("/", $uri['fragment']);
                            $interfacePath = 'interface/blog/' . $pathPart . '/index.php';
                            break;
                        default:
                            if (!empty($uri['fragment'][0]) && strpos($uri['fragment'][0], 'page=') !== 0) {
                                $pathPart = '';
                                $interfacePath = 'interface/page.php';
                            }
                    }
                }

            }
            if (empty($interfacePath)) {
                $interfacePath = 'interface/' . (empty($pathPart) ? '' : $pathPart . '/') . 'index.php';
            }
            define('PATH', 'interface/' . (empty($pathPart) ? '' : $pathPart . '/'));
            unset($pathPart);
            if (!file_exists($interfacePath)) {
                header("HTTP/1.0 404 Not Found");
                exit;
            }
            $uri['interfacePath'] = $this->interfacePath = $interfacePath;
            $uri['interfaceRoute'] = rtrim($this->interfacePath, 'index.php');
        }
        $this->uri = $uri;
        $this->service = $service;
    }
}

?>
