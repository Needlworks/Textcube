<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

interface IAdapter
{
	public function connect($server, $dbname, $userid, $password, array $options);
	public function disconnect();
	public function beginTransaction();
	public function endTransaction($apply = true);
	public function query($query);
	public static function escapeString($var);
	public static function escapeFieldName($var);
}

class DBException extends Exception {}
class DBConnectionError extends DBException {}
class DBQueryError extends DBException {}

?>
