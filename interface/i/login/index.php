<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'loginid' => array('string', 'default' => null),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null),
		'save' => array('string', 'default' => null)
	)
);
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
if (isset($_GET['loginid']))
	$_POST['loginid'] = $_GET['loginid'];
if (isset($_GET['password']))
	$_POST['password'] = $_GET['password'];
if (!empty($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
else
	$_POST['requestURI'] = "#home";
if (isset($_GET['save']))
	$_POST['save'] = $_GET['save'];

$message = '';
//$showPasswordReset = false;

if (isset($_GET['session']) && isset($_GET['requestURI'])) {
	header('Set-Cookie: TSSESSION=' . $_GET['session'] . '; path=/; domain=' . $_SERVER['HTTP_HOST']);
	header('Location: ' . $_GET['requestURI']);
	exit;
} else if (!empty($_POST['loginid']) && !empty($_POST['password'])) {
	$isLogin = login($_POST['loginid'],$_POST['password']);
	if (!$isLogin) {
		$message = _text('잘못된 E-mail 주소 또는 비밀번호입니다.');
//		if (!doesHaveMembership() && isLoginId(getBlogId(), $_POST['loginid'])){
//			$showPasswordReset = true;
//		}
	} else if($isLogin == 2) {
		$message=_text('권한이 없습니다.');
	}
}

if(!doesHaveOwnership()) {
	?>
	<form id="Login" method="GET" action="<?php echo $blogURL;?>/login" title="Login" class="panel" selected="false">
        <h2><?php echo _text('블로그 로그인');?></h2>
        <fieldset>
			<?php if($message) { ?>
			<div class="row">
				<label><span class="loginError"><?php echo $message;?></span></label>
			</div>
			<?php };?>
            <div class="row">
				<label for="loginid"><?php echo _text('E-mail');?></label>
				<input type="email" class="input-text" id="loginid" name="loginid" value="<?php echo htmlspecialchars(empty($_POST['loginid']) ? (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : $_COOKIE['TSSESSION_LOGINID']) : $_POST['loginid']);?>" maxlength="64" tabindex="1" />
            </div>
            <div class="row">
				<label for="password"><?php echo _text('비밀번호');?></label>
				<input type="password" class="input-text" id="password" name="password" onkeydown="if (event.keyCode == 13) document.forms[0].submit()" maxlength="64" tabindex="2" />
            </div>
            <div class="row">
                <label><?php echo _text('E-mail 저장');?></label>
                <div id="emailSave" class="toggle" <?php echo (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : 'toggled="true"');?> onclick="emailSaveToggleCheck(this);"><span class="thumb"></span><span class="toggleOn">|</span><span class="toggleOff">O</span></div>
			</div>
		</fieldset>
		<input type="hidden" id="save" class="checkbox" name="save" />    
		<input type="hidden" name="requestURI" value="#home" />

		<a href="#" class="whiteButton " type="submit"><?php echo _text('블로그 로그인');?></a>
	</form>
<?php
} else {
?>
	<div id="Login" title="Login" class="panel" selected="false">
		<div class="content">
			<?php echo _text('로그인 하였습니다.');?>
		</div>
		<a href="#" onclick="self.location.reload();" class="whiteButton margin-top10"><?php echo _text('첫 페이지로 돌아가기');?></a>
		<a href="<?php echo $defaultURL."/owner/center/dashboard";?>" onclick="window.location.href='<?php echo $defaultURL."/owner/center/dashboard";?>'" class="whiteButton margin-top10"><?php echo _text('관리 패널로 들어가기');?></a>
	</div>
<?php
}
?>
