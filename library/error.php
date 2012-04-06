<?php
//require_once ROOT."/library/include.blog.php";
function errorExit($code)
{
//	global $skin, $skinSetting, $suri, $defaultURL, $blog;
//	global $service, $blogURL, $defaultURL, $serviceURL, $gCacheStorage;

	$status_msg = array(
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported'
	);
	$error_header = '500 Internal Server Error';
	if( isset( $status_msg[$code] ) ) {
		$error_header = "{$code} {$status_msg[$code]}";
	}
	header( "HTTP/1.1 $error_header" );
	echo "<html><head><body>$error_header</body></html>";

	/* This noise is the power of our cron engine, thank you! */
//	requireModel("blog.cron");
//	checkCronJob();
	exit;
}
?>
