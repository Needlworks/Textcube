<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

set_error_handler( "__error" );

function __error( $errno, $errstr, $errfile, $errline )
{
	if(in_array($errno, array(2048))) return;
	print("$errstr($errno)<br />");
	print("File: $errfile:$errline<br /><hr size='1'/>");
}

global $__tcSqlLog;
global $__tcSqlLogCount;
global $__tcSqlLogBeginTime;

$__tcSqlLog= array();
$__tcSqlLogCount = 0;

function __tcSqlLogBegin( $sql )
{
	global $__tcSqlLog, $__tcSqlLogBeginTime, $__tcSqlLogCount;

	$backtrace = debug_backtrace();
	array_shift($backtrace);
	array_shift($backtrace);

	$__tcSqlLog[$__tcSqlLogCount] = array( 'sql' => trim($sql), 'backtrace' => $backtrace );
	$__tcSqlLogBeginTime = microtime(true);
}
function __tcSqlLogEnd( $result, $cachedResult = false )
{
	global $__tcSqlLog, $__tcSqlLogBeginTime, $__tcSqlLogCount;
	static $client_encoding = '';
	if( !$client_encoding ) {
		$client_encoding = str_replace('_','-',mysql_client_encoding());
	}

	if( $client_encoding != 'utf-8' && function_exists('iconv') ) {
		$__tcSqlLog[$__tcSqlLogCount]['error'] = iconv( $client_encoding, 'utf-8', mysql_error());
	}
	else {
		$__tcSqlLog[$__tcSqlLogCount]['error'] = mysql_error();
	}
	$__tcSqlLog[$__tcSqlLogCount]['errno'] = mysql_errno();

	$__tcSqlLog[$__tcSqlLogCount]['elapsed'] = ceil((microtime(true) - $__tcSqlLogBeginTime) * 10000) / 10;
	$__tcSqlLog[$__tcSqlLogCount]['cached'] = $cachedResult;
	$__tcSqlLog[$__tcSqlLogCount]['rows'] = 0;
	if( mysql_errno() == 0 ) {
		switch( strtolower(substr($__tcSqlLog[$__tcSqlLogCount]['sql'], 0, 6 )) )
		{
			case 'select':
				$__tcSqlLog[$__tcSqlLogCount]['rows'] = mysql_num_rows($result);
				break;
			case 'insert':
			case 'delete':
			case 'update':
				$__tcSqlLog[$__tcSqlLogCount]['rows'] = mysql_affected_rows();
				break;
		}
	}
	$__tcSqlLogCount++;
	$__tcSqlLogBeginTime = 0;
}

function __tcSqlLogDump()
{
	global $__tcSqlLog, $__tcSqlLogBeginTime, $__tcSqlLogCount;
	print "<style type='text/css'>";
	print ".debugsql {background: #fff; border-collapse: collapse; margin: auto}";
	print ".debugsql tr.dbgnormal {line-height: 1.3em;}";
	print ".debugsql tr.dbgwarning {background:#fe0; line-height: 1.3em;}";
	print ".debugsql td, .debugsql th {border:1px solid; border-color: #000; font-size: 10pt; font-family: arial; padding:2px;}";
	print ".debugsql .sql {display:block; width: 500px;}";
	print ".debugsql .error {display:block; width: 500px; line-height: 1.3em; height: 100%;}";
	print "</style>";

	$elapsed_total = 0;

	$elapsed = array();
	$count = 1;
	foreach( $__tcSqlLog as $c => $log ) {
		$elapsed[$count] = array( $log['elapsed'], $count );
		$elapsed_total += $log['elapsed'];
		$count++;
	}

	arsort( $elapsed );
	$bgcolor = array();
	foreach( array_splice($elapsed,0,5) as $e ) {
		$top5[$e[1]] = true;
	}

	$count = 1;
	print "<table class='debugsql' style='border:1px solid;'>";
	print "<tr bgcolor='#eff'><th>Count</th><th class='sql'>SQL</th><th>Elapsed</th><th>Rows</th><th>error</th></tr>";
	foreach( $__tcSqlLog as $c => $log ) {
		$error = '';
		$backtrace = '';
		$frame_count = 1;
		foreach($log['backtrace'] as $frame) {
			$cl = isset($frame['class']) ? "{$frame['class']}::" : '';
			$fn = isset($frame['function']) ? "{$frame['function']}" : '';
			$fl = isset($frame['file']) ? "{$frame['file']}:" : '';
			$ln = isset($frame['line']) ? "{$frame['line']}" : '';
			$line = "$fl$ln $cl$fn";
			$backtrace .= "$frame_count: $line<br />";
			$frame_count++;
		}
		if( $log['errno'] ) {
			$error = "{$log['errno']}:{$log['error']}";
		}
		$trclass = "dbgnormal";
		if( isset( $top5[$count] ) ) {
			$trclass = "dbgwarning";
		}
		print "<tr class='$trclass'>";
		print "<td style='text-align:center'; rowspan='2'>$count</td>";
		print "<td class='sql'>{$log['sql']}</td>";
		print "<td>{$log['elapsed']} ms</td>";
		print "<td>{$log['rows']}</td>";
		print "<td class='error'>$error &nbsp;</td>";
		print "</tr>";
		print "<tr class='$trclass'>";
		print "<td colspan='4'>$backtrace</td>";
		print "</tr>";
		$count++;
	}
	print "<tr><td colspan='6'>$count Queries, $elapsed_total ms elapesed</td></tr>";
	print "</table>";
}
?>
