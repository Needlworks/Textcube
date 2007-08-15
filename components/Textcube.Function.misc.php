<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class misc {
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

	function getAttributesFromString($str, $caseSensitive=false) {
		$attributes = array();
		preg_match_all('/([^=\s]+)\s*=\s*"([^"]*)/', $str, $matches); 
		for($i=0; $i<count($matches[0]); $i++) {
			if(!$caseSensitive)
				$matches[1][$i] = strtolower($matches[1][$i]);
			$attributes[$matches[1][$i]] = $matches[2][$i];
		}
		preg_match_all('/([^=\s]+)\s*=\s*\'([^\']*)/', $str, $matches);
		for($i=0; $i<count($matches[0]); $i++) {
			if(!$caseSensitive)
				$matches[1][$i] = strtolower($matches[1][$i]);
			$attributes[$matches[1][$i]] = $matches[2][$i];
		}
		preg_match_all('/([^=\s]+)=([^\'"][^\s]*)/', $str, $matches);
		for($i=0; $i<count($matches[0]); $i++) {
			if(!$caseSensitive)
				$matches[1][$i] = strtolower($matches[1][$i]);
			$attributes[$matches[1][$i]] = $matches[2][$i];
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
	
	function dress($tag, $value, & $contents) {
		if (eregi("\[##_{$tag}_##\]", $contents, $temp)) {
			$contents = str_replace("[##_{$tag}_##]", $value, $contents);
			return true;
		} else {
			return false;
		}	
	}
	function escapeJSInAttribute($str) {
		return htmlspecialchars(str_replace(array('\\', '\r', '\n', '\''), array('\\\\', '\\r', '\\n', '\\\''), $str));
	}

	function escapeCData($str) {
		return str_replace(']]>', ']]&gt;', $str);
	}
	
	function getTimeFromPeriod($period) {
		if (is_numeric($period)) {
			$year = 0;
			$month = 1;
			$day = 1;
			switch (strlen($period)) {
				case 8:
					$day = substr($period, 6, 2);
				case 6:
					$month = substr($period, 4, 2);
				case 4:
					$year = substr($period, 0, 4);
					if (checkdate($month, $day, $year))
						return mktime(0, 0, 0, $month, $day, $year);
			}
		}
		return false;
	}
	
	function fetchConfigVal( $DATA ){
		$xmls = new XMLStruct();
		$outVal = array();
		if( ! $xmls->open($DATA) ) {
			unset($xmls);	
			return null;
		}
		if( is_null(  $xmls->selectNodes('/config/field') )){
			unset($xmls);	
			return null;
		}
		foreach ($xmls->selectNodes('/config/field') as $field) {
			if( empty( $field['.attributes']['name'] )  || empty( $field['.attributes']['type'] ) ){
				unset($xmls);	
				return null;
			}
			$outVal[$field['.attributes']['name']] = $field['.value'] ;
		}
		unset($xmls);	
		return ( $outVal);
	}
	
	function getBlogSetting($name, $default = null) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid AND name = '".mysql_tt_escape_string($name)."'");
		return ($value === null) ? $default : $value;
	}

	function getBlogSettingGlobal($name, $default = null) {
		global $database, $blogid;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid AND name = '".mysql_tt_escape_string($name)."'");
		return ($value === null) ? $default : $value;
	}
	
	function setBlogSetting($name, $value) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		$name = mysql_tt_escape_string($name);
		$value = mysql_tt_escape_string($value);
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, '$name', '$value')");
	}
	
	function setBlogSettingGlobal($name, $value) {
		global $database, $blogid;
		$name = mysql_tt_escape_string($name);
		$value = mysql_tt_escape_string($value);
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES(".getBlogId().", '$name', '$value')");
	}

	function removeBlogSetting($name) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		return DBQuery::execute("DELETE FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid AND name = '".mysql_tt_escape_string($name)."'");
	}

	function getUserSetting($name, $default = null) {
		global $database;
		$name = 'plugin_' . $name;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}UserSettings WHERE userid = ".getUserId()." AND name = '".mysql_tt_escape_string($name)."'");
		return ($value === null) ? $default : $value;
	}

	function getUserSettingGlobal($name, $default = null) {
		global $database;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}UserSettings WHERE userid = ".getUserId()." AND name = '".mysql_tt_escape_string($name)."'");
		return ($value === null) ? $default : $value;
	}
	
	function setUserSetting($name, $value) {
		global $database;
		$name = 'plugin_' . $name;
		$name = mysql_tt_escape_string($name);
		$value = mysql_tt_escape_string($value);
		return DBQuery::execute("REPLACE INTO {$database['prefix']}UserSettings VALUES(".getUserId().", '$name', '$value')");
	}
	
	function removeUserSetting($name) {
		global $database;
		$name = 'plugin_' . $name;
		return DBQuery::execute("DELETE FROM {$database['prefix']}UserSettings WHERE userid = ".getUserId()." AND name = '".mysql_tt_escape_string($name)."'");
	}

	function getServiceSetting($name, $default = null) {
		global $database;
		$name = 'plugin_' . $name;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}ServiceSettings WHERE name = '".mysql_tt_escape_string($name)."'");
		return ($value === null) ? $default : $value;
	}

	function setServiceSetting($name, $value) {
		global $database;
		$name = 'plugin_' . $name;
		$name = mysql_tt_escape_string(mysql_lessen($name, 32));
		$value = mysql_tt_escape_string(mysql_lessen($value, 255));
		return DBQuery::execute("REPLACE INTO {$database['prefix']}ServiceSettings VALUES('$name', '$value')");
	}

	function removeServiceSetting($name) {
		global $database;
		$name = 'plugin_' . $name;
		return DBQuery::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".mysql_tt_escape_string($name)."'");
	}
	
	function getBlogSettingRowsPerPage($default = null) {
		global $database, $blogid;
		$value = DBQuery::queryCell("SELECT value FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid AND name = 'rowsPerPage'");
		return ($value === null) ? $default : $value;
	}

	function setBlogSettingRowsPerPage($value) {
		global $database, $blogid;
		$value = mysql_tt_escape_string($value);
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, 'rowsPerPage', '$value')");
	}

	function isMetaBlog() {
		return (getBlogId() == 1 ? true : false);
	}

	function respondResultPage($error) {
		if ($error === true)
			$error = 0;
		else if ($error === false)
			$error = 1;
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$error</error>\n</response>");
		exit;
	}
	
	function printRespond($result, $useCDATA=true) {
		header('Content-Type: text/xml; charset=utf-8');
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<response>\n";
		$xml .= misc::printRespondValue($result, $useCDATA);
		$xml .= "</response>\n";
		die($xml);
	}
	/* private */
	function printRespondValue($array, $useCDATA=true) {
		$xml = '';
		if(is_array($array)) {
			foreach($array as $key => $value) {
				if(is_null($value))
					continue;
				else if(is_array($value)) {
					if(is_numeric($key))
						$xml .= misc::printRespondValue($value, $useCDATA)."\n";
					else
						$xml .= "<$key>".misc::printRespondValue($value, $useCDATA)."</$key>\n";
				}
				else {
					if($useCDATA)
						$xml .= "<$key><![CDATA[".misc::escapeCData($value)."]]></$key>\n";
					else
						$xml .= "<$key>".htmlspecialchars($value)."</$key>\n";
				}
			}
		}
		return $xml;
	}
}
?>
