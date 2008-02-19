<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';

function getSymbolByQuantity($bytes) {
	$symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	$exp = floor(log($bytes)/log(1024));

	return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

/* Current time */
$serverTime = strftime( "Server Time: %Y-%m-%d %H:%M:%S %z (%Z)", time() );

/* Database version */
$dbVersion = '';
if( $service['dbms'] == 'mysql' ) {
	$dbVersion = POD::queryColumn("SELECT VERSION()");
	$dbVersion = "MySQL: " . $dbVersion[0];
	$dbStat = preg_replace( "/\s{2,}/", "<br />", mysql_stat());
}

/* Webserver information */
$webServer = "Unknown";
if( function_exists('apache_get_version') ) {
	$webServer = apache_get_version();
}

/* OS version */
$osVersion = "Unknown";
if( function_exists('php_uname') ) {
	$osVersion = php_uname();
}

/* Disk space */
$totalSpace = getSymbolByQuantity( disk_total_space( dirname( __FILE__ ) ) );
$freeSpace = getSymbolByQuantity( disk_free_space( dirname( __FILE__ ) ) );
$diskSpace = "$freeSpace / $totalSpace (Free/Total)";

/* Load average */
$loadAvg = "Unknown";
if( function_exists( 'sys_getloadavg' ) ) {
	$loadAvg = sys_getloadavg();
	$loadAvg = "Last 1,5,15 min(s): {$loadAvg[0]} / {$loadAvg[1]} / {$loadAvg[2]}";
}
?>
<h2 class="caption"><span class="main-text"><?php echo _t('Server Info'); ?></span></h2>
<div class="generalinfo">
<table>
<tr>
	<td>Server time</td>
	<td><?php echo $serverTime; ?></td>
</tr>
<tr>
	<td>Database</td>
	<td><? echo $dbVersion; ?></td>
</tr>
<tr>
	<td>Database stat</td>
	<td><? echo $dbStat; ?></td>
</tr>
<tr>
	<td>Web server</td>
	<td><? echo $webServer; ?></td>
</tr>
<tr>
	<td>Operating System</td>
	<td><? echo $osVersion; ?></td>
</tr>
<tr>
	<td>Install path</td>
	<td><? echo dirname(dirname(dirname((dirname(dirname(__FILE__)))))); ?></td>
</tr>
<tr>
	<td>Disk space</td>
	<td><? echo $diskSpace; ?></td>
</tr>
<tr>
	<td>Load Avg.</td>
	<td><? echo $loadAvg; ?></td>
</tr>
</table>
</div>
<h2 class="caption"><span class="main-text"><?php echo _t('PHP Info'); ?></span></h2>
<div class="phpinfo">
<?php 
ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_end_clean();
echo preg_replace( "@.*<body.*?>(.*)</body>.*@sim",'$1', $phpinfo );

/* ORIGINAL embeded css from phpinfo()
<style type="text/css"><!--
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold; color: #000000;}
.h {background-color: #9999cc; font-weight: bold; color: #000000;}
.v {background-color: #cccccc; color: #000000;}
i {color: #666666; background-color: #cccccc;}
img {float: right; border: 0px;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
//--></style>
*/

?>
</div>
<?php require ROOT . '/lib/piece/owner/footer.php';?>
