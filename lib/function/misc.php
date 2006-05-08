<?php

function getFileExtension($path) {
	for ($i = strlen($path) - 1; $i >= 0; $i--) {
		if ($path{$i} == '.')
			return strtolower(substr($path, $i + 1));
		if (($path{$i} == '/') || ($path{$i} == '\\'))
			break;
	}
	return '';
}

function getSizeHumanReadable($size) {
	if ($size < 1024)
		return "$size Bytes";
	else if ($size < 1048576)
		return sprintf("%0.2f", $size / 1024) . " KB";
	else if ($size < 1073741824)
		return sprintf("%0.2f", $size / 1048576) . " MB";
	else
		return sprintf("%0.2f", $size / 1073741824) . " GB";
}

function getArrayValue($array, $key) {
	return $array[$key];
}

function checkDomainName($name) {
	return ereg('^([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$', $name);
}

function getAttributesFromString($str) {
	$attributes = array();
	foreach (explode(' ', $str) as $value) {
		$value = trim($value);
		if (preg_match('/([^= ]+)="([^"]*)/', $value, $matches)) {
			$attributes[$matches[1]] = $matches[2];
		} else if (preg_match("/([^= ]+)='([^']*)/", $value, $matches)) {
			$attributes[$matches[1]] = $matches[2];
		} else if (preg_match('/([^= ]+)=([^ ]*)/', $value, $matches)) {
			$attributes[$matches[1]] = $matches[2];
		}
	}
	return $attributes;
}

function getMIMEType($ext, $filename = null) {
	if ($filename) {
		return '';
	} else {
		switch (strtolower($ext)) {
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
			case 'mpeg':case 'mpg':case 'mpe':
				return 'video/mpeg';
			case 'qt':case 'mov':
				return 'video/quicktime';
			case 'avi':case 'wmv':
				return 'video/x-msvideo';
			case 'pdf':
				return 'application/pdf';
			case 'bz2':
				return 'application/x-bzip2';
			case 'gz':case 'tgz':
				return 'application/x-gzip';
			case 'tar':
				return 'application/x-tar';
			case 'zip':
				return 'application/zip';
		}
	}
	return '';
}

function getNumericValue($value) {
	$value = trim($value);
	switch (strtoupper($value{strlen($value) - 1})) {
		case 'G':
			$value *= 1024;
		case 'M':
			$value *= 1024;
		case 'K':
			$value *= 1024;
	}
	return $value;
}
?>
