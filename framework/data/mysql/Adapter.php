<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// TODO: process errors and exceptions

class Adapter implements IDBAdapter
{
	function __construct()
	{
		$this->conn = NULL;
	}

	function __destruct()
	{
		$this->disconnect();
	}

	public function connect($server, $dbname, $userid, $password, array $options)
	{
		$this->conn = mysql_connect($server, $userid, $password);
		@mysql_query("SET NAMES UTF8");
		@mysql_query("SET CHARACTER SET UTF8");
		mysql_query("USE $dbname");
	}

	public function disconnect()
	{
		if ($this->conn)
			mysql_close($this->conn);
	}

	public function beginTransaction()
	{
		mysql_query("BEGIN");
	}

	public function endTransaction($apply = true)
	{
		if ($apply)
			mysql_query("COMMIT");
		else
			mysql_query("ROLLBACK");
	}

	public function query($query)
	{
		return mysql_query($query);
	}

	public static function escapeString($var)
	{
		if (is_array($var)) {
			return array_map(escapeString, $var);
		} else {
			return mysql_escape_string($var);
		}
	}

	public static function escapeFieldName($var)
	{
		if (is_array($var)) {
			return array_map(escapeFieldName, $var);
		} else {
			return '`'.$var.'`';
		}
	}
}
?>
