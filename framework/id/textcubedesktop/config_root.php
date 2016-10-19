<?php
ini_set('display_errors', 'off');
//ini_set('display_errors', 'on');$service['debugmode'] = true;
date_default_timezone_set('Asia/Seoul');
//$_SERVER['HOME'] = posix_getpwuid(posix_getuid())['dir'];
$database['server'] = 'localhost';
$database['dbms'] = 'SQLite3';
$database['database'] = 'textcube';
$database['port'] = '3306';
$database['username'] = 'textcube';
$database['password'] = 'textcube';
$database['prefix'] = 'tc_';
$service['type'] = 'single';
$service['domain'] = 'localhost';
$service['path'] = '';
$service['skin'] = 'periwinkle';
$service['favicon_daily_traffic'] = 10; // 10MB
//$serviceURL = 'http://localhost' ; // for path of Skin, plugin and etc.
//$service['reader'] = true; // Use Textcube reader. You can set it to false if you do not use Textcube reader, and want to decrease DB load.
//$service['debugmode'] = true; // uncomment for debugging, e.g. displaying DB Query or Session info
$service['pagecache'] = false; // uncomment if you want to disable page cache feature.
//$service['codecache'] = true; // uncomment if you want to enable code cache feature.
//$service['debug_session_dump'] = true; // session info debuging.
$service['debug_rewrite_module'] = true; // rewrite handling module debuging.
//$service['session_cookie_path'] = $service['path']; // for avoiding spoiling other textcube's session id sharing root.
//$service['allowBlogVisibilitySetting'] = true; // Allow service users to change blog visibility.
//$service['externalresources'] = false;  // Loads resources from external storage.
//$service['resourcepath'] = 'http://example.com/resource';	// Specify the full URI of external resource.
//$service['autologinTimeout'] = 1209600;	// Automatic login timeout (sec.)
//$service['favicon_daily_traffic'] = 10; // Set favicon traffic limitation. default is 10MB.
//$service['skincache'] = true;        // Use skin pre-fetching. Textcube will parse static elements (blog name, title…) only when you change skin. Reduces CPU loads.
//$service['cookie_prefix'] = '';        // Service cookie prefix. Default cookie prefix is Textcube_[VERSION_NUMBER].
//$database['port'] = 3639;            // Database port number
//$database['dbms'] = 'MySQLi';         // DBMS. (MySQL, MySQLi, PostgreSQL, Cubrid.)
//$service['memcached'] = true;       // Using memcache to handle session and cache
//$memcached['server'] = 'localhost';  // Where memcache server is.
//$service['requirelogin'] = false;    // Force log-in process to every blogs. (for private blog service)
//$service['jqueryURL'] = '';		// Add URL if you want to use external jquery via CDN. e.g.) Microsoft's CDN: http://ajax.aspnetcdn.com/ajax/jQuery/
//$service['lodashURL'] = '';		// Add URL if you want to use external lo-dash via CDN. e.g.) CDNJS' CDN: https://cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/
?>
