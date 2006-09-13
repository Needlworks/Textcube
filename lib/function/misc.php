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

function getAttributesFromString($str) {
	$attributes = array();
	preg_match_all('/([^=\s]+)\s*=\s*"([^"]*)/', $str, $matches); 
	for($i=0; $i<count($matches[0]); $i++)
		$attributes[$matches[1][$i]] = $matches[2][$i];
	preg_match_all('/([^=\s]+)\s*=\s*\'([^\']*)/', $str, $matches);
	for($i=0; $i<count($matches[0]); $i++)
		$attributes[$matches[1][$i]] = $matches[2][$i];
	preg_match_all('/([^=\s]+)=([^\'"][^\s]*)/', $str, $matches);
	for($i=0; $i<count($matches[0]); $i++)
		$attributes[$matches[1][$i]] = $matches[2][$i];
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
			case 'rar':
				return 'application/x-rar-compressed';
			case '7z':
				return 'application/x-7z-compressed';
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

function getContentWidth() {
	global $skinSetting, $service;
	
	$contentWidth = 400;			
	if ($xml = @file_get_contents(ROOT."/skin/{$skinSetting['skin']}/index.xml")) {
		$xmls = new XMLStruct();
		$xmls->open($xml,$service['encoding']);
		if ($xmls->getValue('/skin/default/contentWidth')) {
			$contentWidth = $xmls->getValue('/skin/default/contentWidth');
		}
	}
	return $contentWidth;
}
?>
