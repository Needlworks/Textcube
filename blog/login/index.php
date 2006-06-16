<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (isset($_GET['loginid']))
	$_POST['loginid'] = $_GET['loginid'];
if (isset($_GET['password']))
	$_POST['password'] = $_GET['password'];
if (!empty($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
else
	$_POST['requestURI'] = $_SERVER['HTTP_REFERER'];
$message = '';
$showPasswordReset = false;
if (!empty($_POST['loginid']) && !empty($_POST['reset'])) {
	if (resetPassword($owner, $_POST['loginid']))
		$message = _t('지정된 이메일로 로그인 정보가 전달되었습니다.');
	else
		$message = _t('권한이 없습니다.');
} else if (!empty($_POST['loginid']) && !empty($_POST['password'])) {
	if (!login($_POST['loginid'], $_POST['password'])) {
		$message = _t('아이디 또는 비밀번호가 틀렸습니다.');
		if (!doesHaveMembership() && isLoginId($owner, $_POST['loginid']))
			$showPasswordReset = true;
	}
	if (doesHaveMembership()) {
		if (!empty($_POST['requestURI']))
			header("Location: {$_POST['requestURI']}");
		else {
			$blog = getBlogSetting($_SESSION['userid']);
			header("Location: $blogURL");
		}
		exit;
	}
} else if (doesHaveMembership() && !doesHaveOwnership()) {
	$message = _t('권한이 없습니다.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml1-traditional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _t('Tattertools - Login')?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/basic.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/login.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/basic.opera.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/login.opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/basic.ie.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$service['adminSkin']?>/login.ie.css" /><![endif]-->
</head>
<body id="body-login" onload="document.forms[0].<?=(empty($_COOKIE['TSSESSION_LOGINID']) ? 'loginid' : 'password')?>.focus();">
	<div id="temp-wrap">
		<div id="all-wrap">
			<form method="post" action="">
				<input type="hidden" name="requestURI" value="<?=htmlspecialchars($_POST['requestURI'])?>" />
				
				<div id="data-outbox">
					<div id="login-box">
						<div id="logo-box">
							<img src="<?=$service['path']?>/image/logo_tattertools.gif" alt="<?php echo _t('태터툴즈 로고')?>" />
			            </div>
			            
			            <div id="field-box">
			            	<dl id="email-line">
			            		<dt><label for="loginid"><span class="text"><?=_t('이메일')?></span></label></dt>
			            		<dd><input type="text" class="text-input" id="loginid" name="loginid" value="<?=htmlspecialchars(empty($_POST['loginid']) ? (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : $_COOKIE['TSSESSION_LOGINID']) : $_POST['loginid'])?>" maxlength="64" tabindex="1" /></dd>
			            	</dl>
			            	<dl id="password-line">
			            		<dt><label for="password"><span class="text"><?=_t('비밀번호')?></span></label></dt>
								<dd><input type="password" class="text-input" id="password" name="password" onkeydown="if (event.keyCode == 13) document.forms[0].submit()" maxlength="64" tabindex="2" /></dd>
							</dl>
							<dl id="checkbox-line">
								<dt><span class="label"><span class="text"><?=_t('선택사항')?></span></span></dt>
								<dd>
									<div id="email-save"><input type="checkbox" id="save" class="checkbox" name="save"<?=(empty($_COOKIE['TSSESSION_LOGINID']) ? '' : 'checked="checked"')?> /> <label for="save"><span class="text"><?=_t('이메일 저장')?></span></label></div>
									<?=($showPasswordReset ? '<div id="password_int"><input type="checkbox" class="checkbox" id="reset" name="reset" /> <label for="reset"><span class="text">' . _t('암호 초기화') . '</span></label></div>' : '')?>

								</dd>
							</dl>
							
							<div class="button-box">
								<a class="login-button button" href="#void" onclick="document.forms[0].submit()"><span class="text"><?=_t('로그인')?></span></a>
							</div>
						</div>
						
<?
if (!empty($message)) {
?>
						<div id="message-box">
							<?=$message.CRLF?>
						</div>
<?
}
?>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
