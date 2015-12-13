<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getFileExtension($path) {
	for ($i = strlen($path) - 1; $i >= 0; $i--) {
		if ($path{$i} == '.')
			return strtolower(substr($path, $i + 1));
		if (($path{$i} == '/') || ($path{$i} == '\\'))
			break;
	}
	return '';
}

function getMIMEType($ext, $filename = null) {
	if ($filename) {
		return getMIMEType(getFileExtension($filename));
	} else {
		switch (strtolower($ext)) {
			// Image
			case 'gif':
				return 'image/gif';
			case 'jpeg':case 'jpg':case 'jpe':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'tiff':case 'tif':
				return 'image/tiff';
			case 'bmp':
				return 'image/bmp';
			// Sound
			case 'wav':
				return 'audio/x-wav';
			case 'mpga':case 'mp2':case 'mp3':
				return 'audio/mpeg';
			case 'm3u':
				return 'audio/x-mpegurl';
			case 'wma':
				return 'audio/x-msaudio';
			case 'ra':
				return 'audio/x-realaudio';
			// Document
			case 'css':
				return 'text/css';
			case 'html':case 'htm':case 'xhtml':
				return 'text/html';
			case 'rtf':
				return 'text/rtf';
			case 'sgml':case 'sgm':
				return 'text/sgml';
			case 'xml':case 'xsl':
				return 'text/xml';
			case 'hwp':case 'hwpx':case 'hwpml':
				return 'application/x-hwp';
			case 'pdf':
				return 'application/pdf';
			case 'js':
				return 'application/javascript';
			case 'odt':case 'ott':
				return 'application/vnd.oasis.opendocument.text';
			case 'ods':case 'ots':
				return 'application/vnd.oasis.opendocument.spreadsheet';
			case 'odp':case 'otp':
				return 'application/vnd.oasis.opendocument.presentation';
			case 'sxw':case 'stw':
				return 'application/vnd.sun.xml.writer';
			case 'sxc':case 'stc':
				return 'application/vnd.sun.xml.calc';
			case 'sxi':case 'sti':
				return 'application/vnd.sun.xml.impress';
			case 'doc':
				return 'application/vnd.ms-word';
			case 'xls':case 'xla':case 'xlt':
			case 'xlb':
				return 'application/vnd.ms-excel';
			case 'ppt':case 'ppa':case 'pot':case 'pps':
				return 'application/vnd.mspowerpoint';
			case 'vsd':case 'vss':case 'vsw':
				return 'application/vnd.visio';
			case 'docx':case 'docm':
			case 'pptx':case 'pptm':
			case 'xlsx':case 'xlsm':
				return 'application/vnd.openxmlformats';
			case 'csv':
				return 'text/comma-separated-values';
			case 'apxl':
				return 'application/octet-stream';
			case 'key':case 'key-tef':case 'keynote':
				return 'application/x-iwork-keynote-sffkey';
			case 'pages':case 'pages-tef':
				return 'application/x-iwork-pages-sffpages';
			case 'numbers':case 'nmbtemplate':
				return 'application/x-iwork-numbers-sffnumbers';
			// Multimedia
			case 'mpeg':case 'mpg':case 'mpe':
				return 'video/mpeg';
			case 'qt':case 'mov':
				return 'video/quicktime';
			case 'avi':case 'wmv':
				return 'video/x-msvideo';
			case 'mp4':
				return 'video/mp4';
			case 'mkv':
				return 'video/x-matroska';
			// Compression
			case 'bz2':
				return 'application/x-bzip2';
			case 'gz':case 'tgz':
				return 'application/x-gzip';
			case 'tar':
				return 'application/x-tar';
			case 'zip':
				return 'application/zip';
			case 'rar':
				return 'application/x-rar-compressed';
			case '7z':
				return 'application/x-7z-compressed';
			case 'alz':
				return 'application/x-alzip';
		}
	}
	return '';
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
		header('Content-type: '.getMIMEType(null,$path));
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

function copyRecusive($source, $target, $chkPrint = false) {
	if (Path::getBaseName($source) == "." || Path::getBaseName($source) == "..") {
		return;
	}
	if (!is_dir($source)) {
		copy($source, $target);
		return;
	}
	if (!file_exists($target) || !is_dir($target)) {
		mkdir($target);
		@chmod($target, 0777);
	}
	$d = dir($source);
	while ($entry = $d->read()) {
		copyRecusive("$source/$entry", "$target/$entry", $chkPrint);
	}
	$d->close();
}

function deltree($dir) {
	$d = dir($dir);
	while ($f = $d->read()) {
		if ($f != "." && $f != "..") {
			if (is_dir($dir . $f)) {
				deltree($dir . $f . "/");
				rmdir($dir . $f);
			}
			if (is_file($dir . $f))
				unlink($dir . $f);
		}
	}
	$d->close();
}

function deleteFilesByRegExp($path, $regexp) {
	$path = rtrim($path, '/') . '/';

	if (!file_exists($path))
		return false;

	$handle = opendir($path);
	while ($tempFile = readdir($handle)) {
		if ($tempFile == '.' || $tempFile != '..') {
			continue;
		}
		if ($regexp == '*') {
			@unlink($path.$tempFile);
		} elseif (preg_match($regexp, $tempFile, $temp)) {
			@unlink($path.$tempFile);
		}
	}
	return true;
}
?>
