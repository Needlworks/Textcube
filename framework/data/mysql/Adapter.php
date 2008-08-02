<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class MySQLAdapter implements IAdapter
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
		if ($options != NULL) {
			foreach ($options as $key => $value) {
				switch ($key) {
				default:
					// Do something with options
					break;
				}
			}
		}
		$this->conn = mysql_connect($server, $userid, $password);
		if ($this->conn === FALSE) {
			$this->conn = NULL;
			throw new DBConnectionError(mysql_error());
		}
		@mysql_query("SET NAMES UTF8", $this->conn);
		@mysql_query("SET CHARACTER SET UTF8", $this->conn);
		if (mysql_select_db($dbname, $this->conn) === FALSE)
			throw new DBError("No such database: $dbname");
	}

	public function disconnect()
	{
		if ($this->conn) {
			mysql_close($this->conn);
			$this->conn = NULL;
		}
	}

	public function beginTransaction()
	{
		$this->_query("BEGIN", $this->conn);
	}

	public function endTransaction($apply = TRUE)
	{
		if ($apply)
			$this->_query("COMMIT", $this->conn);
		else
			$this->_query("ROLLBACK", $this->conn);
	}

	public function query($query)
	{
		return $this->_query($query);
	}

	public static function escapeString($var)
	{
		if (is_array($var))
			return array_map(array('MySQLAdaper', 'escapeString'), $var);
		else
			return mysql_escape_string($var);
	}

	public static function escapeFieldName($var)
	{
		if (is_array($var))
			return array_map(array('MySQLAdapter', 'escapeFieldName'), $var);
		else
			return '`'.$var.'`';
	}

	private function _query($query) {
		$result = mysql_query($query, $this->conn);
		if ($result === FALSE)
			throw new DBQueryError(mysql_error($this->conn));
		return $result;
	}
}
?>
