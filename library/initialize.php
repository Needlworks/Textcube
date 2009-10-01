<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Database I/O initialization.
   ---------------------------
   - Depends on /library/database.php 
   - Choose DBMS and bind database.*/
   
requireComponent('Needlworks.Database');
if(!empty($database) && !empty($database["database"])) {
	if(POD::bind($database) === false) {
		respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
		exit;
	}
}

$database['utf8'] = (POD::charset() == 'utf8') ? true : false;

global $memcache;
$memcache = null;
/* Path-dependent environment setting
   ----------------------------------
   */
require ROOT.'/library/suri.php';

/* Session initializing */
if (!defined('NO_SESSION')) {
	if (isset($service['memcached']) && $service['memcached'] == true) {
		$memcache = new Memcache;
		$memcache->connect((isset($memcached['server']) && $memcached['server'] ? $memcached['server'] : 'localhost'));
		require_once ROOT.'/library/session.memcached.php';
	} else
		require_once ROOT.'/library/session.php';
	startSession();
}
if (!defined('NO_INITIALIZAION')) {
	/* Get User information */
	if (doesHaveMembership()) {
		$user = array('id' => getUserId());
		$user['name'] = User::getName(getUserId());
		$user['homepage'] = User::getHomePage();
	} else {
		$user = null;
	}

	/* Locale initialization
   ---------------------
   - Depends on /library/locale.php 
   - Set current locale, load locale and language resources. */
  
	$__locale = array(
		'locale' => null,
		'directory' => './locale',
		'domain' => null,
		);

	// Set timezone.
	if(isset($database) && !empty($database['database'])) {
		Timezone::set(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
		POD::setTimezone(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
	}

	// Load administration panel locale.
	// TODO : po지원하도록 변경해야 함.
	if(!defined('NO_LOCALE')) {
		Locale::setDirectory(ROOT . '/language');
		Locale::set(isset($blog['language']) ? $blog['language'] : $service['language']);

		// Load blog screen locale.
		if (!isset($blog['blogLanguage'])) {
			$blog['blogLanguage'] = $service['language'];
		}
		Locale::setSkinLocale(isset($blog['blogLanguage']) ? $blog['blogLanguage'] : $service['language']);
	}

/* Administration panel skin and editor template initialization
   ---------------------
   - Set administration panel skin and editor template CSS. */

	// 관리 모드 스킨 및 에디터 템플릿 설정.
	if(defined('__TEXTCUBE_ADMINPANEL__')) {
		$adminSkinSetting = array();
		$adminSkinSetting['skin'] = "/style/admin/".getBlogSetting("adminSkin", "whitedream");
		// 1.5에서 올라온 경우 스킨이 있는 경우를 위한 workaround.
		if($adminSkinSetting['skin'] == '/style/admin/default') {
			setBlogSetting("adminSkin", "whitedream");
			$adminSkinSetting['skin'] = "/style/admin/whitedream";
		}

		// content 본문에 removeAllTags()가 적용되는 것을 방지하기 위한 프로세스를 위한 변수.
		$contentContainer = array();

		if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/wysiwyg.css"))
			$adminSkinSetting['editorTemplate'] = "/skin/{$skinSetting['skin']}/wysiwyg.css";
		else
			$adminSkinSetting['editorTemplate'] = "/style/default-wysiwyg.css";
	}
	if (!file_exists(ROOT . '/config.php')) {
		header('Location: ' . ROOT . '/setup.php');
		exit;
	}
}
?>