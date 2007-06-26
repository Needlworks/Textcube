<?php
global $serviceURL;
$_try_auth_url = $serviceURL . "/plugin/openid/try_auth?redirect=$requestURI";
$_op_base = "http://www.idtail.com";
$_encoded_args      = base64_encode( "login_url:" . $_try_auth_url );
$openid_help_link   = $_op_base . "/affiliate/help/textcube/" . $_encoded_args;
$openid_signup_link = $_op_base . "/signup/textcube/" . $_encoded_args;
?>
