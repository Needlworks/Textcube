<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define( 'TCDEBUG', true );

set_error_handler( "__error" );

function __error( $errno, $errstr, $errfile, $errline )
{
	if(in_array($errno, array(2048))) return;
	print("$errstr($errno)<br />");
	print("File: $errfile:$errline<br /><hr size='1' />");
}

global $__tcSqlLog;
global $__tcSqlLogCount;
global $__tcSqlQueryBeginTime;
global $__tcPageStartTime;
global $__tcPageEndTime;

$__tcPageStartTime = explode(' ', microtime());

$__tcSqlLog= array();
$__tcSqlLogCount = 0;

function __tcSqlLogBegin( $sql )
{
	global $__tcSqlLog, $__tcSqlQueryBeginTime, $__tcSqlLogCount;

	$backtrace = debug_backtrace();
	array_shift($backtrace);
	array_shift($backtrace);

	$__tcSqlLog[$__tcSqlLogCount] = array( 'sql' => trim($sql), 'backtrace' => $backtrace );
	$__tcSqlQueryBeginTime = explode(' ', microtime());
	//dumpAsFile($sql);
}
function __tcSqlLogEnd( $result, $cachedResult = 0 )
{
	global $__tcSqlLog, $__tcSqlQueryBeginTime, $__tcSqlLogCount, $__tcPageStartTime;
	static $client_encoding = '';
	$tcSqlQueryEndTime = explode(' ', microtime());
	$elapsed = ($tcSqlQueryEndTime[1] - $__tcSqlQueryBeginTime[1]) + ($tcSqlQueryEndTime[0] - $__tcSqlQueryBeginTime[0]);
	if( !$client_encoding ) {
		$client_encoding = str_replace('_','-',mysqli_client_encoding(POD::$db));
	}

	if( $client_encoding != 'utf8' && function_exists('iconv') ) {
		$__tcSqlLog[$__tcSqlLogCount]['error'] = iconv( $client_encoding, 'utf-8', mysqli_error(POD::$db));
	}
	else {
		$__tcSqlLog[$__tcSqlLogCount]['error'] = mysqli_error(POD::$db);
	}
	$__tcSqlLog[$__tcSqlLogCount]['errno'] = mysqli_errno(POD::$db);

	if( $cachedResult == 0 ) {
		$__tcSqlLog[$__tcSqlLogCount]['elapsed'] = ceil($elapsed * 10000) / 10;
	} else {
		$__tcSqlLog[$__tcSqlLogCount]['elapsed'] = 0;
	}
	$__tcSqlLog[$__tcSqlLogCount]['elapsed'] = sprintf("%4.1f", $__tcSqlLog[$__tcSqlLogCount]['elapsed'] );
	$__tcSqlLog[$__tcSqlLogCount]['cached'] = $cachedResult;
	$__tcSqlLog[$__tcSqlLogCount]['rows'] = 0;
	$__tcSqlLog[$__tcSqlLogCount]['endtime'] = ($tcSqlQueryEndTime[1] - $__tcPageStartTime[1]) + ($tcSqlQueryEndTime[0] - $__tcPageStartTime[0]);
	$__tcSqlLog[$__tcSqlLogCount]['endtime'] = sprintf("%4.1f",ceil($__tcSqlLog[$__tcSqlLogCount]['endtime'] * 10000) / 10);
	if( ! $cachedResult && mysqli_errno(POD::$db) == 0 ) {
		switch( strtolower(substr($__tcSqlLog[$__tcSqlLogCount]['sql'], 0, 6 )) )
		{
			case 'select':
				$__tcSqlLog[$__tcSqlLogCount]['rows'] = mysqli_num_rows($result);
				break;
			case 'insert':
			case 'delete':
			case 'update':
				$__tcSqlLog[$__tcSqlLogCount]['rows'] = mysqli_affected_rows(POD::$db);
				break;
		}
	}
	$__tcSqlLogCount++;
	$__tcSqlQueryBeginTime = 0;
}

function __tcSqlLogPoint($description = null)
{
	global $__tcSqlLog, $__tcSqlQueryBeginTime, $__tcSqlLogCount, $__tcPageStartTime, $__tcPageEndTime;
	if (is_null($description)) $description = 'Point'; 
	$backtrace = debug_backtrace();
	array_shift($backtrace);
	array_shift($backtrace);
	$__tcSqlLog[$__tcSqlLogCount] = array( 'sql' => '['. trim($description) .']', 'backtrace' => $backtrace );
	$__tcSqlQueryBeginTime = explode(' ', microtime());
	$tcSqlQueryEndTime = explode(' ', microtime());
	$__tcSqlLog[$__tcSqlLogCount]['error'] = '';
	$__tcSqlLog[$__tcSqlLogCount]['errno'] = '';
	$__tcSqlLog[$__tcSqlLogCount]['elapsed'] = '';
	$__tcSqlLog[$__tcSqlLogCount]['cached'] = '';
	$__tcSqlLog[$__tcSqlLogCount]['rows'] = '';
	$__tcSqlLog[$__tcSqlLogCount]['endtime'] = ($tcSqlQueryEndTime[1] - $__tcPageStartTime[1]) + ($tcSqlQueryEndTime[0] - $__tcPageStartTime[0]);
	$__tcSqlLog[$__tcSqlLogCount]['endtime'] = sprintf("%4.1f",ceil($__tcSqlLog[$__tcSqlLogCount]['endtime'] * 10000) / 10);
	$__tcPageEndTime = $__tcSqlLog[$__tcSqlLogCount]['endtime'];
	$__tcSqlLog[$__tcSqlLogCount]['rows'] = '';
	$__tcSqlLogCount++;
	$__tcSqlQueryBeginTime = 0;
}

function __tcSqlLoggetCallstack($backtrace, $level = 0) {
	$callstack = '';
	for ($i = $level; $i < count($backtrace); $i++) {
		if (isset($backtrace[$i]['file'])) {
			$callstack .= "{$backtrace[$i]['file']}:{$backtrace[$i]['line']}";
			if (!empty($backtrace[$i + 1]['type']))
				$callstack .= " {$backtrace[$i + 1]['class']}{$backtrace[$i + 1]['type']}{$backtrace[$i + 1]['function']}";
			else if (isset($backtrace[$i + 1]['function']))
				$callstack .= " {$backtrace[$i + 1]['function']}";
			$callstack .= "\r\n";
		}
	}
	if (empty($callstack))
		$callstack = $_SERVER['SCRIPT_FILENAME'] . "\r\n";
	return $callstack;
}

function __tcSqlLogDump()
{
	global $__tcSqlLog, $__tcPageEndTime;
	global $service, $memcache;
	static $sLogPumped = false;
	if (!empty($sLogPumped)) return;
	$sLogPumped = true;
	
	__tcSqlLogPoint('shutdown');
	
	$headers = array();
	
	if (function_exists('apache_response_headers') || function_exists('headers_list')) {
		if (function_exists('apache_response_headers')) {
			flush();
			$headers = apache_response_headers();
		} else  {
			$headers = headers_list();
		}
	}
	
	$commentBlosk = false;
	
	foreach ($headers as $row) {
		if (strpos($row, '/xml') !== false || strpos($row, '+xml') !== false) { 
			/* To check text/xml, application/xml and application/xml+blah, application/blah+xml... types */
			$commentBlosk = true;
			break;
		}
		if (strpos($row, 'text/javascript') !== false) {
			return;
		}
	}
	
	if ($commentBlosk == true) echo '<!--';
	
	if (!$commentBlosk) {
	print <<<EOS
<style type='text/css'>
/*<![CDATA[*/
	.debugTable
	{
		background-color: #fff;
		border-left: 1px solid #999;
		border-top: 1px solid #999;
		border-collapse: collapse;
		margin-bottom: 20px;
	}
	
	.debugTable *
	{
		border: none;
		margin: 0;
		padding: 0;
	}
	
	.debugTable td, .debugTable th
	{
		border-bottom: 1px solid #999;
		border-right: 1px solid #999;
		color: #000;
		font-family: Arial, Tahoma, Verdana, sans-serif;
		font-size: 12px;
		padding: 3px 5px;
	}
	
	.debugTable th
	{
		background-color: #dedede;
		text-align: center;
	}
	
	tr.debugSQLLine .rows
	{
		text-align: center;
	}
	
	tr.debugSQLLine .error
	{
		text-align: left;
	}
	
	tr.debugSQLLine .elapsed, tr.debugSQLLine .elapsedSum
	{
		text-align: right;
	}
	
	tr.debugSQLLine .backtrace
	{
		font-family: Courier, 'Courier new', monospace;
		font-size: 11px;
		letter-spacing: -1px;
	}
	
	tr.debugCached *, tr.debugSystem *
	{
		color: #888888 !important;
	}
	
	/* warning */
	tr.debugWarning *
	{
		background-color: #fefff1;
		color: #4b4b3b !important;
	}
	
	tr.debugWarning th
	{
		background-color: #e5e5ca;
	}
	
	/* error */
	tr.debugError *
	{
		background-color: #fee5e5;
		color: #961f1d !important;
	}
	
	tr.debugError th
	{
		background-color: #fccbca;
	}
	
	tfoot td
	{
		padding: 15px !important;
		text-align: center;
	}
/*]]>*/
</style>
EOS;
	}

	$elapsed_total_db = 0;

	$elapsed = array();
	$count = 1;
	$cached_count = 0;
	foreach( $__tcSqlLog as $c => $log ) {
		$elapsed[$count] = array( $log['elapsed'], $count, $log['cached'] ? "cached":"" );
		$__tcSqlLog[$c]['percent'] = sprintf("%4.1f", $log['endtime']*100/$__tcPageEndTime);
		$count++;
	}

	arsort( $elapsed );
	$bgcolor = array();
	foreach( array_splice($elapsed,0,5) as $e ) {
		if($e[2] !=  "cached")
			$top5[$e[1]] = true;
	}

	$count = 1;
	if (!$commentBlosk) {
		print '<table class="debugTable">';
		print <<<THEAD
		<thead>
			<tr>
				<th>count</th><th class="sql">query string</th><th>elapsed (ms)</th><th>elapsed sum (ms)</th><th></th><th>rows</th><th>error</th><th>stack</th>
			</tr>
		</thead>
THEAD;
		print '<tbody>';
	}
	foreach( $__tcSqlLog as $c => $log ) {
		$error = '';
		$backtrace = '';
		$frame_count = 1;
		$backtrace = __tcSqlLoggetCallstack($log['backtrace']);
		if( $log['errno'] ) {
			$error = "Error no. {$log['errno']} : {$log['error']}";
		}
		
		$trclass = '';
		$count_label = $count;
		if (!empty($error)) {
			$trclass = ' debugError';
		} else if( isset( $top5[$count] ) ) {
			$trclass = ' debugWarning';
		} else if( $log['cached'] == 1) {
			$error = "(cached)";
			$trclass .= ' debugCached';
			$cached_count++;
		} else if( $log['cached'] == 2 ) {
			$error = "";
			$trclass .= ' debugCached';
			$count_label = '';
			$backtrace = '';
		}
		if ($log['sql'] == '[shutdown]') {
			$error = "";
			$log['sql'] = 'Shutdown';
			$trclass .= ' debugSystem';
			$count_label = '';
			$backtrace = '';
		}
		
		$elapsed_total_db += $log['elapsed'];
		$elapsed_total = $log['endtime'];
		$progress_bar = $log['percent'] / 2; //Max 50px;
		if (!$commentBlosk) {
			$log['sql'] = htmlspecialchars($log['sql']);
			$log['percent'] = "<div style='background:#f00;line-height:10px;width:{$progress_bar}px'>&nbsp;</div>";
			print <<<TBODY
		<tr class="debugSQLLine{$trclass}">
			<th>{$count_label}</th>
			<td class="code"><code>{$log['sql']}</code></td>
			<td class="elapsed">{$log['elapsed']}</td>
			<td class="elapsedSum">{$log['endtime']}</td>
			<td class="elapsedSum">{$log['percent']}</td>
			<td class="rows">{$log['rows']}</td>
			<td class="error">{$error}</td>
			<td class="backtrace"><pre>{$backtrace}</pre></td>
		</tr>
TBODY;
		} else {
			$log['sql'] = str_replace('-->', '-- >', $log['sql']);
			print <<<TBODY

===============================================================================================
$count_label:{$log['sql']}
Elapsed:{$log['elapsed']} ms/End time:{$log['endtime']}/Percent:{$log['percent']}/Rows:{$log['rows']} rows
{$error}
{$backtrace}
TBODY;
		}
	
		if( $log['cached'] < 2 ) {
			$count++;
		}
	}
	
	$count--;
	$real_query_count = $count - $cached_count;
	
	if (!$commentBlosk) {
		print '</tbody>';
		print <<<TFOOT
<tfoot>
	<tr>
		<td colspan='8'>
		$count ($real_query_count+$cached_count cache) Queries <br />
		$elapsed_total_db ms elapsed in db query, overall $elapsed_total ms elapsed
		</td>
	</tr>
</tfoot>
TFOOT;
		print '</table>';
	}

	global $service, $URLInfo, $suri;
	print '<div class="debugTable">'.CRLF;
	print '<h4>Current Database Management System :</h4>'.CRLF.'<p>'.POD::dbms().' '.POD::version().'</p>'.CRLF;
	print '<h4>Cache system :</h4>'.CRLF;
	if(isset($service['pagecache']) && $service['pagecache'] == true) print '<p>Page cache Enabled</p>'.CRLF;
	else print '<p>Page cache Disabled</p>'.CRLF;
	if(isset($service['skincache']) && $service['skincache'] == true) print '<p>Skin cache Enabled</p>'.CRLF;
	else print '<p>Skin cache Disabled</p>'.CRLF;
	if(isset($service['memcached']) && $service['memcached'] == true) print '<p>Memcached module Enabled</p>'.CRLF;
	else print '<p>Memcached module Disabled</p>'.CRLF;
	if( ! empty($service['debug_session_dump'])) {
		print '<h4>Session Information</h4>'.CRLF;
		print '<pre>session_id = ' . session_id() . CRLF;
		print '$_SESSION = ';
		print_r( $_SESSION );
		print '$_COOKIE = ';
		print_r( $_COOKIE );
		print '</pre>';
	}
	if( ! empty($service['debug_rewrite_module'])) {
		print '<h4>Path parse result</h4>'.CRLF;
		print '<pre>'.CRLF;
		print_r( $URLInfo );
		print_r( $suri );
		print '</pre>';
	}
	print '</div>'.CRLF;
	if ($commentBlosk == true) echo '-->';
}

register_shutdown_function('__tcSqlLogDump');

function dump($data) {
	echo '<pre>';
	var_dump($data);
	echo'</pre>';
}

function dumpToHeader($data) {
	static $count = 0;
	$debug_string = print_r($data, true);
	foreach( split( "\n", $debug_string ) as $line ) {
		$count++;
		header( "X-TC-Debug-$count: $line" );
	}
}

function dumpAsFile($data) {
	if(!is_dir(ROOT."/cache")) {
		@mkdir(ROOT."/cache");
		@chmod(ROOT."/cache",0777);
	}
	$dumpFile = ROOT.'/cache/dump';
	if(file_exists($dumpFile)) {
		$dumpedLog = @file_get_contents($dumpFile);
	} else {
		$dumpedLog = '';
	}
	//$dumpedLog = $dumpedLog.Timestamp::format5()." : ".var_export($data,true).CRLF;
	$dumpedLog = $dumpedLog.var_export($data,true).CRLF;
	$fileHandle = fopen($dumpFile,'w');
	fwrite($fileHandle, $dumpedLog);
	fclose($fileHandle);
}
?>
