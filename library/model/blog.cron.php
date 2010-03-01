<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function dumbCronScheduler($checkOnly=true)
{
	global $service, $blog;
	requireModel('common.setting');
	$now = Timestamp::getUNIXtime();

	$dumbCronStamps = getServiceSetting('dumbCronStamps',
			serialize( array( '1m' => 0, '5m' => 0, '30m' => 0, 
					'1h' => 0, '2h' => 0, '6h' => 0, '12h' => 0, '24h' => 0, 'Daily' => 0 )));

	$dumbCronStamps = unserialize( $dumbCronStamps );

	$schedules = array(
					'1m'  => 60,
					'5m'  => 60*5,
					'30m' => 60*30,
					'1h'  => 60*60,
					'2h'  => 60*60*2,
					'6h'  => 60*60*6,
					'12h' => 60*60*12,
					'24h' => 60*60*24,
					'Daily' => 60*60*24 );
	/* Events: Cron1m, Cron5m, Cron30m, Cron1h, Cron2h, Cron6h, Cron12h */
	$log_file = dirname(__FILE__).DS."..".DS."..".DS."cache".DS."cronlog.txt";
	$log = fopen( $log_file, "a" );
	foreach( $schedules as $d => $diff ) {
		if( !isset( $dumbCronStamps[$d] ) ) {
			$dumbCronStamps[$d] = 0;
		}
		if( $now > $diff + $dumbCronStamps[$d] ) { 
			if( $checkOnly && eventExists("Cron$d") ) return true;
			fireEvent( "Cron$d",  null, $now );
			fwrite( $log, date( 'Y-m-d H:i:s' ).' '.$blog['name']." Cron$d executed ({$_SERVER['REQUEST_URI']})\r\n" );
			$dumbCronStamps[$d] = $now;
		}
	}
	fclose($log);

	/* Keep just 1000 lines */
	$logcontent = explode( "\r\n", file_get_contents( $log_file ) );
	$logcontent = implode( "\r\n", array_slice( $logcontent, -1000 ) );
	$log = fopen( $log_file, "w" );
	fwrite( $log, $logcontent );
	fclose( $log );
	setServiceSetting( 'dumbCronStamps', serialize( $dumbCronStamps ) );
	return false;
}

function doCronJob()
{
	dumbCronScheduler(false);
}

function checkCronJob()
{
	global $defaultURL;
	requireComponent('Eolin.PHP.HTTPRequest');
	/* Cron, only in single page request, not in a page dead link */
	if( !empty($_SERVER['HTTP_REFERER']) || !dumbCronScheduler(true) ) return;
	$request = new HTTPRequest('GET', $defaultURL.'/cron');
	$request->timeout = 2;
	$request->send();
}

?>
