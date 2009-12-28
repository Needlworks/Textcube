<?php
/* Make your own affiliate.local.php for changing the values of $openid_help_links and $openid_signup_links */

if (!defined('ROOT')) {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}

global $requestURI;

$another_config = __FILE__;
$another_config = str_replace( ".php", ".local.php", $another_config);
if( file_exists( $another_config ) ) {
	require( $another_config );
} else {
	$context = Model_Context::getInstance();
	global $hostURL, $blogURL;
	$_try_auth_url = $context->getProperty('uri.host') . $context->getProperty('uri.blog') . "/login/openid?action=try_auth&requestURI=$requestURI";
	$_op_base = "http://www.idtail.com";
	$_encoded_args      = base64_encode( "login_url:" . $_try_auth_url );
	$openid_help_link   = $_op_base . "/affiliate/help/textcube/" . $_encoded_args;
	$openid_signup_link = $_op_base . "/signup/textcube/" . $_encoded_args;
}
?>
