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

	// For Blog-scope setting
	function getBlogSettingGlobal($name, $default = null, $blogid = null) {
		$settings = misc::getBlogSettingsGlobal(($blogid == null ? getBlogId() : $blogid)); 
		if ($settings === false) return $default;
		if( isset($settings[$name]) ) {
			return $settings[$name];
		}
		return $default;
	}

	function getBlogSettingsGlobal($blogid = null) {
		global $database, $service;
		global $__gCacheBlogSettings;
		if(is_null($blogid)) $blogid = getBlogId();
		if (array_key_exists($blogid, $__gCacheBlogSettings)) {
			return $__gCacheBlogSettings[$blogid];
		}
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		$query->setQualifier('blogid',$blogid);
		$blogSettings = $query->getAll();
		if( $blogSettings ) {
			$result = array();
			$blogSettingFields = array();
			$defaultValues = array(
					'name'                     => '',
					'defaultDomain'            => 0,
					'title'                    => '', 
					'description'              => '', 
					'logo'                     => '', 
					'logoLabel'                => '', 
					'logoWidth'                => 0,
					'logoHeight'               => 0,
					'useSlogan'                => 1,
					'entriesOnPage'            => 10, 
					'entriesOnList'            => 10, 
					'entriesOnRSS'             => 10, 
					'publishWholeOnRSS'        => 1,
					'publishEolinSyncOnRSS'    => 1,
					'allowWriteOnGuestbook'    => 1,
					'allowWriteDblCommentOnGuestbook' => 1,
					'language'     => $service['language'],
					'blogLanguage' => $service['language'],
					'timezone'     => $service['timezone'],
					'noneCommentMessage'       => '',
					'singleCommentMessage'     => '',
					'noneTrackbackMessage'     => '',
					'singleTrackbackMessage'   => '');
			foreach($blogSettings as $blogSetting) {
				$result[$blogSetting['name']] = $blogSetting['value'];
				if(array_key_exists($blogSetting['name'],$defaultValues)) {
					array_push($blogSettingFields, $blogSetting['name']);
				}
			}
			foreach($defaultValues as $name => $value) {
				if(!in_array($name,$blogSettingFields)) {
					$result[$name] = $value;
					setBlogSettingDefault($name,$value);
				}
			}
			$__gCacheBlogSettings[$blogid] = $result;
			return $result;
		}
		$__gCacheBlogSettings[$blogid] = false;
		return false;
	}
	
	function setBlogSettingGlobal($name, $value, $blogid = null) {
		global $database;
		global $__gCacheBlogSettings;
	
		if (is_null($blogid)) $blogid = getBlogId();
		if (!is_numeric($blogid)) return null;
	
		if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
			// force loading
			misc::getBlogSettingsGlobal($blogid);
		}
		if ($__gCacheBlogSettings[$blogid] === false) {
			return null;
		}
		
		$escape_name = POD::escapeString($name);
		$escape_value = POD::escapeString($value);
		
		if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
			// overwrite value
			$__gCacheBlogSettings[$blogid][$name] = $value;
			return POD::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, '$escape_name', '$escape_value')");
		}
		
		// insert new value
		$__gCacheBlogSettings[$blogid][$name] = $value;
		return POD::execute("INSERT INTO {$database['prefix']}BlogSettings VALUES($blogid, '$escape_name', '$escape_value')");
	}

	function removeBlogSettingGlobal($name, $blogid = null) {
		global $database;
		global $__gCacheBlogSettings; // share blog.service.php
	
		if (is_null($blogid)) $blogid = getBlogId();
		if (!is_numeric($blogid)) return null;
	
		if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
			// force loading
			misc::getBlogSettingsGlobal($blogid);
		}
		if ($__gCacheBlogSettings[$blogid] === false) {
			return null;
		}
		
		$escape_name = POD::escapeString($name);
		
		if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
			// overwrite value
			unset($__gCacheBlogSettings[$blogid][$name]);
			return POD::execute("DELETE FROM {$database['prefix']}BlogSettings 
				WHERE blogid = $blogid AND name = '$escape_name'");
		}
		
		// already not exist
		return true;
	}

	// For plugin-specific use.
	function getBlogSetting($name, $default = null) {
		$settings = misc::getBlogSettingsGlobal(getBlogId()); // from blog.service.php
		if ($settings === false) return $default;
		$name = 'plugin_' . $name;
		if( isset($settings[$name]) ) {
			return $settings[$name];
		}
		return $default;
	}
	
	function setBlogSetting($name, $value) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		return misc::setBlogSettingGlobal($name, $value);
	}
	
	function removeBlogSetting($name) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		return misc::removeBlogSettingGlobal($name);
	}

	// For User
	function getUserSetting($name, $default = null) {
		global $database, $userSetting;
		$name = 'plugin_' . $name;
		return misc::getUserSettingGlobal($name, $default);
	}

	function getUserSettingGlobal($name, $default = null, $userid = null) {
		global $database, $userSetting;
		if( empty($userSetting) || !isset($userSetting[$userid])) {
			$userid = is_null($userid) ? getUserId() :  $userid;
			$settings = POD::queryAll("SELECT name, value 
					FROM {$database['prefix']}UserSettings
					WHERE userid = ".$userid, MYSQL_NUM );
			foreach( $settings as $k => $v ) {
				$userSetting[$userid][ $v[0] ] = $v[1];
			}
		}
		if( isset($userSetting[$userid][$name]) ) {
			return $userSetting[$userid][$name];
		}
		return $default;
	}
	
	function setUserSetting($name, $value) {
		global $database;
		$name = 'plugin_' . $name;
		return misc::setUserSettingGlobal($name, $value);
	}
	
	function setUserSettingGlobal($name, $value, $userid = null) {
		global $database;
		$name = POD::escapeString($name);
		$value = POD::escapeString($value);
		clearUserSettingCache();
		return POD::execute("REPLACE INTO {$database['prefix']}UserSettings VALUES(".(is_null($userid) ? getUserId() : $userid).", '$name', '$value')");
	}
	
	function removeUserSetting($name) {
		global $database;
		$name = 'plugin_' . $name;
		return misc::removeUserSettingGlobal($name);
	}

	function removeUserSettingGlobal($name, $userid = null) {
		global $database;
		clearUserSettingCache();
		return POD::execute("DELETE FROM {$database['prefix']}UserSettings WHERE userid = ".(is_null($userid) ? getUserId() : $userid)." AND name = '".POD::escapeString($name)."'");
	}

	function getServiceSetting($name, $default = null) {
		global $database;
		$name = 'plugin_' . $name;
		$value = POD::queryCell("SELECT value FROM {$database['prefix']}ServiceSettings WHERE name = '".POD::escapeString($name)."'");
		return (is_null($value)) ? $default : $value;
	}

	function setServiceSetting($name, $value) {
		global $database;
		$name = 'plugin_' . $name;
		$name = POD::escapeString(UTF8::lessenAsEncoding($name, 32));
		$value = POD::escapeString(UTF8::lessenAsEncoding($value, 255));
		return POD::execute("REPLACE INTO {$database['prefix']}ServiceSettings VALUES('$name', '$value')");
	}

	function removeServiceSetting($name) {
		global $database;
		$name = 'plugin_' . $name;
		return POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = '".POD::escapeString($name)."'");
	}
	
	function getBlogSettingRowsPerPage($default = null) {
		global $database, $blogid;
		$value = POD::queryCell("SELECT value FROM {$database['prefix']}BlogSettings WHERE blogid = $blogid AND name = 'rowsPerPage'");
		return (is_null($value)) ? $default : $value;
	}

	function setBlogSettingRowsPerPage($value) {
		global $database, $blogid;
		$value = POD::escapeString($value);
		return POD::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES($blogid, 'rowsPerPage', '$value')");
	}

	function isMetaBlog() {
		return (getBlogId() == getServiceSetting("defaultBlogId",1) ? true : false);
	}

	/* Synch with lib/view/pages.php */
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
}
?>
