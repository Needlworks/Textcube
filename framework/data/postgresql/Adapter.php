<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class PSQLAdapter implements IAdapter
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
		$connection_str = "host=$server dbname=$dbname user=$userid password=$password";
		if ($options != NULL) {
			foreach ($options as $key => $value) {
				switch ($key) {
				default:
					// Do something with options
					break;
				}
			}
		}
		$this->conn = pg_connect($connection_str);
		if (pg_connection_status($this->conn) === PSQL_CONNECTION_OK) {
			pg_set_client_encoding($this->conn, 'UTF8');		
		} else {
			$this->conn = NULL;
			throw new ConnectionError(pg_last_error());
		}
	}

	public function disconnect()
	{
		if ($this->conn) {
			pg_close($this->conn);
			$this->conn = NULL;
		}
	}

	public function beginTransaction()
	{
		pg_query($this->conn, "BEGIN");
	}

	public function endTransaction($apply = TRUE)
	{
		if ($apply)
			pg_query($this->conn, "COMMIT");
		else
			pg_query($this->conn, "ROLLBACK");
	}

	public static function escapeString($var)
	{
		if (is_array($var))
			return array_map(PSQLAdapter::escapeString, $var);
		else
			return pg_escape_string($var);
	}

	public static function escapeFieldName($var)
	{
		// TODO: how to do this in psql?
		return $var;
	}
}

?>
