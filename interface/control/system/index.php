<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'range' => array('int', 'min' => -1, 'max' => 64, 'default' => -1)
	) 
);

require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/control/header.php';

requirePrivilege('group.creators');

function getSymbolByQuantity($bytes) {
	$symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	$exp = floor(log($bytes)/log(1024));

	return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

$context = Model_Context::getInstance();
/* Current time */
$serverTime = strftime( "Server Time: %Y-%m-%d %H:%M:%S %z (%Z)", time() );

/* Database version */
$dbVersion = '';
//if( $service['dbms'] == 'mysql' ) {
	$dbVersion = POD::version();
	$dbVersion = POD::dbms(). ": " . $dbVersion;
	$dbStat = preg_replace( "/\\s{2,}/", "<br />", POD::stat());
//}

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
	<div id="part-system-generalinfo" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('Server 정보'); ?></span></h2>
		
		<table>
			<tbody>
				<tr>
					<th>Server time</th>
					<td><?php echo $serverTime; ?></td>
				</tr>
				<tr>
					<th>Database</th>
					<td><?php echo $dbVersion; ?></td>
				</tr>
				<tr>
					<th>Database stat</th>
					<td><?php echo $dbStat; ?></td>
				</tr>
				<tr>
					<th>Web server</th>
					<td><?php echo $webServer; ?></td>
				</tr>
				<tr>
					<th>Operating System</th>
					<td><?php echo $osVersion; ?></td>
				</tr>
				<tr>
					<th>Install path</th>
					<td><?php echo dirname(dirname(dirname((dirname(dirname(__FILE__)))))); ?></td>
				</tr>
				<tr>
					<th>Disk space</th>
					<td><?php echo $diskSpace; ?></td>
				</tr>
				<tr>
					<th>Load Avg.</th>
					<td><?php echo $loadAvg; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div id="part-system-phpinfo" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('PHP 정보'); ?></span></h2>
		
<?php 
switch ($_GET['range']) {
	case 1:
	case 2:
	case 4:
	case 8:
	case 16:
	case 32:
	case 64:
	case -1:
		$phpinfo = (int) $_GET['range'];
		break;
	default:
		$phpinfo = -1;
		break;
}
?>
		<form id="phpinfoForm" action="<?php echo strtok($_SERVER['REQUEST_URI'], '?');?>">
			<label for="range"><?php echo _t('범위설정');?></label>
			<select id="range" name="range" onchange="document.getElementById('phpinfoForm').submit(); return false;">
				<option value="-1">All Information</option>
<?php
for ($i=1; $i<=64; $i=abs($i*2)) {
	switch ($i) {
		case 1:
			$text = 'General Information';
			break;
		case 2:
			$text = 'Credits';
			break;
		case 4:
			$text = 'Configuration';
			break;
		case 8:
			$text = 'Modules';
			break;
		case 16:
			$text = 'Environments';
			break;
		case 32:
			$text = 'Variables';
			break;
		case 64:
			$text = 'License';
			break;
	}
	
	echo sprintf('<option value="%d"%s>%s</option>',
					$i,
					$i == $phpinfo ? ' selected="selected"' : NULL,
					$text
				);
}
?>
			</select>
		</form>
		
<?php
if (!in_array('phpinfo', array_map('trim', explode(',', ini_get('disable_functions'))))) {
	ob_start();
	phpinfo($phpinfo);
	$phpinfo = ob_get_contents();
	ob_end_clean();

	$regexpArray = array();
	array_push($regexpArray, '@.*<body.*?>(.*)</body>.*@sim');
	array_push($regexpArray, '@<table.*>\s*<tr.*><td>\s*<a href="(.+)"><img border="0" src="(.+)" alt="PHP Logo" /></a><h1 class="p">(.+)</h1>\s*</td></tr>\s*</table>@Usi');
	//array_push($regexpArray, '@<table.*>\s*<tr.*><td>\s*<a href="(.+)"><img border="0" src="(.+)" alt="Zend logo" /></a>(.+)\s*</td></tr>\s*</table>@Usi');
	array_push($regexpArray, '@<(/?)h1(.*)>@Usi');
	array_push($regexpArray, '@<(/?)h2(.*)>@Usi');
	array_push($regexpArray, '@<tr class="h">(.+)</tr>@Usi');
	array_push($regexpArray, '@<table .*>@Usi');
	array_push($regexpArray, '@</table>@');
	array_push($regexpArray, '@<td class="e">(.+)</td>@Usi');
	array_push($regexpArray, '@<td class="v">(.+)</td>@Usi');
	array_push($regexpArray, '@<(br|hr) />@');
	//array_push($regexpArray, '');
	$resultArray = array();
	array_push($resultArray, '$1');
	array_push($resultArray, '<div id="PHPLogo"><a href="$1"><img src="$2" /></a><p>$3</p></div>');
	//array_push($resultArray, '<div id="ZendLogo"><a href="$1"><img src="$2" /></a><p>$3</p></div>');
	array_push($resultArray, '<$1h3>');
	array_push($resultArray, '<$1h4>');
	array_push($resultArray, '<thead><tr>$1</tr></thead><tbody>');
	array_push($resultArray, '<table>');
	array_push($resultArray, '</tbody></table>');
	array_push($resultArray, '<th>$1</th>');
	array_push($resultArray, '<td>$1</td>');
	array_push($resultArray, '');
	//array_push($resultArray, '');

	echo preg_replace($regexpArray, $resultArray, $phpinfo);
} else {
	echo _t('서버 정책이 보안을 위하여 PHP 정보를 출력할 수 없도록 설정되어 있습니다.');
}

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
<?php require ROOT . '/interface/common/control/footer.php';?>
