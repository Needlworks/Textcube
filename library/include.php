<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Pre-define basic components */
/***** Loading code pieces *****/
if (isset($uri)) {
    $codeName = $uri->uri['interfaceType'];
}
if (isset($service['codecache']) && ($service['codecache'] == true) && file_exists(__TEXTCUBE_CACHE_DIR__ . '/code/' . $codeName)) {
    $codeCacheRead = true;
    require(__TEXTCUBE_CACHE_DIR__ . '/code/' . $codeName);
} else {
    $codeCacheRead = false;
    foreach ((array_merge($context->getProperty('import.basics'), $context->getProperty('import.library'))) as $lib) {
        if (strpos($lib, 'DEBUG') === false) {
            require ROOT . '/library/' . $lib . '.php';
        } else {
            if (defined('TCDEBUG')) {
                __tcSqlLogPoint($lib);
            }
        }
    }
    foreach ($context->getProperty('import.model') as $lib) {
        if (strpos($lib, 'DEBUG') === false) {
            require ROOT . '/library/model/' . $lib . '.php';
        } else {
            if (defined('TCDEBUG')) {
                __tcSqlLogPoint($lib);
            }
        }
    }

    foreach ($context->getProperty('import.view') as $lib) {
        if (strpos($lib, 'DEBUG') === false) {
            require ROOT . '/library/view/' . $lib . '.php';
        } else {
            if (defined('TCDEBUG')) {
                __tcSqlLogPoint($lib);
            }
        }
    }
}
if (isset($service['codecache'])
    && $service['codecache'] == true && $codeCacheRead == false
) {
    $libCode = new CodeCache();
    $libCode->name = $codeName;
    foreach ((array_merge($context->getProperty('import.basics'), $context->getProperty('import.library'))) as $lib) {
        array_push($libCode->sources, '/library/' . $lib . '.php');
    }
    foreach ($context->getProperty('import.model') as $lib) {
        array_push($libCode->sources, '/library/model/' . $lib . '.php');
    }
    foreach ($context->getProperty('import.view') as $lib) {
        array_push($libCode->sources, '/library/view/' . $lib . '.php');
    }
    $libCode->save();
    unset($libCode);
}
?>
