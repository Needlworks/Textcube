<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_ADMINPANEL__',true);
require ROOT . '/library/preprocessor.php';

global $openid_session;
global $openid_session_id;

$context = Model_Context::getInstance();
if( empty( $_GET['requestURI'] ) ) {
	$requestURI = $context->getProperty('uri.blog');
} else {
	$requestURI = $_GET['requestURI'];
	if( Acl::getIdentity( 'openid' ) ) {
		header( "Location: $requestURI" );
		exit;
	}
}

list( $openid_help_link, $openid_signup_link ) = fireEvent( 'OpenIDAffiliateLinks', $requestURI );

$img_url = $context->getProperty('uri.host').$context->getProperty('service.path') . "/plugins/" . basename(dirname( __file__ )) . "/login-bg.gif";

if( !empty($_COOKIE['openid']) ) {
	$openid_remember_check = "checked";
	$cookie_openid = $_COOKIE['openid'];
} else {
	$openid_remember_check = "";
	$cookie_openid = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html><head>
<title><?php echo _text('텍스트큐브') .":". _text('오픈아이디 인증'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/basic.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/login.css" />
<!--[if lte IE 6]>
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/basic.ie.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/login.ie.css" />
<![endif]-->
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/basic.ie7.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/login.ie7.css" />
<![endif]-->
</head>
<body id="body-login" onload="document.getElementById('openid_identifier').focus()">
	<div id="temp-wrap">
		<div id="all-wrap">
			<form method="get" name="openid_form" action="<?php echo $context->getProperty('uri.blog') . '/login/openid?action=try_auth'?>" >
        		<input type="hidden" name="action" value="try_auth" />
        		<input type="hidden" name="requestURI" value="<?php echo $requestURI; ?>" />
				<input type="hidden" name="need_writers" value="0" />
				<div id="data-outbox">
					<div id="login-box">
						<div id="logo-box">
							<img src="<?php echo $context->getProperty('service.path');?>/skin/admin/whitedream/image/logo_textcube.png" alt="<?php echo _text('텍스트큐브 로고'); ?>" />
			            </div>
			            
			            <div class="field-box">
							<h1><?php echo _text('오픈아이디 게스트 로그인'); ?></h1>
							
			            	<dl id="email-line">
			            		<dt><label for="loginid"><?php echo _text('오픈아이디'); ?></label></dt>
			            		<dd><input type="text" class="input-text openid-identifier-guest-login" id="openid_identifier" name="openid_identifier" value="<?php echo $cookie_openid ?>" maxlength="256" tabindex="1" /></dd>
							</dl>
							<dl id="checkbox-line">
								<dt><span class="label"><?php echo _text('선택사항');?></span></dt>
			            		<dd><input type="checkbox" class="checkbox" id="openid_remember" name="openid_remember" <?php echo $openid_remember_check ?> /><label for="openid_remember"><?php echo _text('오픈아이디 기억') ?></label></dd>
							</dl>
							
							<div class="button-box">
								<input type="submit" class="login-button input-button" name="openid_login" value="<?php echo _text('로그인');?>" />
								<input type="submit" class="input-button" name="openid_cancel" value="<?php echo _text('취소') ?>" />
							</div>
							
		            		<ul>
								<?php if( !empty( $openid_help_link ) ) { ?>
								<li id="openid-help"><a href="<?php echo $openid_help_link; ?>" ><?php echo _text('오픈아이디란?') ?></a></li>
								<?php } ?>
								<?php if( !empty( $openid_signup_link ) ) { ?>
								<li><a href="<?php echo $openid_signup_link; ?>"><?php echo _text('오픈아이디 발급하기'); ?></a></li>
								<?php } ?>
								<li id="openid-helper"><?php echo _textf('Technically supported by %1', '<a href="http://www.idtail.com/">idtail.com</a>');?></li>
							</ul>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>