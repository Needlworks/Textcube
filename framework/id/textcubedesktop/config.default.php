<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// Define basic signatures.
define('TEXTCUBE_NAME', 'TextcubeDesktop');
define('TEXTCUBE_VERSION_ID', '2.0.0 : Beta 1');
define('TEXTCUBE_REVISION', 'root-main-trunk');
define('TEXTCUBE_CODENAME', 'inquieto');
define('TEXTCUBE_VERSION', TEXTCUBE_VERSION_ID . ' : ' . TEXTCUBE_CODENAME);
define('TEXTCUBE_COPYRIGHT', 'Copyright &copy; 2004-2016. Needlworks / Tatter Network Foundation. All rights reserved. Licensed under the GPL.');
define('TEXTCUBE_HOMEPAGE', 'http://www.textcube.org/');
define('TEXTCUBE_RESOURCE_URL', 'http://resources.textcube.org/trunk');
define('TEXTCUBE_NOTICE_URL', 'http://feeds.feedburner.com/textcube/');
// Define basic definitions.
define('CRLF', "\r\n");
define('TAB', "	");
define('INT_MAX', 2147483647);
if (strstr(PHP_OS, "WIN") !== false) {
    define('DS', "\\");
} else {
    define('DS', "/");
}
// Define library specific options.
define("OPENID_LIBRARY_ROOT", ROOT . "/library/contrib/phpopenid/");
define("XPATH_LIBRARY_ROOT", ROOT . "/library/contrib/phpxpath/");
define("Auth_OpenID_NO_MATH_SUPPORT", 1);
define("OPENID_PASSWORD", "-OPENID-");

define('JQUERY_VERSION', '1.11.3.min');
define('JQUERY_BPOPUP_VERSION', '0.10.0.min');
define('JQUERY_UI_VERSION', '1.11.2.min');
define('LODASH_VERSION', '3.10.0.min');

// App-specific intialization routine.
/// Prepare Textcube App storage / cache / attachment storage.
// $_SERVER['HOMEDRIVE'] and $_SERVER['HOMEPATH'] for windows app
if (stripos(PHP_OS,'WINNT') !== false || stripos(PHP_OS,'Windows') !== false) {
    $homedir = ROOT;
    $userdir = '';
} elseif (strpos(PHP_OS,'Darwin') !== false) {
    $_SERVER['HOME'] = posix_getpwuid(posix_getuid())['dir'];
    $homedir = $_SERVER['HOME'];
    $userdir = '/Library/Application Support/Textcube';
	if (!file_exists($homedir.$userdir)) {
		mkdir($homedir.$userdir);
	}
}

$requiredStorages = array(
    '__TEXTCUBE_DATA_DIR__'=>$homedir.$userdir.'/data',
    '__TEXTCUBE_CACHE_DIR__'=>$homedir.$userdir.'/cache',
    '__TEXTCUBE_SKIN_STORAGE__'=>$homedir.$userdir.'/theme',
    '__TEXTCUBE_ATTACH_DIR__'=>$homedir.$userdir.'/attach'
);
foreach($requiredStorages as $symbol=>$dir) {
    if(!defined($symbol)) define($symbol,$dir);
}
foreach($requiredStorages as $symbol=>$dir) {
    if (!file_exists($dir)) {
		mkdir($dir);
        if ($dir == $homedir.$userdir.'/attach') {
            mkdir($homedir.$userdir.'/attach/1');
        }
    }
}
if (!file_exists($homedir.$userdir.'/data/textcube.sqlite')) {
    copy(ROOT.'/resources/setup/textcube.sqlite',$homedir.$userdir.'/data/textcube.sqlite');
}


// Define global variable for legacy support.
// This settings are set to default for configuration.
global $database, $service, $blog, $memcache;

$database['server'] = 'localhost';
$database['database'] = '';
$database['username'] = '';
$database['password'] = '';
$database['prefix'] = '';
$service['timeout'] = 3600;
$service['autologinTimeout'] = 3600 * 24 * 14;    // Automatic login for 2 weeks.
$service['type'] = 'single';
$service['domain'] = '';
$service['path'] = '';
$service['language'] = 'ko';
$service['timezone'] = 'Asia/Seoul';
$service['encoding'] = 'UTF-8';
$service['umask'] = 0;
$service['skin'] = 'periwinkle';
if (defined('__TEXTCUBE_NO_FANCY_URL__')) {
    $service['fancyURL'] = 1;
} else {
    $service['fancyURL'] = 2;
}

$service['useEncodedURL'] = false;
$service['debugmode'] = false;
$service['reader'] = true;
$service['flashclipboardpoter'] = true;
$service['allowBlogVisibilitySetting'] = true;
$service['disableEolinSuggestion'] = true;
$service['interface'] = 'simple';    // 'simple' or 'detail'. Default is 'simple' from 2.0
$service['pagecache'] = true;
$service['codecache'] = false;
$service['skincache'] = true;
$service['externalresources'] = false;
$service['favicon_daily_traffic'] = 10;
$service['flashuploader'] = true;
$service['debug_session_dump'] = false;
$service['debug_rewrite_module'] = false;
$service['useNumericURLonRSS'] = false;
$service['forceinstall'] = false;
$service['jqueryURL'] = null;    // You can change this to use external CDNs. (microsoft / google, etc..)
$service['lodashURL'] = null;    // You can change this to use external CDNs. (microsoft / google, etc..)
$service['useSSL'] = false;
$service['cookie_prefix'] = '';
//$service['adminskin'] = 'whitedream';
?>
