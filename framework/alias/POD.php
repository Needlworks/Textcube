<?php
// POD : PHP Ontology(or Object-Oriented)-based Data model/framework
// Version 0.19a-PHP5
// By Jeongkyu Shin (inureyes@gmail.com)
// Created       : 2007.11.30
// Last modified : 2008.7.20

// (C) 2007 Jeongkyu Shin. All rights reserved. 
// Licensed under the GPL.
// See the GNU General Public License for more details. (/LICENSE, /COPYRIGHT)
// For more information, visit http://pod.nubimaru.com

// NOTE : THIS FILE CONTAINS LEGACY ROUTINE OF DBQuery ONLY.
//        FOR USING FULL FUNCTION, INCLUDE POD.Core.php instead.

// Bypass variables are supported. ($_pod_setting);
class POD extends DBAdapter {
    /** Pre-definition **/
    /** Initialization **/

    /** Additional features for Textcube **/
    /** NOTICE : PARTS BELOW EXTENDS DBQuery Class WHICH IS THE BASE OF POD
     * AND WORKS ONLY WITH 'PageCache' Component in Textcube **/
    public static function queryWithDBCache($query, $prefix = null, $type = 'both', $count = -1) {
        $cache = queryCache::getInstance();
        $cache->reset($query, $prefix);
        if (!$cache->load()) {
            $cache->contents = POD::query($query, $type, $count);
            $cache->update();
        }
        return $cache->contents;
    }

    public static function queryAllWithDBCache($query, $prefix = null, $type = 'both', $count = -1) {
        $cache = queryCache::getInstance();
        $cache->reset($query, $prefix);
        if (!$cache->load()) {
            $cache->contents = POD::queryAllWithCache($query, $type, $count);
            $cache->update();
        }
        return $cache->contents;
    }

    public static function queryRowWithDBCache($query, $prefix = null, $type = 'both', $count = -1) {
        $cache = queryCache::getInstance();
        $cache->reset($query, $prefix);
        if (!$cache->load()) {
            $cache->contents = POD::queryRow($query, $type, $count);
            $cache->update();
        }
        return $cache->contents;
    }

    public static function queryColumnWithDBCache($query, $prefix = null, $type = 'both', $count = -1) {
        $cache = queryCache::getInstance();
        $cache->reset($query, $prefix);
        if (!$cache->load()) {
            $cache->contents = POD::queryColumn($query, $type, $count);
            $cache->update();
        }
        return $cache->contents;
    }
}

POD::cacheLoad();
register_shutdown_function(array('POD', 'cacheSave'));
?>
