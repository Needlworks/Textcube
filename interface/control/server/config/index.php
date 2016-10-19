<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'allowBlogVisibility'    => array('bool'),
		'requireLogin'           => array('bool'),
		'encoding'               => array('string'),
		'faviconDailyTraffic'    => array('int'),
		'flashClipboardPoter'    => array('bool'),
		'flashUploader'          => array('bool'),
		'language'               => array('string'),
		'serviceurl'             => array('string'),
		'cookieprefix'           => array('string', 'mandatory' => false, 'default' => ''),
		'skin'                   => array('string'),
		'timeout'                => array('int'),
		'autologinTimeout'       => array('int'),
		'timezone'               => array('string'),
		'useDebugMode'           => array('bool'),
		'useEncodedURL'          => array('bool'),
		'useNumericRSS'          => array('bool'),
		'usePageCache'           => array('bool'),
		'useCodeCache'           => array('bool'),
		'useReader'              => array('bool'),
		'useRewriteDebugMode'    => array('bool'),
		'useSessionDebugMode'    => array('bool'),
		'useSkinCache'           => array('bool'),
		'useMemcached'           => array('bool'),
		'useExternalResource'    => array('bool'),
		'externalResourceURL'    => array('string', 'mandatory' => false, 'default' => '')
		)
);

require ROOT . '/library/preprocessor.php';

importlib('model.blog.service');
requireStrictRoute();
$matchTable = array(
	'timeout' => 'timeout',
	'autologinTimeout' => 'autologinTimeout',
	'skin'    =>'skin',
	'language'=>'language',
	'timezone'=>'timezone',
	'encoding'=>'encoding',
	'serviceurl'  => 'serviceURL',
	'cookieprefix'=> 'cookie_prefix',
	'usePageCache'=>'pagecache',
	'useCodeCache'=>'codecache',
	'useSkinCache'=>'skincache',
	'useMemcached'=>'memcached',
	'useReader'   =>'reader',
	'useNumericRSS'=>'useNumericRSS',
	'useEncodedURL'=>'useEncodedURL',
	'useExternalResource'=>'externalresources',
	'externalResourceURL'=>'resourcepath',
	'allowBlogVisibility'   => 'allowBlogVisibilitySetting',
	'requireLogin'          => 'requirelogin',
	'flashClipboardPoter'   => 'flashclipboardpoter',
	'flashUploader' => 'flashuploader',
	'useDebugMode'  =>'debugmode',
	'useSessionDebugMode' => 'debug_session_dump',
	'useRewriteDebugMode' => 'debug_rewrite_module',
	'faviconDailyTraffic' =>'favicon_daily_traffic'
	);
$description = array(
	'server'=>'Database server location. Can be socket or address.',
	'database'=>'Database name.',
	'username'=>'Database username.',
	'password'=>'Database password.',
	'dbms'=>'Database engine.',
	'prefix'=>'Table prefix in database.',
	'type'=>'Service type. [single|path|domain] e.g. [http://www.example.com/blog | http://www.example.com/blog/blog1 | http://blog1.example.com].',
	'domain'=>'Service domain. (http://www.example.com)',
	'path'=>'Service path. (e.g. /blog)',
	'timeout' => 'Session timeout limit (sec.)',
	'autologinTimeout' => 'Automatic login timeout (sec.)',
	'skin'    =>'Default blog skin name.',
	'language'=>'Server language',
	'timezone'=>'Server timezone',
	'encoding'=>'Character encoding',
	'serviceURL'  => 'Specify the default service URL. Useful if using other web program under the same domain.',
	'cookie_prefix'=> 'Service cookie prefix. Default cookie prefix is Textcube_[VERSION_NUMBER].',
	'pagecache'=>'Use pagecache function.',
	'codecache'=>'Use codecache function.',
	'skincache'=>'Use skin pre-fetching.',
	'memcached'=>'Use memcache to handle session and cache',
	'reader'   =>'Use Textcube reader. You can set it to false if you do not use Textcube reader, and want to decrease DB load.',
	'useNumericRSS'=>'Can force permalink to numeric format on RSS output.',
	'useEncodedURL'=>'URL encoding using RFC1738',
	'externalresources'=>'Loads resources from external CDN from resourceURL.',
	'resourcepath'=>'Specify the full URI of external resource.',
	'useSSL'=>'Use SSL connection. Every http:// will be replaced with https://',
	'allowBlogVisibilitySetting'   => 'Allow service users to change blog visibility.',
	'requirelogin'          => 'Force log-in process to every blogs. (for private blog service)',
	'flashclipboardpoter'   => 'Use Flash clipboard copy to support one-click trackback address copy.',
	'flashuploader' => 'Use Flash uploader to upload multiple files.',
	'debugmode'  =>'Textcube debug mode. (for core / plugin debug or optimization)',
	'debug_session_dump' => 'session info debuging.',
	'debug_rewrite_module' => 'rewrite handling module info debuging.',
	'favicon_daily_traffic' =>'Set favicon traffic limitation. default is 10MB.'
);

/* Exception handling */
$config = array();
foreach($matchTable as $abs => $real) {
	if($_POST[$abs] === 1) $config[$real] = true;
	else if($_POST[$abs] === 0) $config[$real] = false;
	else $config[$real] = $_POST[$abs];
}

$result = writeConfigFile($config, $description);
if ($result === true) {
	Respond::PrintResult(array('error' => 0));
} else {
	Respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
