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
	$_op_base = "http://www.example.com";
	$_encoded_args      = base64_encode( "login_url:" . $_try_auth_url );
	// Currently we have no use for OpenID descriptions.
	// Just set to null to hide the links in the login screen.
	$openid_help_link   = null; //$_op_base . "/affiliate/help/textcube/" . $_encoded_args;
	$openid_signup_link = null; //$_op_base . "/signup/textcube/" . $_encoded_args;
}
?>
