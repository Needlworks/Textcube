<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// Define basic signatures.
define('TEXTCUBE_NAME', 'Textcube');
define('TEXTCUBE_VERSION_ID', '1.10.10');
define('TEXTCUBE_REVISION', 'root-main-branch1.10-r43');
define('TEXTCUBE_CODENAME', 'Tempo primo');
define('TEXTCUBE_VERSION', TEXTCUBE_VERSION_ID.' : '.TEXTCUBE_CODENAME);
define('TEXTCUBE_COPYRIGHT', 'Copyright &copy; 2004-2016. Needlworks / Tatter Network Foundation. All rights reserved. Licensed under the GPL.');
define('TEXTCUBE_HOMEPAGE', 'http://www.textcube.org/');
define('TEXTCUBE_RESOURCE_URL', 'http://resources.textcube.org/1.10.9');
define('TEXTCUBE_NOTICE_URL','http://feeds.feedburner.com/textcube/');
define('CRLF', "\r\n");
define('TAB', "	");
define('INT_MAX',2147483647);
define('JQUERY_VERSION','1.11.2.min');
define('JQUERY_UI_VERSION','1.11.2.min');
define('JQUERY_BPOPUP_VERSION','0.10.0.min');
define('LODASH_VERSION','2.4.1.min');

if( strstr( PHP_OS, "WIN") !== false ) {
	define('DS', "\\");
} else {
	define('DS', "/");
}
define( "OPENID_LIBRARY_ROOT", ROOT . "/library/contrib/phpopenid/" );
define( "XPATH_LIBRARY_ROOT", ROOT . "/library/contrib/phpxpath/" );
define( "Auth_OpenID_NO_MATH_SUPPORT", 1 );
define( "OPENID_PASSWORD", "-OPENID-" );

// Define global variable.
global $database, $service, $blog, $memcache;

$database['server'] = 'localhost';
$database['database'] = '';
$database['username'] = '';
$database['password'] = '';
$database['prefix'] = '';
$service['timeout'] = 900;
$service['type'] = 'single';
$service['domain'] = '';
$service['path'] = '';
$service['language'] = 'ko';
$service['timezone'] = 'Asia/Seoul';
$service['encoding'] = 'UTF-8';
$service['umask'] = 0;
$service['skin'] = 'periwinkle';
if(defined('__TEXTCUBE_NO_FANCY_URL__')) $service['fancyURL'] = 1;
else $service['fancyURL'] = 2;
$service['useEncodedURL'] = false;
$service['debugmode'] = false;
$service['reader'] = true;
$service['flashclipboardpoter'] = true;
$service['allowBlogVisibilitySetting'] = true;
$service['disableEolinSuggestion'] = true;
$service['interface'] = 'detail';	// 'simple' or 'detail'. Default is 'detail'
$service['pagecache'] = true;
$service['codecache'] = false;
$service['skincache'] = true;
$service['externalresources'] = false;
$service['favicon_daily_traffic'] = 10;
$service['debug_session_dump'] = false;
$service['debug_rewrite_module'] = false;
$service['useNumericURLonRSS'] = false;
$service['forceinstall'] = false;
$service['useSSL'] = false;
$service['cookie_prefix'] = '';
//$service['adminskin'] = 'whitedream';
?>
