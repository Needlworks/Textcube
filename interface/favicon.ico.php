<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require_once ROOT . '/library/preprocessor.php';

$context = Model_Context::getInstance();
if( empty($icon_path) ) {
	$icon_path = __TEXTCUBE_ATTACH_DIR__."/".$context->getProperty("blog.id")."/favicon.ico";
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
    $host = explode( '/', $_SERVER["HTTP_REFERER"] );
	$host = $host[2];

    $favicon_daily_traffic = $context->getProperty("service.favicon_daily_traffic", 20) * 1024 * 1024; /* default: 20 MB/day */

	if( $host != $_SERVER['HTTP_HOST'] ) {
		define( 'REFERER_STAT', __TEXTCUBE_CACHE_DIR__."/favicon_traffic.dat" );
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
	errorExit(503);
}
dumpWithEtag( $icon_path );
?>
