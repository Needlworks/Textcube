<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Pre-processor
 * -------------
 * Performs Variable validation
 * Loads components and models
 * Initialization
 * Checks privilege
 */
$bootFiles = array();    // From PHP 5.3, DirectoryIterator does not gurantee the order.
foreach (new DirectoryIterator(ROOT . '/framework/boot') as $fileInfo) {
    if ($fileInfo->isFile() && substr($fileInfo->getBasename(), -3) == 'php') {
        array_push($bootFiles, $fileInfo->getPathname());
    }
}
sort($bootFiles);
foreach ($bootFiles as $bf) {
    require_once($bf);
}
unset($bootFiles);

/** CHECK : Basic POST/GET variable validation.
 * -------------------------------------------
 * Drops not allowed variables.
 */
$valid = true;
if (isset($IV)) {
    $valid = $valid && Validator::validate($IV);
}

/// Basic SERVER variable validation to prevent hijacking possibility.
$basicIV = array(
    'SCRIPT_NAME' => array('string'),
    'REQUEST_URI' => array('string'),
    'REDIRECT_URL' => array('string', 'mandatory' => false)
);
$valid = $valid && Validator::validateArray($_SERVER, $basicIV);

/// Basic URI information validation. (you can skip this part.)
if (isset($URLInfo)) {
    $URLInfo['fullpath'] = urldecode($URLInfo['fullpath']);
    $basicIV = array(
        'fullpath' => array('string'),
        'input' => array('string'),
        'position' => array('string'),
        'root' => array('string'),
        'input' => array('string', 'mandatory' => false)
    );
    $valid = $valid && Validator::validateArray($URLInfo, $basicIV);
}

/// Basic URI information validation.
if (!$valid) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

/** LOAD : Basic Components
 * --------------------
 * Loads singleton base class and autoloader.
 */

/** LOAD : Configuration and Debug module (if necessary)
 * --------------------
 */
global $context, $uri;
//global $config, $context, $uri;

/// Loading configuration
$context = Model_Context::getInstance(); // automatic initialization via first instanciation
$config = Model_Config::getInstance();
$uri = Model_URIHandler::getInstance();
/// Setting basic paths
$predefinedPaths = array(
    '__TEXTCUBE_CONFIG_FILE__'=> ROOT . '/config.php',
    '__TEXTCUBE_CACHE_DIR__'=> ROOT . '/user/cache',
    '__TEXTCUBE_ATTACH_DIR__'=>ROOT . '/user/attach'
);
foreach ($predefinedPaths as $symbol=>$location) {
    if(!defined($symbol)) {
        define($symbol, $location);
    }
}
/// Loading debug module
if ($context->getProperty('service.debugmode') == true) {
    require_once(ROOT . "/library/debug.php");
    if (!is_null($context->getProperty('database.dbms'))) {
        require_once(ROOT . "/framework/data/" . $context->getProperty('database.dbms') . "/Debug.php");
    } else {
        require_once(ROOT . "/framework/data/MySQL/Debug.php");
    }
} else {
    if (!function_exists('dumpAsFile')) {
        function dumpAsFile($dummy) {
            return true;
        }
    }
}

/** INITIALIZE : Sending header
 * ---------------------------
 */
if (!defined('__TEXTCUBE_CUSTOM_HEADER__')) {
    if (defined('__TEXTCUBE_HEADER_XML__')) {
        header('Content-Type: text/xml; charset=utf-8');
    } else {
        header('Content-Type: text/html; charset=utf-8');
    }
}
// Enabling HSTS when SSL is enabled.
if ($context->getProperty('service.useSSL',false) == true) {
	header("strict-transport-security: max-age=".$context->getProperty("service.timeout",3600));
}

/** INITIALIZE : Database I/O
 * -------------------------
 * Performs database connection.
 */
if (!is_null($context->getProperty('database.database'))) {
    $context->useNamespace('database');
    $db['database'] = $context->getProperty('database');
    $db['server'] = $context->getProperty('server');
    $db['port'] = $context->getProperty('port');
    $db['username'] = $context->getProperty('username');
    $db['password'] = $context->getProperty('password');
    $context->useNamespace();
    if (POD::bind($db) === false) {
        Respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
        exit;
    }
    POD::cacheLoad();
    register_shutdown_function(array('POD', 'cacheSave'));
    $context->setProperty('database.connected', true);
    //register_shutdown_function( array('POD','unbind') );
}
$database['utf8'] = (POD::charset() == 'utf8') ? true : false;
/// Memcache module bind (if possible)
global $memcache;
$memcache = null;
if ($context->getProperty('service.memcached') == true):
    $memcache = new Memcache;
    $memcache->connect((!is_null($context->getProperty('memcached.server')) ? $context->getProperty('memcached.server') : 'localhost'));
endif;

/** INITIALIZE : URI Parsing and specify parameters
 * -----------------------------------------------
 * Textcube judges blogid from its URI.
 * After parsing URI-specific variables, fetch global variables (legacy support till Textcube 2)
 */
$__requireComponent = array(
    'Textcube.Core',
    'Needlworks.Cache.PageCache');
foreach ($__requireComponent as $lib) {
    require ROOT . '/framework/legacy/' . $lib . '.php';
}

$uri = Model_URIHandler::getInstance();
$uri->URIParser();
$uri->VariableParser(); // Now DB-stored variables are loaded.

/** LOAD : Required components / models / views
 * -------------------------------------------
 * include.XXXX contains necessary file list. (XXXX : blog, owner, reader, feeder, icon)
 * Loading files from the file list.
 */
/// Override mobile mode call
$browserUtil = Utils_Browser::getInstance();
if ($context->getProperty('blog.useiPhoneUI', true) && ($browserUtil->isMobile() == true)
    && (!isset($_GET['mode']) || $_GET['mode'] != 'desktop')
    && (!isset($_SESSION['mode']) || !in_array($_SESSION['displayMode'], array('desktop')))
) {
    $context->setProperty('blog.displaymode', 'mobile');
    if ($uri->uri['interfaceType'] == 'blog') {
        $uri->uri['interfaceType'] = 'mobile';
    }
    define('__TEXTCUBE_IPHONE__', true);    // Legacy flag for plugins
    $_SESSION['displaymode'] = 'mobile';
    define('__TEXTCUBE_SKIN_DIR__', ROOT . '/skin/default');
    if (!defined('__TEXTCUBE_SKIN_STORAGE__')) {
        define('__TEXTCUBE_SKIN_CUSTOM_DIR__',__TEXTCUBE_SKIN_DIR__.'/customize');
    } else {
        define('__TEXTCUBE_SKIN_CUSTOM_DIR__',__TEXTCUBE_SKIN_STORAGE__.'/default/customize');
    }
} else {
    $_SESSION['displaymode'] = 'desktop';
    if(!defined('__TEXTCUBE_SKIN_DIR__')) {
        define('__TEXTCUBE_SKIN_DIR__', ROOT . '/skin/blog');
    }
    if (!defined('__TEXTCUBE_SKIN_STORAGE__')) {
		define('__TEXTCUBE_SKIN_CUSTOM_DIR__',__TEXTCUBE_SKIN_DIR__.'/customize');
	} else {
		define('__TEXTCUBE_SKIN_CUSTOM_DIR__',__TEXTCUBE_SKIN_STORAGE__.'/blog/customize');
	}
    $context->setProperty('blog.displaymode', 'desktop');
}
/// Reading necessary file list
require_once(ROOT . '/library/include.' . $uri->uri['interfaceType'] . '.php');
/// Loading files.
require_once(ROOT . '/library/include.php');

/// Delayed default skin change. (after including necessary modules.)
if ($context->getProperty('blog.displaymode', 'desktop') == 'mobile') {
    $context->setProperty('skin.skin', 'lucid');
}
if ($browserUtil->isMobile() == true) {
    $context->setProperty('blog.workmode', 'standard');
    $context->setProperty('blog.displaymode', 'mobile');
} else {
    $context->setProperty('blog.workmode', 'enhanced');
}
/// Setting global variables
if ($context->getProperty('service.legacymode') == true) {
    $legacy = Model_LegacySupport::getInstance();
    $legacy->addSupport('URLglobals');
}

/** INITIALIZE : Session (if necessary)
 * -----------------------------------
 */
if (!defined('NO_SESSION')) {
    session_name(Session::getName());
    Session::set();
    session_set_save_handler(array('Session', 'open'), array('Session', 'close'), array('Session', 'read'), array('Session', 'write'), array('Session', 'destroy'), array('Session', 'gc'));
    session_cache_expire(1);
    session_set_cookie_params(0, '/', $context->getProperty('service.session_cookie_domain'));
    // Workaround for servers that modifies session cookie to its own way
    $sess_cookie_params = session_get_cookie_params();
    $context->setProperty('service.session_cookie_domain', $sess_cookie_params['domain']);
    register_shutdown_function('session_write_close');
    if (session_start() !== true) {
        header('HTTP/1.1 503 Service Unavailable');
        exit;
    }
}

/** INITIALIZE
 * ----------
 */
if (!defined('NO_INITIALIZAION')) {
    /** User information
     * ----------------
     * If connection is authenticated, load user information.
     */
    if (doesHaveMembership()) {
        $user = array('id' => getUserId());
        $user['name'] = User::getName(getUserId());
        $user['homepage'] = User::getHomePage();
    } else {
        $user = null;
    }

    /** Timezone
     * --------
     * Blog-specific Timezone setting.
     */
    if (!is_null($context->getProperty('database.database'))) {
        $timezone = new Timezone;
        $timezone->set($context->getProperty('blog.timezone') !== null ? $context->getProperty('blog.timezone') : $context->getProperty('service.timezone'));
        POD::setTimezone($context->getProperty('blog.timezone') !== null ? $context->getProperty('blog.timezone') : $context->getProperty('service.timezone'));
    }
    /** Locale Resources
     * ----------------
     * Loads necessary locale resource.
     * (TODO : Reduce the capacity of i18n resource by dividing blog / adminpanel setting.
     */

/// Load administration panel locale.
    if (!defined('NO_LOCALE')) {
        if ($context->getProperty('uri.interfaceType') == 'reader') {
            $languageDomain = 'owner';
        } else {
            $languageDomain = $context->getProperty('uri.interfaceType');
        }
        if ($languageDomain == 'owner') {
            $language = $context->getProperty('blog.language') !== null ? $context->getProperty('blog.language') : $context->getProperty('service.language');
        } else {
            $language = $context->getProperty('blog.blogLanguage') !== null ? $context->getProperty('blog.blogLanguage') : $context->getProperty('service.language');
        }
        $locale = Locales::getInstance();
        $locale->setDirectory(ROOT . '/resources/locale/' . $languageDomain);
        $locale->set($language, $languageDomain);
        $locale->setDomain($languageDomain);
        $locale->setDefaultLanguage($language);
        unset($languageDomain);
        unset($language);
    }
    /** Resource Options
     * ----------------
     * Determine the resource URLs and paths.
     */
    if (is_null($context->getProperty('service.jqueryURL'))) {
        $context->setProperty('service.jqueryURL', $context->getProperty('service.path') . "/resources/script/jquery/");
    }
    /** Administration panel skin / editor template
     * -------------------------------------------
     * When necessary, loads admin panel skin information.
     */
    if (in_array($context->getProperty('uri.interfaceType'), array('owner', 'reader')) || defined('__TEXTCUBE_ADMINPANEL__')) {
        $adminSkinSetting = array();

        /// TODO : This is a test routine. we should abstract this.
        $browser = Utils_Browser::getInstance();
        if ($browser->isMobile()) {
            $context->setProperty('panel.skin', "/skin/admin/mobile");
        } else {
            if (!is_null($context->getProperty('service.adminskin'))) {
                $context->setProperty('panel.skin', "/skin/admin/" . $context->getProperty('service.adminskin'));
            } else {
                $context->setProperty('panel.skin', "/skin/admin/" . Setting::getBlogSettingGlobal("adminSkin", "canon"));
            }
        }
        // content 본문에 removeAllTags()가 적용되는 것을 방지하기 위한 프로세스를 위한 변수.
        $contentContainer = array();

        if (file_exists(__TEXTCUBE_SKIN_DIR__ . "/" . $context->getProperty('skin.skin') . "/wysiwyg.css")) {
            $context->setProperty('panel.editorTemplate', "/skin/blog/" . $context->getProperty('skin.skin') . "/wysiwyg.css");
        } else {
            $context->setProperty('panel.editorTemplate', "/resources/style/default-wysiwyg.css");
        }
    }
}

/** INITIALIZE : Plugin module (if necessary)
 * -------------------------------------------
 * Load and bind specific plugin codes and initialze them.
 */
if (in_array($context->getProperty('uri.interfaceType'), array('blog', 'owner', 'reader'))) {
    require_once(ROOT . '/library/plugins.php');
}

/** INITIALIZE : Access privilege Check
 * -----------------------------------
 * Checks privilege setting and block user (or connection).
 */
if ($context->getProperty('uri.interfaceType') == 'blog' && !defined('__TEXTCUBE_LOGIN__')) {
    $blogVisibility = $context->getProperty('blog.visibility', 2);
//	$blogVisibility = Setting::getBlogSettingGlobal('visibility',2);
    if ($context->getProperty('service.requirelogin', false) == true) {
        if ($blogVisibility == 0) {
            requireOwnership();
        } else {
            requireMembership();
        }
    } else {
        if ($blogVisibility == 0) {
            requireOwnership();
        } else {
            if ($blogVisibility == 1) {
                requireMembership();
            }
        }
    }
}
if (in_array($context->getProperty('uri.interfaceType'), array('owner', 'reader'))) {
    requireOwnership();     // Check access control list
    if (!empty($_SESSION['acl'])) {
        $requiredPriv = Aco::getRequiredPrivFromUrl($context->getProperty('suri.directive'));
        if (!empty($requiredPriv) && !Acl::check($requiredPriv)) {
            if (in_array('group.administrators', $requiredPriv)) {
                header("location:" . $context->getProperty('uri.blog') . "/owner/center/dashboard");
                exit;
            } else {
                header("location:" . $context->getProperty('uri.blog') . "/owner/entry");
                exit;
            }
        }

    }
}

/** INITIALIZE : Cookie prefix
 * -----------------------------------
 * Determines cookie prefix.
 */
if ($context->getProperty('service.cookie_prefix', '') == '') {
    $context->setProperty('service.cookie_prefix', 'Textcube' . str_replace('.', '', TEXTCUBE_VERSION_ID));
}

// DBMS unbind should work after session close.
if ($context->getProperty('database.connected') == true) {
    register_shutdown_function(array('POD', 'unbind'));
}
?>
