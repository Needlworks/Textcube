<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function dumbCronScheduler($checkOnly=true)
{
	$ctx = Model_Context::getInstance();
	$now = Timestamp::getUNIXtime();

	$dumbCronStamps = Setting::getServiceSetting('dumbCronStamps',
			serialize( array( '1m' => 0, '5m' => 0, '30m' => 0, 
					'1h' => 0, '2h' => 0, '6h' => 0, '12h' => 0, '24h' => 0, 'Daily' => 0 )),true);

	$dumbCronStamps = unserialize( $dumbCronStamps );

	$schedules = array(
					'1m'  => 60,
					'5m'  => 60*5,
					'10m' => 60*10,
					'30m' => 60*30,
					'1h'  => 60*60,
					'2h'  => 60*60*2,
					'6h'  => 60*60*6,
					'12h' => 60*60*12,
					'24h' => 60*60*24,
					'Daily' => 60*60*24,
					'1w'  => 60*60*24*7 );
	/* Events: Cron1m, Cron5m, Cron30m, Cron1h, Cron2h, Cron6h, Cron12h */
	$log_file = ROOT.'/cache/cronlog.txt';
	$log = fopen( $log_file, "a" );
	foreach( $schedules as $d => $diff ) {
		if( !isset( $dumbCronStamps[$d] ) ) {
			$dumbCronStamps[$d] = 0;
		}
		if( $now > $diff + $dumbCronStamps[$d] ) { 
			if( $checkOnly && eventExists("Cron$d") ) {
				fclose($log);
				return true;
			}
			fireEvent( "Cron$d",  null, $now );
			if($d == '6h') {
				requireModel('blog.trash');
				trashVan();
			}
			fwrite( $log, date( 'Y-m-d H:i:s' ).' '.$ctx->getProperty('blog.name')." Cron$d executed ({$_SERVER['REQUEST_URI']})\r\n" );
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
	Setting::setServiceSetting( 'dumbCronStamps', serialize( $dumbCronStamps ), true );
	return false;
}

function doCronJob()
{
	dumbCronScheduler(false);
}

function checkCronJob()
{
	$ctx = Model_Context::getInstance();
	/* Cron, only in single page request, not in a page dead link */
	if( !empty($_SERVER['HTTP_REFERER']) || !dumbCronScheduler(true) ) return;

	$request = new HTTPRequest('GET', $ctx->getProperty('uri.default').'/cron');
	$request->timeout = 2;
	$request->send();
}
?>
