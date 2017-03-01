<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'allowBlogVisibility'    => array('int'),
		'requireLogin'           => array('int',0,1),
		'encoding'               => array('string'),
		'faviconDailyTraffic'    => array('int'),
		'flashClipboardPoter'    => array('int',0,1),
		'flashUploader'          => array('int',0,1),
		'language'               => array('string'),
		'serviceurl'             => array('string'),
		'cookieprefix'           => array('string', 'mandatory' => false, 'default' => ''),
		'skin'                   => array('string'),
		'timeout'                => array('int'),
		'timezone'               => array('string'),
		'useDebugMode'           => array('int',0,1),
		'useEncodedURL'          => array('int',0,1),
		'useNumericRSS'          => array('int',0,1),
		'usePageCache'           => array('int',0,1),
		'useCodeCache'           => array('int',0,1),
		'useReader'              => array('int',0,1),
		'useRewriteDebugMode'    => array('int',0,1),
		'useSessionDebugMode'    => array('int',0,1),
		'useSkinCache'           => array('int',0,1),
		'useMemcached'           => array('int',0,1),
		'useSSL'                 => array('int',0,1),
		'useExternalResource'    => array('int',0,1),
		'externalResourceURL'    => array('string', 'mandatory' => false, 'default' => '')
		)
);

require ROOT . '/library/preprocessor.php';

requireModel('blog.service');
requireStrictRoute();
$matchTable = array(
	'timeout' => 'timeout',
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
	'useSSL'=>'useSSL',
	'useReader'   =>'reader',
	'useNumericRSS'=>'useNumericRSS',
	'useEncodedURL'=>'useEncodedURL',
	'useExternalResource'=>'externalresources',
	'externalResourceURL'=>'resourceURL',
	'allowBlogVisibility'   => 'allowBlogVisibilitySetting',
	'requireLogin'          => 'requirelogin',
	'flashClipboardPoter'   => 'flashclipboardpoter',
	'flashUploader' => 'flashuploader',
	'useDebugMode'  =>'debugmode',
	'useSessionDebugMode' => 'debug_session_dump',
	'useRewriteDebugMode' => 'debug_rewrite_module',
	'faviconDailyTraffic' =>'favicon_daily_traffic'
	);
/* Exceptional handling */
$config = array();
foreach($matchTable as $abs => $real) {
	if($_POST[$abs] === 1) $config[$real] = true;
	else if($_POST[$abs] === 0) $config[$real] = false;
	else $config[$real] = $_POST[$abs];
}

$result = writeConfigFile($config);
if ($result === true) {
	Respond::PrintResult(array('error' => 0));
} else {
	Respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
