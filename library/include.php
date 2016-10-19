<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Pre-define basic components */
/***** Loading code pieces *****/
if (isset($uri)) {
    $codeName = $uri->uri['interfaceType'];
}
if (($context->getProperty('service.codecache',null) == true) && file_exists(__TEXTCUBE_CACHE_DIR__ . '/code/' . $codeName)) {
    $codeCacheRead = true;
    require(__TEXTCUBE_CACHE_DIR__ . '/code/' . $codeName);
} else {
    $codeCacheRead = false;
    foreach ($context->getProperty('import.library') as $lib) {
        if (strpos($lib, 'DEBUG') === false) {
            importlib($lib);
        } else {
            if (defined('TCDEBUG')) {
                __tcSqlLogPoint($lib);
            }
        }
    }
}
if ($context->getProperty('service.codecache',null) == true && $codeCacheRead == false) {
    $libCode = new CodeCache();
    $libCode->name = $codeName;
    foreach ($context->getProperty('import.library') as $lib) {
        array_push($libCode->sources, '/library/' . str_replace(".", "/", $lib) . '.php');
    }
    $libCode->save();
    unset($libCode);
}
?>
