<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// Legacy support
require_once(ROOT . '/framework/legacy/Needlworks.PHP.Loader.php');

/// This function will override the current requireXXX functions.
/// python-style import 
function import() {
    $args = func_get_args();
    if (empty($args)) {
        return false;
    }
    foreach ($args as $libPath) {
        $paths = explode(".", $libPath);
        if (end($paths) == "*") {
            array_pop($paths);
            foreach (new DirectoryIterator(ROOT . '/framework/' . implode("/", $paths)) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    require_once($fileInfo->getPathname());
                }
            }
        } else {
            require_once ROOT . '/framework/' . $str_replace(".", "/", $libPath) . ".php";
        }
    }
    return true;
}

/** Library binders **/
function importlib() {
    $context = Model_Context::getInstance();
    $args = func_get_args();
    if (empty($args)) {
        return false;
    }
    foreach ($args as $libPath) {
        $paths = explode(".", $libPath);
        if (end($paths) == "*") {
            array_pop($paths);
            foreach (new DirectoryIterator(ROOT . '/library/' . implode("/", $paths)) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    require_once($fileInfo->getPathname());
                    //$context->setPropertyItem('import.lib', implode(".", $paths).'.'.preg_replace('/\\.[^.\\s]{3,4}$/', '', $fileInfo->getFilename()));
                }
            }
        } else {
            //if (!in_array($libPath, $context->getProperty('import.lib',array()))) {
                require_once ROOT . '/library/' . str_replace(".", "/", $libPath) . ".php";
            //    $context->setPropertyItem('import.lib', $libPath);
            //}
        }
    }
    return true;
}

/// Autoload function
class Autoload {
    static function load($className) {
        $pos = strrpos($className, '_');
        if ($pos !== false) {
            $naf2frameworkPath = ROOT . '/framework/' . str_replace('_', '/', strtolower(substr($className, 0, $pos))) . '/' . substr($className, $pos + 1) . '.php';
            if (file_exists($naf2frameworkPath)) {
                require_once $naf2frameworkPath;
            } else {
                // TODO : Error handler here.
            }
        } else {
            // Original structure (NAF2)
            if (file_exists(ROOT . '/framework/alias/' . $className . '.php')) {
                require_once ROOT . '/framework/alias/' . $className . '.php';
            } else {
                if (file_exists(ROOT . '/framework/' . strtolower($className) . '/' . $className . '.php')) {
                    require_once ROOT . '/framework/' . strtolower($className) . '/' . $className . '.php';
                } else {
                    if (file_exists(ROOT . '/framework/' . $className . '.php')) {
                        require_once ROOT . '/framework/' . $className . '.php';
                    } else {
                        // TODO : Error handler here.
                    }
                }
            }
        }
    }
}

spl_autoload_register(array('Autoload', 'load'));
?>
