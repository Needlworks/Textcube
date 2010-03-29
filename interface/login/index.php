<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'loginid' => array('string', 'mandatory' => false ),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null ),
		'refererURI' => array('string', 'default' => null ),
		'session' => array('string' , 32, 32, 'default' => null),
		'try' => array(array(1,2,3), 'default' => null),
	),
	'POST' => array(
		'loginid' => array('string', 'default' => null),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null),
		'refererURI' => array('string', 'default' => null),
		'reset' => array(array('on') ,'default' => null),
		'save' => array('any', 'default' => null),
		'teamblogPatch' => array('string', 'default' => null)
	)
);
define('__TEXTCUBE_LOGIN__',true);
define('__TEXTCUBE_ADMINPANEL__',true);
require ROOT . '/library/preprocessor.php';
$context = Model_Context::getInstance(); 

//$blogURL = getBlogURL();
if (isset($_GET['loginid']))
	$_POST['loginid'] = $_GET['loginid'];
if (isset($_GET['password']))
	$_POST['password'] = $_GET['password'];
if (!empty($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
else if (empty($_POST['requestURI']) && !empty($_SERVER['HTTP_REFERER']) )
	$_POST['requestURI'] = $_SERVER['HTTP_REFERER'];
else
	$_POST['requestURI'] = $context->getProperty('uri.blog');
if (!empty($_GET['refererURI'])) $_POST['refererURI'] = $_GET['refererURI'];
else $_POST['refererURI'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

$message = '';
$showPasswordReset = false;
if (isset($_GET['session']) && isset($_GET['requestURI'])) {
	global $service;
	setcookie( Session::getName(), $_GET['session'], 0, $context->getProperty('service.session_cookie_path'), $context->getProperty('service.session_cookie_domain'));
	header('Location: ' . $_GET['requestURI']);
	exit;
} else if (!empty($_POST['loginid']) && !empty($_POST['reset'])) {
	if (resetPassword($blogid, $_POST['loginid']))
		$message = _text('지정된 이메일로 로그인 정보가 전달되었습니다.');
	else 
		$message = _text('권한이 없습니다.');
} else if (!empty($_POST['loginid']) && !empty($_POST['password'])) {
	$isLogin = login($_POST['loginid'],$_POST['password']);
	if (!$isLogin) {
		$message = _text('아이디 또는 비밀번호가 틀렸습니다.');
		if (!doesHaveMembership() && isLoginId(getBlogId(), $_POST['loginid'])){
			$showPasswordReset = true;
		}
	}
}
$authResult = fireEvent('LOGIN_try_auth', false);
if (doesHaveOwnership() || doesHaveMembership()) {
	if (doesHaveOwnership() && !empty($_POST['requestURI'])) {
		$url = parse_url($_POST['requestURI']);
		if ($url && isset($url['host']) && !String::endsWith( '.' . $url['host'], '.' . $context->getProperty('service.domain')))
			$redirect = $context->getProperty('uri.blog')."/login?requestURI=" . rawurlencode($_POST['requestURI']) . '&session=' . rawurlencode(session_id());
		else
			$redirect = $_POST['requestURI'];
	} else {
		$redirect = $_POST['refererURI'];
	}
	if (empty($_SESSION['lastloginRedirected']) || $_SESSION['lastloginRedirected'] != $redirect) {
		$_SESSION['lastloginRedirected'] = $redirect;
	} else {
		unset($_SESSION['lastloginRedirected']);
	}
	header('Location: '.$redirect);
	exit;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _text('Textcube - Login');?></title>
<?php
	$browser = Utils_Browser::getInstance();
	if($browser->isMobile()) {
?>
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<?php
	}
?>
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
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/byTextcube.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.resourcepath');?>/script/common2.js"></script>
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $context->getProperty('service.path');?>";
			var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";

			window.addEventListener("load", execLoadFunction, false);
			
			function execLoadFunction() {
				document.forms[0].<?php echo (empty($_COOKIE['TSSESSION_LOGINID']) ? 'loginid' : 'password');?>.focus();
<?php
	$browser = Utils_Browser::getInstance();
	if($browser->isMobile()) {
?>
				setTimeout(scrollTo, 0, 0, 1);
<?php
	}
?>
			}
		//]]>
	</script>
<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
	<script type="text/javascript">
		//<![CDATA[
			window.addEventListener("load", checkTextcubeVersion, false);
			function checkTextcubeVersion() {
				if (confirm("<?php echo _text('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
					window.location.href = "<?php echo $context->getProperty('uri.blog');?>/checkup";
			}
		//]]>
	</script>
<?php
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION) {
?>
	<script type="text/javascript">
		//<![CDATA[
			window.addEventListener("load", checkTextcubeVersion, false);
			function checkTextcubeVersion() {
				if (confirm("<?php echo _text('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
					window.location.href = "<?php echo $context->getProperty('uri.blog');?>/checkup";
				}
		//]]>
	</script>
<?php
}
?>
</head>
<body id="body-login">
	<div id="temp-wrap">
		<div id="all-wrap">
			<div id="data-outbox">
				<div id="login-box">
					<div id="logo-box">
						<img src="<?php echo $context->getProperty('service.path').$adminSkinSetting['skin'];?>/image/logo_textcube.png" alt="<?php echo _text('텍스트큐브 로고');?>" />
					</div>
					<form method="post" action="">
						<input type="hidden" name="requestURI" value="<?php echo htmlspecialchars($_POST['requestURI']);?>" />
						<input type="hidden" name="refererURI" value="<?php echo htmlspecialchars($_POST['refererURI']); ?>" />
						<div id="basic-field-box" class="field-box">
							<dl id="email-line">
								<dt><label for="loginid"><?php echo _text('이메일');?></label></dt>
								<dd><input type="text" class="input-text" id="loginid" name="loginid" value="<?php echo htmlspecialchars(empty($_POST['loginid']) ? (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : $_COOKIE['TSSESSION_LOGINID']) : $_POST['loginid']);?>" maxlength="64" tabindex="1" /></dd>
							</dl>
							<dl id="password-line">
								<dt><label for="password"><?php echo _text('비밀번호');?></label></dt>
								<dd><input type="password" class="input-text" id="password" name="password" onkeydown="if (event.keyCode == 13) document.forms[0].submit()" maxlength="64" tabindex="2" /></dd>
							</dl>
							<dl id="checkbox-line">
								<dt><span class="label"><?php echo _text('선택사항');?></span></dt>
								<dd>
									<div id="email-save"><input type="checkbox" id="save" class="checkbox" name="save"<?php echo (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : 'checked="checked"');?> /><label for="save"><?php echo _text('이메일 저장');?></label></div>
									<?php echo ($showPasswordReset ? '<div id="password_int"><input type="checkbox" class="checkbox" id="reset" name="reset" /><label for="reset">' . _text('암호 초기화') . '</label></div>'.CRLF : '');?>
								</dd>
							</dl>
							
							<div class="button-box">
								<input type="submit" class="login-button input-button" name="button_login" value="<?php echo _text('로그인');?>" />
							</div>
						</div>
					</form>
						
<?php if( isActivePlugin('CL_OpenID') ) { 
	if( !empty($_COOKIE['openid']) ) {
		$openid_remember_check = "checked";
		$cookie_openid = $_COOKIE['openid'];
	} else {
		$openid_remember_check = "";
		$cookie_openid = '';
	}
	list( $openid_help_link, $openid_signup_link ) = fireEvent( "OpenIDAffiliateLinks", array('',''), $_POST['requestURI'] );
?>
					<form method="get" name="openid_form" action="<?php echo $context->getProperty('uri.blog'); ?>/login/openid?action=try_auth">
						<input type="hidden" name="requestURI" value="<?php echo htmlspecialchars($_POST['requestURI']); ?>" />
						<input type="hidden" name="refererURI" value="<?php echo htmlspecialchars($_POST['refererURI']); ?>" />
						<input type="hidden" name="need_writers" value="1" />
						<input type="hidden" name="action" value="try_auth" />
						
						<div id="openid-field-box" class="field-box">
							<dl id="openid-line">
								<dt><label for="openid_identifier"><?php echo _text('관리자 계정과 연결된 오픈아이디');?></label></dt>
								<dd>
									<input type="text" class="input-text openid-identifier-login" id="openid_identifier" name="openid_identifier" value="<?php echo $cookie_openid; ?>" maxlength="256" />
									<p class="example"><?php echo _text('예) textcube.idtail.com, textcube.myid.net'); ?></p>
								</dd>
							</dl>
							<dl id="openid-remember">
								<dt><span class="label"><?php echo _text('선택사항');?></span></dt>
								<dd><input type="checkbox" class="checkbox" id="openid_remember" name="openid_remember" <?php echo $openid_remember_check; ?> /><label for="openid_remember"><?php echo _text('오픈아이디 저장'); ?></label></dd>
							</dl>
							
							<div class="button-box">
								<input type="submit" class="login-button input-button" id="openid-login-button" name="openid_login" value="<?php echo _text('로그인'); ?>" />
							</div>
							
							<?php if (!empty($openid_help_link) || !empty($openid_signup_link)) { ?>
							<ul id="openid-intro">
								<?php if( !empty( $openid_help_link ) ) { ?>
								<li id="openid-help"><a href="<?php echo $openid_help_link; ?>" ><?php echo _text('오픈아이디란?') ?></a></li>
								<?php } ?>
								<?php if( !empty( $openid_signup_link ) ) { ?>
								<li id="openid-generate"><a href="<?php echo $openid_signup_link; ?>"><?php echo _text('오픈아이디 발급하기'); ?></a></li>
								<?php } ?>
							</ul>
							<?php } ?>
						</div>
					</form>
					<script type="text/javascript">
					//<![CDATA[ 
						function focus_openid(){ 
							document.getElementById("openid_identifier").focus();}
					//]]>
					</script>
<?php } ?>
<?php
						echo fireEvent('LOGIN_add_form', '', $_POST['requestURI'] );
if (!empty($message)) {
?>
					<div id="message-box">
						<?php echo $message.CRLF;?>
					</div>
<?php
}
?>
				</div> <!-- login-box -->
			</div> <!-- data-outbox -->
		</div> <!-- all-wrap -->
	</div> <!-- temp-wrap -->
<?php
	if( function_exists('__tcSqlLogDump') ) { 
		__tcSqlLogDump();
	}
?>
</body>
</html>
