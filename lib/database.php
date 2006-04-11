<?
mysql_connect($database['server'], $database['username'], $database['password']);
mysql_select_db($database['database']);
@mysql_query('SET CHARACTER SET utf8');
@mysql_query('SET SESSION collation_connection = \'utf8_general_ci\'');
?>