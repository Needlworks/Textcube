<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'loginid' => array('string', 'default' => null),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null),
		'save' => array('string', 'default' => null)
	)
);
define('__TEXTCUBE_IPHONE__', true);
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
		$message = _text('Wrong E-mail or Password.');
//		if (!doesHaveMembership() && isLoginId(getBlogId(), $_POST['loginid'])){
//			$showPasswordReset = true;
//		}
	} else if($isLogin == 2) {
		$message=_t('Permission denied.');
	}
}

if(!doesHaveOwnership()) {
	?>
	<form id="Login" method="GET" action="<?php echo $blogURL;?>/login" title="Login" class="panel" selected="false">
        <h2>Blog Admin Login.</h2>
        <fieldset>
			<?php if($message) { ?>
			<div class="row">
				<label><span class="loginError"><?php echo $message;?></span></label>
			</div>
			<?php };?>
            <div class="row">
				<label for="loginid"><?php echo _text('E-mail');?></label>
				<input type="text" class="input-text" id="loginid" name="loginid" value="<?php echo htmlspecialchars(empty($_POST['loginid']) ? (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : $_COOKIE['TSSESSION_LOGINID']) : $_POST['loginid']);?>" maxlength="64" tabindex="1" />
            </div>
            <div class="row">
				<label for="password"><?php echo _text('Password');?></label>
				<input type="password" class="input-text" id="password" name="password" onkeydown="if (event.keyCode == 13) document.forms[0].submit()" maxlength="64" tabindex="2" />
            </div>
            <div class="row">
                <label>Save E-mail</label>
                <div id="emailSave" class="toggle" <?php echo (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : 'toggled="true"');?> onclick="emailSaveToggleCheck(this);"><span class="thumb"></span><span class="toggleOn">ON</span><span class="toggleOff">OFF</span></div>
			</div>
		</fieldset>
		<input type="hidden" id="save" class="checkbox" name="save" />    
		<input type="hidden" name="requestURI" value="#home" />

		<a href="#" class="whiteButton " type="submit"><?php echo _text('Blog Login');?></a>
	</form>
<?php
} else {
?>
	<div id="Login" title="Login" class="panel" selected="false">
		<div class="content">
			Login Successfully.
		</div>
		<a href="#" onclick="self.location.reload();" class="whiteButton margin-top10"><?php echo _text('Go to front page');?></a>
	</div>
<?php
}
?>
