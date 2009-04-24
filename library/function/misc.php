<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getArrayValue($array, $key) {
	return $array[$key];
}

function headerEtag($etag,$length,$lastmodified) {
	$cache_hit = false;
	if( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) || isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) { 
		$cache_hit = true; 
		if( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && $etag != $_SERVER['HTTP_IF_NONE_MATCH'] ) { 
			$cache_hit = false; 
		} else if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && $lastmodified != $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) {
			$cache_hit = false;
		}   
	}   

	if( $cache_hit ) { header("HTTP/1.1 304 Not Modified", true, 304); } 
	else {
		header("HTTP/1.1 200 Ok", true, 200);
		header("Last-Modified: " . $lastmodified);
		header("Date: " . gmdate("D, j M Y H:i:s ", time()) . " GMT");
		header("Expires: " . gmdate("D, j M Y H:i:s", time() + 3600*24) . " GMT");
		header("Cache-Control: max-age=" . 3600*24);
		header("Pragma: cache");        // HTTP/1.0
		header("Content-Length: $length");
	}   
	header("ETag: $etag");

	return $cache_hit;
}

function dumpWithEtag($path) {
	$path = urldecode($path);
	$qIndex = strpos($path,'?');
	if( $qIndex !== false ) {
		$path = substr($path,0,$qIndex);
	}
	/* I think, it is a bad idea to check '..' and skip.
	   but this is an annoyance to solve gracefully about whole HTTP request */
	/* Kill them all requests with referencing parent directory */
	if( strpos( $path, "/.." ) !== false || 
		strpos( $path, "\\.." ) !== false ||
		strcasecmp( substr( $path, -3 ), "php" ) == 0 ||
		!file_exists( $path ) ) {
		header("HTTP/1.0 404 Not found");
		exit;
	}
		
	$fs = stat( $path );
	if( !$fs || !$fs['size'] ) { header('HTTP/1.1 404 Not Found');exit; }
	$etag = sprintf( "textcube-%x", (0x1234*$fs['size'])^$fs['mtime'] );
	$lastmodified = gmdate("D, j M Y H:i:s ", $fs['mtime']) . "GMT";
	$length = $fs['size'];

	if( !headerEtag($etag,$length,$lastmodified) ) {
		header('Content-type: '.Utils_Misc::getMIMEType(null,$path)); 
		$f =  fopen($path,"r");
		if( !$f ) {
			header("HTTP/1.0 404 Not found");
			exit;
		}
		while( ($content=fread($f,8192)) ){
			echo $content;
		}
		fclose($f);
	}
}

?>
