<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

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
global $context, $uri;
//global $config, $context, $uri;

/// Loading configuration	
$context = Model_Context::getInstance(); // automatic initialization via first instanciation
$config  = Model_Config::getInstance();
$uri     = Model_URIHandler::getInstance();
/// Loading debug module
if($context->getProperty('service.debugmode') == true) {
	if(!is_null($context->getProperty('database.dbms'))) {
		require_once(ROOT. "/framework/data/".$context->getProperty('database.dbms')."/Debug.php");
	} else require_once(ROOT. "/framework/data/MySQL/Debug.php");
} else {
	if(!function_exists('dumpAsFile')) {function dumpAsFile($dummy){return true;}}
}
    
/** LOAD : Required components / models / views 
    -------------------------------------------
    include.XXXX contains necessary file list. (XXXX : blog, owner, reader, feeder, icon)
    Loading files from the file list.
*/

/// Reading necessary file list
require_once (ROOT.'/library/include.'.$uri->uri['interfaceType'].'.php');
/// Loading files.
require_once (ROOT.'/library/include.php');

/** INITIALIZE : Sending header 
    ---------------------------
*/
if(!defined('__TEXTCUBE_CUSTOM_HEADER__')) {
	if(defined('__TEXTCUBE_HEADER_XML__')) {
		header('Content-Type: text/xml; charset=utf-8');
	} else {
		header('Content-Type: text/html; charset=utf-8');
	}
}

/** INITIALIZE : Database I/O
    -------------------------
    Performs database connection.
*/
if(!is_null($context->getProperty('database.database'))) {
	$context->useNamespace('database');
	$db['database'] = $context->getProperty('database');
	$db['server']   = $context->getProperty('server');
	$db['port']     = $context->getProperty('port');
	$db['username'] = $context->getProperty('username');
	$db['password'] = $context->getProperty('password');
	$context->useNamespace();
	if(POD::bind($db) === false) {
		Respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
		exit;
	}
	POD::cacheLoad();
	register_shutdown_function( array('POD','cacheSave') );
}
$database['utf8'] = (POD::charset() == 'utf8') ? true : false;
/// Memcache module bind (if possible)
global $memcache;
$memcache = null;
if($context->getProperty('service.memcached') == true): 
	$memcache = new Memcache;
	$memcache->connect((!is_null($context->getProperty('memcached.server')) ? $context->getProperty('memcached.server') : 'localhost'));
endif;

/** INITIALIZE : URI Parsing and specify parameters
    -----------------------------------------------
    Textcube judges blogid from its URI.
    After parsing URI-specific variables, fetch global variables (legacy support till Textcube 2)
*/
$uri = Model_URIHandler::getInstance();

$uri->URIParser();
$uri->VariableParser();

/// Setting global variables
//if($context->getProperty('service.legacyMode') == true) {
	$legacy = Model_LegacySupport::getInstance();
	$legacy->addSupport('URLglobals');
//}

/** INITIALIZE : Session (if necessary)
    -----------------------------------
*/
if (!defined('NO_SESSION')) {
	session_name(Session::getName());
	Session::set();
	session_set_save_handler( array('Session','open'), array('Session','close'), array('Session','read'), array('Session','write'), array('Session','destroy'), array('Session','gc') );
	session_cache_expire(1);
	session_set_cookie_params(0, '/', $context->getProperty('service.session_cookie_domain'));
	// Workaround for servers that modifies session cookie to its own way
	$sess_cookie_params = session_get_cookie_params();
	$context->setProperty('service.session_cookie_domain',$sess_cookie_params['domain']);
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
	if(!is_null($context->getProperty('database.database'))) {
		$timezone = new Timezone;
		$timezone->set($context->getProperty('blog.timezone') !== null ? $context->getProperty('blog.timezone') : $context->getProperty('service.timezone'));
		POD::setTimezone($context->getProperty('blog.timezone') !== null ? $context->getProperty('blog.timezone') : $context->getProperty('service.timezone'));
	}
/** Locale Resources
    ----------------
    Loads necessary locale resource. 
    (TODO : Reduce the capacity of i18n resource by dividing blog / adminpanel setting.
*/
	
/// Load administration panel locale.
	if(!defined('NO_LOCALE')) {
		if($context->getProperty('uri.interfaceType') == 'reader') { $languageDomain = 'owner'; }
		else $languageDomain = $context->getProperty('uri.interfaceType');
		if($languageDomain == 'owner') {
			$language = $context->getProperty('blog.language') !== null ?  $context->getProperty('blog.language') : $context->getProperty('service.language');
		} else {
			$language = $context->getProperty('blog.blogLanguage') !== null ?  $context->getProperty('blog.blogLanguage') : $context->getProperty('service.language');
		}
		$locale = Locales::getInstance();
		$locale->setDirectory(ROOT . '/resources/locale/'.$languageDomain);
		$locale->set($language,$languageDomain);
		$locale->setDomain($languageDomain);
		$locale->setDefaultLanguage($language);
		unset($languageDomain);
		unset($language);
	}
	
/** Administration panel skin / editor template
    -------------------------------------------
    When necessary, loads admin panel skin information.
*/
	if(in_array($context->getProperty('uri.interfaceType'), array('owner','reader')) || defined('__TEXTCUBE_ADMINPANEL__')) {
		$adminSkinSetting = array();
		
		/// TODO : This is a test routine. we should abstract this.
		$browser = Utils_Browser::getInstance();
		if($browser->isMobile()) {
			$adminSkinSetting['skin'] = "/skin/admin/mobile";
		} else {

			if(!is_null($context->getProperty('service.adminskin'))) {
				$adminSkinSetting['skin'] = "/skin/admin/".$context->getProperty('service.adminskin');
			} else {
				$adminSkinSetting['skin'] = "/skin/admin/".Setting::getBlogSettingGlobal("adminSkin", "whitedream");
			}
		}
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
if(in_array($context->getProperty('uri.interfaceType'), array('blog','owner','reader','mobile'))) {
	require_once(ROOT.'/library/plugins.php');
}

/** INITIALIZE : Access privilege Check 
    -----------------------------------
    Checks privilege setting and block user (or connection).
*/
if($context->getProperty('uri.interfaceType') == 'blog' && !defined('__TEXTCUBE_LOGIN__')) {
	$blogVisibility = $context->getProperty('blog.visibility',2);
//	$blogVisibility = Setting::getBlogSettingGlobal('visibility',2);
	if($context->getProperty('service.requirelogin',false) == true) {
		if($blogVisibility == 0) requireOwnership();
		else requireMembership();
	} else {
		if($blogVisibility == 0) requireOwnership();
		else if($blogVisibility == 1) requireMembership();
	}
}

if(in_array($context->getProperty('uri.interfaceType'), array('owner','reader'))) {
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
