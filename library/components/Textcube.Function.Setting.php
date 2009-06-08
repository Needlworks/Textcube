<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $__gCacheBlogSettings, $__serviceSetting; // share blog.service.php
$__serviceSetting = array();
class Setting {
	function fetchConfigVal( $DATA ){
		if (is_null($DATA)) return null; // Compartibility. If data is stored as array (new method), return it.
		if (is_array($DATA)) return $DATA;

		$xmls = new XMLStruct();		// else, parse them...
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
	function getBlogSettingGlobal($name, $default = null, $blogid = null, $directAccess = false) {
		global $database;
		if(is_null($blogid)) $blogid = getBlogId();
		if($directAccess == true) {
			$query = new TableQuery($database['prefix']. 'BlogSettings');
			$query->setQualifier('blogid', $blogid);
			$query->setQualifier('name',$name, true);
			return $query->getCell('value');
		}
		$settings = Setting::getBlogSettingsGlobal(($blogid == null ? getBlogId() : $blogid)); 
		if ($settings === false) return $default;
		if( isset($settings[$name]) ) {
			return $settings[$name];
		}
		return $default;
	}

	function getBlogSettingsGlobal($blogid = null) {
		global $database, $service, $__gCacheBlogSettings, $gCacheStorage;

		if(is_null($blogid)) $blogid = getBlogId();
		if (array_key_exists($blogid, $__gCacheBlogSettings) && !empty($__gCacheBlogSettings[$blogid])) {
			return $__gCacheBlogSettings[$blogid];
		}
		if($blogid == getBlogId()) {
			$result = $gCacheStorage->getContent('BlogSettings');
			if(!empty($result)) { 
				$__gCacheBlogSettings[$blogid] = $result;
				return $result;
			}
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
					'useSloganOnPost'          => 1,
					'useSloganOnCategory'      => 1,
					'useSloganOnTag'           => 1,
					'entriesOnPage'            => 10, 
					'entriesOnList'            => 10, 
					'entriesOnRSS'             => 10, 
					'commentsOnRSS'            => 10, 
					'publishWholeOnRSS'        => 1,
					'publishEolinSyncOnRSS'    => 1,
					'allowWriteOnGuestbook'    => 1,
					'allowWriteDblCommentOnGuestbook' => 1,
					'visibility'               => 2,
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
					Setting::setBlogSettingDefault($name,$value,$blogid);
				}
			}
			$__gCacheBlogSettings[$blogid] = $result;
			if($blogid == getBlogId()) $gCacheStorage->setContent('BlogSettings', $result);
			return $result;
		}
		$__gCacheBlogSettings[$blogid] = false;
		return false;
	}
	
	function setBlogSettingGlobal($name, $value, $blogid = null) {
		global $database;
		global $__gCacheBlogSettings;
		global $gCacheStorage;
	
		if (is_null($blogid)) $blogid = getBlogId();
		if (!is_numeric($blogid)) return null;
	
		if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
			// force loading
			Setting::getBlogSettingsGlobal($blogid);
		}
		if ($__gCacheBlogSettings[$blogid] === false) {
			return null;
		}
		
		$gCacheStorage->purge();
		if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
			// overwrite value
			$__gCacheBlogSettings[$blogid][$name] = $value;
			$query = new TableQuery($database['prefix'] . 'BlogSettings');
			$query->setQualifier('blogid', $blogid);
			$query->setQualifier('name', $name, true);
			$query->setAttribute('blogid', $blogid);
			$query->setAttribute('name', $name, true);
			$query->setAttribute('value',$value, true);
			return $query->replace();
		}
		
		// insert new value
		$__gCacheBlogSettings[$blogid][$name] = $value;
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		$query->setAttribute('blogid', $blogid);
		$query->setAttribute('name',$name, true);
		$query->setAttribute('value',$value, true);
		return $query->insert();
	}

	function setBlogSettingDefault($name, $value, $blogid = null) {
		global $database;
		if(is_null($blogid)) $blogid = getBlogId();
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		$query->setQualifier('blogid', $blogid);
		$query->setQualifier('name', $name, true);
		$query->setAttribute('blogid', $blogid);
		$query->setAttribute('name', $name, true);
		$query->setAttribute('value',$value, true);
		return $query->replace();
	}

	function removeBlogSettingGlobal($name, $blogid = null) {
		global $database;
		global $__gCacheBlogSettings; // share blog.service.php
		global $gCacheStorage;

		if (is_null($blogid)) $blogid = getBlogId();
		if (!is_numeric($blogid)) return null;
	
		if (!array_key_exists($blogid, $__gCacheBlogSettings)) {
			// force loading
			Setting::getBlogSettingsGlobal($blogid);
		}
		if ($__gCacheBlogSettings[$blogid] === false) {
			return null;
		}
		
		$escape_name = POD::escapeString($name);

		if (array_key_exists($name, $__gCacheBlogSettings[$blogid])) {
			// overwrite value
			$gCacheStorage->purge();
			unset($__gCacheBlogSettings[$blogid][$name]);
			$query = new TableQuery($database['prefix'] . 'BlogSettings');
			$query->setQualifier('blogid',$blogid);
			$query->setQualifier('name',$name);
			return $query->delete();
		}
		
		// already not exist
		return true;
	}

	// For plugin-specific use.
	function getBlogSetting($name, $default = null) {
		$settings = Setting::getBlogSettingsGlobal(getBlogId()); // from blog.service.php
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
		return Setting::setBlogSettingGlobal($name, $value);
	}
	
	function removeBlogSetting($name) {
		global $database, $blogid;
		$name = 'plugin_' . $name;
		return Setting::removeBlogSettingGlobal($name);
	}

	// For User
	function getUserSetting($name, $default = null, $global = null) {
		global $database, $userSetting;
		if(is_null($global)) $name = 'plugin_' . $name;
		return Setting::getUserSettingGlobal($name, $default);
	}

	function getUserSettingGlobal($name, $default = null, $userid = null, $directAccess = false) {
		global $database, $userSetting;
		if($directAccess !== false) {
			$query = new TableQuery($database['prefix'] . 'UserSettings');
			$query->setQualifier('userid',$userid);
			$query->setQualifier('name',$name, true);
			return $query->getCell('value');
		}
		if( empty($userSetting) || !isset($userSetting[$userid])) {
			$userid = is_null($userid) ? getUserId() :  $userid;
			$query = new TableQuery($database['prefix'] . 'UserSettings');
			$query->setQualifier('userid',$userid);
			$settings = $query->getAll('name, value');	
			foreach( $settings as $k => $v ) {
				$userSetting[$userid][ $v[0] ] = $v[1];
			}
		}
		if( isset($userSetting[$userid][$name]) ) {
			return $userSetting[$userid][$name];
		}
		return $default;
	}
	
	function setUserSetting($name, $value, $global = null) {
		global $database;
		if(is_null($global)) $name = 'plugin_' . $name;
		return Setting::setUserSettingGlobal($name, $value);
	}
	
	function setUserSettingGlobal($name, $value, $userid = null) {
		global $database;
		if(is_null($userid)) $userid = getUserId();
		$name = POD::escapeString($name);
		$value = POD::escapeString($value);
		clearUserSettingCache();
		$query = new TableQuery($database['prefix'] . 'UserSettings');
		$query->setQualifier('userid', $userid);
		$query->setQualifier('name', $name, true);
		$query->setAttribute('userid', $userid);
		$query->setAttribute('name', $name, true);
		$query->setAttribute('value',$value, true);
		return $query->replace();
	}
	
	function removeUserSetting($name, $global = null) {
		global $database;
		if(is_null($global)) $name = 'plugin_' . $name;
		return Setting::removeUserSettingGlobal($name);
	}

	function removeUserSettingGlobal($name, $userid = null) {
		global $database;
		clearUserSettingCache();
		$query = new TableQuery($database['prefix'] . 'UserSettings');
		$query->setQualifier('userid',(is_null($userid) ? getUserId() : $userid));
		$query->setQualifier('name',$name,true);
		return $query->delete();
	}

	function getServiceSetting($name, $default, $global = null) {
		global $database, $__serviceSetting;
		if(is_null($global)) $name = 'plugin_' . $name;
		if( empty($__serviceSetting) ) {
			$query = new TableQuery($database['prefix'] . 'ServiceSettings');
			$settings = $query->getAll('name, value');
			foreach( $settings as $k => $v ) {
				$__serviceSetting[ $v[0] ] = $v[1];
			}
		}
		if( isset($__serviceSetting[$name]) ) {
			return $__serviceSetting[$name];
		}
		return $default;
	}
		
	function setServiceSetting($name, $value, $global = null) {
		global $database, $__serviceSetting;
		if(is_null($global)) $name = 'plugin_' . $name;
		$name = UTF8::lessenAsEncoding($name, 32);
		$value = UTF8::lessenAsEncoding($value, 255);
		$query = new TableQuery($database['prefix'] . 'ServiceSettings');
		$query->setQualifier('name', $name, true);
		$query->setAttribute('name', $name, true);
		$query->setAttribute('value',$value, true);
		if(!empty($__serviceSetting)) $__serviceSetting[$name] = $value;
		return $query->replace();
	}

	function removeServiceSetting($name, $global = null) {
		global $database;
		if(is_null($global)) $name = 'plugin_' . $name;
		$name = 'plugin_' . $name;
		$query = new TableQuery($database['prefix'] . 'ServiceSettings');
		$query->setQualifier('name',$name,true);
		return $query->delete();
	}

	function getServiceSettingGlobal($name, $default = null) {
		return Setting::getServiceSetting($name, $default, true);
	}

	function setServiceSettingGlobal($name, $value) {
		return Setting::setServiceSetting($name, $value, true);
	}

	function removeServiceSettingGlobal($name) {
		return Setting::removeServiceSetting($name, true);
	}
}
?>
