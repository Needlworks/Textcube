<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require_once ROOT . '/library/preprocessor.php';

if( empty($icon_path) ) {
	$icon_path = ROOT . "/attach/$blogid/favicon.ico";
	if( !file_exists($icon_path) ) {
		$icon_path = ROOT . '/resources/image/icon_favicon_default.ico';
	}
}

/* Icon size */
$st = stat( $icon_path );
$icon_size = 0;
if( !empty($st) ) {
	$icon_size = $st[7]; /* Size */
}

$approvedToSend = true;

/* If referred by other site */
if( !empty($_SERVER["HTTP_REFERER"]) && $icon_size > 0 ) {
	$host = split( '/', $_SERVER["HTTP_REFERER"] );
	$host = $host[2];

	$favicon_daily_traffic = 
		( empty($service['favicon_daily_traffic']) ? 10 : $service['favicon_daily_traffic'] ) *1024*1024; /* 10 MB/day */

	if( $host != $_SERVER['HTTP_HOST'] ) {
		define( 'REFERER_STAT', ROOT . "/cache/favicon_traffic.dat" );
		if( file_exists( REFERER_STAT ) ) {
			$referer = unserialize( file_get_contents( REFERER_STAT ) );
		}
		if( empty($referer)      )   { $referer = array();        }
		if( empty($referer[$host]) ) { $referer[$host] = array(); }
		if( empty($referer[$host]['begin']) ) { $referer[$host]['begin'] = time(); }
		if( empty($referer[$host]['sent']) )  { $referer[$host]['sent'] = $icon_size; }
		$elapsed = time() - $referer[$host]['begin'];

		/* Initialize begin time */
		if( $elapsed > 86400 ) { // 24 * 60 * 60
			$referer[$host]['begin'] = time();
			$referer[$host]['sent'] = 0;
		}
		if( $referer[$host]['sent'] > $favicon_daily_traffic ) {
			$approvedToSend = false;
		}
		$referer[$host]['sent'] += $icon_size;

		$f = fopen( REFERER_STAT, "w" );
		fwrite( $f, serialize( $referer ) );
		fclose( $f );
	}
}

if( !$approvedToSend ) {
	header( "HTTP/1.0 503 Service Unavailable" );
	exit;
}
dumpWithEtag( $icon_path );
?>
