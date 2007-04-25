<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
$IV = array(
	'GET' => array(
		'loginid' => array('string', 'mandatory' => false ),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null ),
		'session' => array('string' , 32, 32, 'default' => null),
		'try' => array(array(1,2,3), 'default' => null),
	),
	'POST' => array(
		'loginid' => array('string', 'default' => null),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null),
		'reset' => array(array('on') ,'default' => null),
		'save' => array('any', 'default' => null)
	)
);
require ROOT . '/lib/includeForBlog.php';

if (isset($_GET['loginid']))
	$_POST['loginid'] = $_GET['loginid'];
if (isset($_GET['password']))
	$_POST['password'] = $_GET['password'];
if (!empty($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
else if (empty($_POST['requestURI']))
	$_POST['requestURI'] = $_SERVER['HTTP_REFERER'];
$message = '';
$showPasswordReset = false;
if (isset($_GET['session']) && isset($_GET['requestURI'])) {
	header('Set-Cookie: TSSESSION=' . $_GET['session'] . '; path=/; domain=' . $_SERVER['HTTP_HOST']);
	header('Location: ' . $_GET['requestURI']);
	exit;
} else if (!empty($_POST['loginid']) && !empty($_POST['reset'])) {
	if (resetPassword($owner, $_POST['loginid']))
		$message = _text('지정된 이메일로 로그인 정보가 전달되었습니다.');
	else
		$message = _text('권한이 없습니다.');
} else if (!empty($_POST['loginid']) && !empty($_POST['password'])) {
	if (!login($_POST['loginid'], $_POST['password'])) {
		$message = _text('아이디 또는 비밀번호가 틀렸습니다.');
		if (!doesHaveMembership() && isLoginId($owner, $_POST['loginid']))
			$showPasswordReset = true;
	}
}

if (doesHaveOwnership()) {
	if (!empty($_POST['requestURI'])) {
		if (($url = parse_url($_POST['requestURI'])) && isset($url['host']) && !String::endsWith($url['host'], '.' . $service['domain']))
			header("Location: {$blogURL}/login?requestURI=" . rawurlencode($_POST['requestURI']) . '&session=' . rawurlencode(session_id()));
		else
			header("Location: {$_POST['requestURI']}");
	} else {
		$blog = getBlogSetting($_SESSION['userid']);
		header("Location: $blogURL");
	}
	exit;
} else if (doesHaveMembership()) {
	$message = _text('권한이 없습니다.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _text('Textcube - Login');?></title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/login.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.ie.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/login.ie.css" /><![endif]-->
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/byTextcube.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/EAF2.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/common2.js"></script>
	<script type="text/javascript">
		//<![CDATA[
			window.addEventListener("load", execLoadFunction, false);
			
			function execLoadFunction() {
				document.forms[0].<?php echo (empty($_COOKIE['TSSESSION_LOGINID']) ? 'loginid' : 'password');?>.focus();
			}
		//]]>
	</script>
</head>
<body id="body-login">
	<div id="temp-wrap">
		<div id="all-wrap">
			<form method="post" action="">
				<input type="hidden" name="requestURI" value="<?php echo htmlspecialchars($_POST['requestURI']);?>" />
				
				<div id="data-outbox">
					<div id="login-box">
						<div id="logo-box">
							<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/logo_textcube.png" alt="<?php echo _text('텍스트큐브 로고');?>" />
			            </div>
			            
			            <div id="field-box">
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
								<input type="submit" class="login-button input-button" value="<?php echo _text('로그인');?>" />
							</div>
						</div>
						
<?php
if (!empty($message)) {
?>
						<div id="message-box">
							<?php echo $message.CRLF;?>
						</div>
<?php
}
?>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
