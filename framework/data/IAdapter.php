<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

interface IDBAdapter
{
	public function connect($server, $dbname, $userid, $password, array $options);
	public function disconnect();
	public function beginTransaction();
	public function endTransaction($apply = true);
	public function query($query);
	public static function escape($string);
}

class DBException extends Exception {}
class ConnectionError extends DBException {}
class QueryError extends DBException {}

?>
