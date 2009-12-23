<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function cutlog( $file, $size )
{
	if( !file_exists($file) ) {
		return;
	}
	$st = stat( $file );
	if( $st['size'] < $size ) {
		return;
	}
	$f = fopen($file, "r+");
	fseek( $f, $st['size'] - $size );
	$log = '';
	fgets($f,1024); /* Skip incomplete first line */
	while( ($chunk=fread($f,4096)) ) {
		$log .= $chunk;
	}
	fseek( $f, 0 );
	ftruncate( $f, strlen($log) );
	fwrite( $f, $log );
	fclose($f);
}
?>
