<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/** Pre-processor 
    -------------
    * Performs Variable validation 
    * Loads components and models
    * Initialization
    * Checks privilege 
*/
foreach (new DirectoryIterator(ROOT.'/framework/boot') as $fileInfo) {
	if($fileInfo->isFile()) require_once($fileInfo->getPathname());
}

/** CHECK : Basic POST/GET variable validation. 
    -------------------------------------------
    Drops not allowed variables. 
*/
$valid = true;
if (isset($IV)) $valid = $valid && Validator::validate($IV);

/// Basic SERVER variable validation to prevent hijacking possibility.
$basicIV = array(
	'SCRIPT_NAME' => array('string'),
	'REQUEST_URI' => array('string'),
	'REDIRECT_URL' => array('string', 'mandatory' => false)
);
$valid = $valid && Validator::validateArray($_SERVER, $basicIV);

/// Basic URI information validation. (you can skip this part.)
if(isset($URLInfo)) {
	$URLInfo['fullpath'] = urldecode($URLInfo['fullpath']);
	$basicIV = array(
		'fullpath' => array('string'),
		'input'    => array('string'),
		'position' => array('string'),
		'root'     => array('string'),
		'input'    => array('string', 'mandatory' => false)
	);
	$valid = $valid && Validator::validateArray($URLInfo, $basicIV);
}

/// Basic URI information validation.
if (!$valid) {
	header('HTTP/1.1 404 Not Found');
	exit;
}

/** LOAD : Basic Components
    --------------------
    Loads singleton base class and autoloader.
*/

/** LOAD : Configuration and Debug module (if necessary)
    --------------------
*/
global $config, $context;

/// Loading configuration	
$config = Config::getInstance();
$context = Context::getInstance(); // automatic initialization via first instanciation

/// Loading debug module
if($config->service['debugmode'] == true) {
	if(isset($config->database['dbms'])) {
		require_once(ROOT. "/framework/legacy/Needlworks.Debug.".$config->database['dbms'].".php");
	} else require_once(ROOT. "/framework/legacy/Needlworks.Debug.MySQL.php");
}
    
/** LOAD : Required components / models / views 
    -------------------------------------------
    include.XXXX contains necessary file list. (XXXX : blog, owner, reader, feeder, icon)
    Loading files from the file list.
*/

/// Reading necessary file list
require_once (ROOT.'/library/include.'.$context->URLInfo['interfaceType'].'.php');
/// Loading files.
require_once (ROOT.'/library/include.php');

/** INITIALIZE : Sending header 
    ---------------------------
*/
header('Content-Type: text/html; charset=utf-8');

/** INITIALIZE : Database I/O
    -------------------------
    Performs database connection.
*/
if(!empty($config->database) && !empty($config->database["database"])) {
	if(POD::bind($config->database) === false) {
		Respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
		exit;
	}
}
$database['utf8'] = (POD::charset() == 'utf8') ? true : false;
/// Memcache module bind (if possible)
global $memcache;
$memcache = null;
if(!empty($config->database) && !empty($config->service['memcached']) && $config->service['memcached'] == true): 
	$memcache = new Memcache;
	$memcache->connect((isset($memcached['server']) && $memcached['server'] ? $memcached['server'] : 'localhost'));
endif;

/** INITIALIZE : URI Parsing and specify parameters
    -----------------------------------------------
    Textcube judges blogid from its URI.
    After parsing URI-specific variables, fetch global variables (legacy support till Textcube 2)
*/
$context->URIParser();
/// Setting global variables
$context->globalVariableParser();
/** INITIALIZE : Session (if necessary)
    -----------------------------------
*/
if (!defined('NO_SESSION')) {
	session_name(Session::getName());
	Session::set();
	session_set_save_handler( array('Session','open'), array('Session','close'), array('Session','read'), array('Session','write'), array('Session','destroy'), array('Session','gc') );
	session_cache_expire(1);
	session_set_cookie_params(0, '/', $config->service['domain']);
	if (session_start() !== true) {
		header('HTTP/1.1 503 Service Unavailable');
		exit;
	}
}

/** INITIALIZE
    ----------
*/
if (!defined('NO_INITIALIZAION')) {
/** User information 
    ----------------
    If connection is authenticated, load user information.
*/
	if (doesHaveMembership()) {
		$user = array('id' => getUserId());
		$user['name'] = User::getName(getUserId());
		$user['homepage'] = User::getHomePage();
	} else {
		$user = null;
	}
	
/** Timezone
    --------
    Blog-specific Timezone setting.
*/
	if(isset($config->database) && !empty($config->database['database'])) {
		$timezone = new Timezone;
		$timezone->set(isset($blog['timezone']) ? $blog['timezone'] : $config->service['timezone']);
		POD::setTimezone(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
	}
/** Locale Resources
    ----------------
    Loads necessary locale resource. 
    (TODO : Reduce the capacity of i18n resource by dividing blog / adminpanel setting.
*/
	$__locale = array(
		'locale' => null,
		'directory' => '/resources/locale',
		'domain' => null,
		);
	
/// Load administration panel locale.
	if(!defined('NO_LOCALE')) {
		if($context->URLInfo['interfaceType'] == 'reader') { $languageMode = 'owner'; }
		else $languageMode = $context->URLInfo['interfaceType'];
		Locale::setDirectory(ROOT . '/resources/locale');
		Locale::set(isset($blog['language']) ? $blog['language'] : $service['language'],$languageMode);
		unset($languageMode);
	
		// Load blog screen locale.
		if (!isset($blog['blogLanguage'])) {
			$blog['blogLanguage'] = $service['language'];
		}
		Locale::setSkinLocale(isset($blog['blogLanguage']) ? $blog['blogLanguage'] : $service['language']);
	}
	
/** Administration panel skin / editor template
    -------------------------------------------
    When necessary, loads admin panel skin information.
*/
	if(in_array($context->URLInfo['interfaceType'], array('owner','reader')) || defined('__TEXTCUBE_ADMINPANEL__')) {
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
	
/** INITIALIZE : Plugin module (if necessary)
    -------------------------------------------
    Load and bind specific plugin codes and initialze them.
*/ 
if(in_array($context->URLInfo['interfaceType'], array('blog','owner','reader'))) {
	require_once(ROOT.'/library/plugins.php');
}

/** INITIALIZE : Access privilege Check 
    -----------------------------------
    Checks privilege setting and block user (or connection).
*/

if($context->URLInfo['interfaceType'] == 'blog' && !defined('__TEXTCUBE_LOGIN__')) {
	$blogVisibility = Setting::getBlogSettingGlobal('visibility',2);
	if($blogVisibility == 0) requireOwnership();
	else if($blogVisibility == 1) requireMembership();
}

if(in_array($context->URLInfo['interfaceType'], array('owner','reader'))) {
	requireOwnership();     // Check access control list
	if(!empty($_SESSION['acl'])) {
		$requiredPriv = Aco::getRequiredPrivFromUrl( $suri['directive'] );
		if( !empty($requiredPriv) && !Acl::check($requiredPriv) ) {
			if( in_array( 'group.administrators', $requiredPriv ) ) {
				header("location:".$blogURL ."/owner/center/dashboard"); exit;
			} else {
				header("location:".$blogURL ."/owner/entry"); exit;
			}
		}
	
	}
}
?>
