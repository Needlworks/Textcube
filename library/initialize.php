<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


/**
      Memcache module bind (if possible)
	--------------------------------------
**/
$memcache = null;
if(!empty($database) && !empty($service['memcached']) && $service['memcached'] == true): 
	$memcache = new Memcache;
	$memcache->connect((isset($memcached['server']) && $memcached['server'] ? $memcached['server'] : 'localhost'));
endif;

/* Get User information */
if (!defined('NO_INITIALIZAION')) {
	if (doesHaveMembership()) {
		$user             = array('id' => getUserId());
		$user['name']     = User::getName(getUserId());
		$user['homepage'] = User::getHomePage();
	} else {
		$user = null;
	}

/**
     Locale initialization
   -------------------------
   - Depends on /library/locale.php 
   - Set current locale, load locale and language resources. 
**/
  
	$__locale = array(
		'locale' => null,
		'directory' => './locale',
		'domain' => null,
		);

	// Set timezone.
	if(isset($database) && !empty($database['database'])) {
		$timezone = new Timezone;
		$timezone->set(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
		POD::query('SET time_zone = \'' . $timezone->getCanonical() . '\'');
	}

	// Load administration panel locale.
	// TODO : po지원하도록 변경해야 함.
	if(!defined('NO_LOCALE')) {
		Locale::setDirectory(ROOT . '/resources/language');
		Locale::set(isset($blog['language']) ? $blog['language'] : $service['language']);

		// Load blog screen locale.
		if (!isset($blog['blogLanguage'])) {
			$blog['blogLanguage'] = $service['language'];
		}
		Locale::setSkinLocale(isset($blog['blogLanguage']) ? $blog['blogLanguage'] : $service['language']);
	}

/** 
     Administration panel skin / editor template initialization
   --------------------------------------------------------------
   - Set administration panel skin and editor template CSS.
**/

	// 관리 모드 스킨 및 에디터 템플릿 설정.
	if(defined('__TEXTCUBE_ADMINPANEL__')) {
		$adminSkinSetting = array();
		$adminSkinSetting['skin'] = "/skin/admin/".getBlogSetting("adminSkin", "canon");
		// 1.5에서 올라온 경우 스킨이 있는 경우를 위한 workaround.
/*		if(($adminSkinSetting['skin'] == '/skin/admin/default') ||
		 ($adminSkinSetting['skin'] == '/skin/admin/whitedream')) {
			setBlogSetting("adminSkin", "canon");
			$adminSkinSetting['skin'] = "/skin/admin/canon";
		}*/

		// content 본문에 removeAllTags()가 적용되는 것을 방지하기 위한 프로세스를 위한 변수.
		$contentContainer = array();

		if (file_exists(ROOT . "/skin/blog/{$skinSetting['skin']}/wysiwyg.css"))
			$adminSkinSetting['editorTemplate'] = "/skin/blog/{$skinSetting['skin']}/wysiwyg.css";
		else
			$adminSkinSetting['editorTemplate'] = "/resources/style/default-wysiwyg.css";
	}
}
?>
