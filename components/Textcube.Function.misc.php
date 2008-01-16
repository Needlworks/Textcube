<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
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
		if (preg_match('/\[##_' . preg_quote($tag, '/') . '_##\]/i', $contents, $temp)) {
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
	function isMetaBlog() {
		return (getBlogId() == getServiceSetting("defaultBlogId",1) ? true : false);
	}

	function respondResultPage($errorResult) {
		if (is_array($errorResult)) {
			$error = $errorResult[0];
			$errorMsg = $errorResult[1];
		} else {
			$error = $errorResult;
			$errorMsg = '';
		}
		if ($error === true)
			$error = 0;
		else if ($error === false)
			$error = 1;
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$error</error>\n<message><![CDATA[$errorMsg]]></message></response>");
		exit;
	}
	
	/* Synch with lib/view/pages.php */
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

/***** Functions below are legacy support : THEY WILL BE REMOVED AFTER 1.6 MILESTONE. *****/

	function fetchConfigVal( $DATA ){
		requireComponent('Textcube.Function.Setting');
		return setting::fetchConfigVal($DATA);
	}

	// For Blog-scope setting
	function getBlogSettingGlobal($name, $default = null, $blogid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getBlogSettingGlobal($name, $default, $blogid);
	}

	function getBlogSettingsGlobal($blogid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getBlogSettingsGlobal($blogid);
	}
	
	function setBlogSettingGlobal($name, $value, $blogid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::setBlogSettingGlobal($name, $value, $blogid);
	}

	function removeBlogSettingGlobal($name, $blogid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::removeBlogSettingsGlobal($name, $blogid);
	}

	// For plugin-specific use.
	function getBlogSetting($name, $default = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getBlogSetting($name, $default);
	}
	
	function setBlogSetting($name, $value) {
		requireComponent('Textcube.Function.Setting');
		return setting::setBlogSetting($name, $value);
	}
	
	function removeBlogSetting($name) {
		requireComponent('Textcube.Function.Setting');
		return setting::removeBlogSetting($name);
	}
	
	// For User
	function getUserSetting($name, $default = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getUserSetting($name, $default);
	}

	function getUserSettingGlobal($name, $default = null, $userid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getUserSettingGlobal($name, $default, $userid);
	}
	
	function setUserSetting($name, $value) {
		requireComponent('Textcube.Function.Setting');
		return setting::setUserSetting($name, $value);
	}
	
	function setUserSettingGlobal($name, $value, $userid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::setUserSettingGlobal($name, $value, $userid);
	}
	
	function removeUserSetting($name) {
		requireComponent('Textcube.Function.Setting');
		return setting::removeUserSetting($name);
	}

	function removeUserSettingGlobal($name, $userid = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::removeUserSettingGlobal($name, $userid);
	}

	function getServiceSetting($name, $default = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getServiceSetting($name, $default);
	}

	function setServiceSetting($name, $value) {
		requireComponent('Textcube.Function.Setting');
		return setting::setServiceSetting($name);
	}

	function removeServiceSetting($name) {
		requireComponent('Textcube.Function.Setting');
		return setting::removeServiceSetting($name);
	}
	
	function getBlogSettingRowsPerPage($default = null) {
		requireComponent('Textcube.Function.Setting');
		return setting::getBlogSettingRowsPerPage($default);
	}

	function setBlogSettingRowsPerPage($value) {
		requireComponent('Textcube.Function.Setting');
		return setting::setBlogSettingRowsPerPage($value);
	}
}
?>
