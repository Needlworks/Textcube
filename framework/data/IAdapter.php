<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

interface IAdapter {
    /// DBMS Connection
    public static function bind($database);

    public static function unbind();

    /// DBMS Information
    public static function charset();

    public static function dbms();

    public static function version($mode = 'server');

    public static function tableList($condition = null);

    public static function setTimezone($time);

    public static function reservedFieldNames();

    public static function reservedFunctionNames();

    /// Querying
    public static function queryExistence($query);

    public static function queryCount($query);

    public static function queryCell($query, $field = 0, $useCache = true);

    public static function queryRow($query, $type = 'both', $useCache = true);

    public static function queryColumn($query, $useCache = true);

    public static function queryAll($query, $type = 'both', $count = -1);

    public static function queryAllWithoutCache($query, $type = 'both', $count = -1);

    public static function queryAllWithCache($query, $type = 'both', $count = -1);

    public static function execute($query);

    public static function multiQuery();

    public static function query($query);

    public static function insertId();

    public static function escapeString($string, $link = null);

    public static function clearCache();

    public static function cacheLoad();

    public static function cacheSave();

    /// Transaction
    public static function commit();

    /// RAW connection
    public static function num_rows($handle = null);

    public static function free($handle = null);

    public static function fetch($handle = null, $type = 'assoc');

    public static function error($err = null);

    public static function stat($stat = null);

    public static function __queryType($type);

    /// Model Creation
    public static function fieldType($abstractType);
}

?>
