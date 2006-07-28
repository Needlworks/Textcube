<?
define('ROOT', '../..');
$IV = array(
	'GET' => array(
		'loginid' => array('string', 'mandatory' => false ),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null ),
		'session' => array('string' , 16, 16, 'default' => null),
		'try' => array(array(1,2,3), 'default' => null),
		'throughDaum' => array(array('true'), 'default' => null) 
	),
	'POST' => array(
		'loginid' => array('string', 'default' => null),
		'password' => array('string', 'default' => null),
		'requestURI' => array('string', 'default' => null),
		'reset' => array(array('on') ,'default' => null),
		'save' => array('any', 'default' => null)
	)
);
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
if (isset($_GET['session']) && isset($_GET['requestURI'])) {
	header('Set-Cookie: TSSESSION=' . $_GET['session'] . '; path=/; domain=' . $_SERVER['HTTP_HOST']);
	header('Location: ' . $_GET['requestURI']);
	exit;
} else if (!empty($_POST['loginid']) && !empty($_POST['reset'])) {
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
}

if (doesHaveOwnership()) {
	if (!empty($_POST['requestURI'])) {
		if (($url = parse_url($_POST['requestURI'])) && isset($url['host']) && !String::endsWith($url['host'], '.' . $service['domain']))
			header("Location: http://{$url['host']}{$service['path']}/login?requestURI=" . rawurlencode($_POST['requestURI']) . '&session=' . rawurlencode(session_id()));
		else
			header("Location: {$_POST['requestURI']}");
	} else {
		$blog = getBlogSetting($_SESSION['userid']);
		header("Location: $blogURL");
	}
	exit;
} else if (doesHaveMembership()) {
	$message = _t('권한이 없습니다.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tattertools - Login</title>
<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
</head>
<body onload="document.forms[0].<?=(empty($_COOKIE['TSSESSION_LOGINID']) ? 'loginid' : 'password')?>.focus()">
<form method="post" action="">
  <input type="hidden" name="requestURI" value="<?=htmlspecialchars($_POST['requestURI'])?>" />
  <table cellspacing="0" width="100%" height="450px" style="background-image:url('<?=$service['path']?>/image/owner/bg.gif'); background-repeat:repeat-x">
    <tr>
      <td>&nbsp;</td>
      <td width="520" valign="top" style="padding:100px 25px 30px 20px">
        <table cellspacing="0" style="width:100%">
          <tr>
            <td style="width:7px; height:7px"><img width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftTop.gif" alt="" /></td>
            <td bgcolor="#FFFFFF"><img width="1" height="1" src="<?=$service['path']?>/image/owner/spacer.gif" alt="" /></td>
            <td style="width:7px; height:7px"><img width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightTop.gif" alt="" /></td>
          </tr>
        </table>
        <table cellspacing="0" style="width:100%; background-color:#FFFFFF">
          <tr>
            <td valign="middle" style="height:50px; padding:5px 15px 15px 15px">
              <table cellspacing="0">
                <tr>
                  <td align="center" style="padding-left:20px"><img src="<?=$service['path']?>/image/owner/controlPanelLogo.gif" alt="" /></td>
                </tr>
              </table>
            </td>
            <td>
              <table cellspacing="0">
                <tr>
                  <td style="padding:10px 30px 10px 30px">
                    <table cellspacing="0" width="100%">
                      <tr>
                        <td align="right" style="padding-right:5px"><?=_t('E-mail')?> ::</td>
                        <td>
                          <input type="text" name="loginid" value="<?=htmlspecialchars(empty($_POST['loginid']) ? (empty($_COOKIE['TSSESSION_LOGINID']) ? '' : $_COOKIE['TSSESSION_LOGINID']) : $_POST['loginid'])?>" maxlength="64" tabindex="2" style="width:160px" />
                        </td>
                      </tr>
                      <tr>
                        <td align="right" style="padding-right:5px"><?=_t('비밀번호')?> ::</td>
                        <td>
                          <input type="password" name="password" maxlength="64" onkeydown="if (event.keyCode == 13) document.forms[0].submit()" tabindex="3" style="width:160px" />
                        </td>
                      </tr>
                    </table>
                    <table style="width:100%; margin:7px 0px 5px 0px">
                      <tr>
                        <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" width="100%">
                      <tr>
                        <!--td><a href="/service/signup">Sign up</a></td-->
						<td><input type="checkbox" name="save"<?=(empty($_COOKIE['TSSESSION_LOGINID']) ? '' : 'checked="checked"')?> /> <?=_t('이메일 저장')?><br /><?=($showPasswordReset ? '<input type="checkbox" name="reset" /> ' . _t('암호 초기화') : '')?></td>
                        <td align="right">
                          <table class="buttonTop" cellspacing="0" onclick="document.forms[0].submit()">
                            <tr>
                              <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" /></td>
                              <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><a href="javascript:document.forms[0].submit()" tabindex="4" style="color:black;text-decoration:none"><?=_t('로그인')?></a></td>
                              <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" /></td>
                            </tr>
                          </table>
                        </td>
					  </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
<?
if (!empty($message)) {
?>
          <tr>
            <td colspan="2" align="center" style="background-color:#EBF2F8; padding:5px 10px 5px 10px"><?=$message?></td>
          </tr>
<?
}
?>
        </table>
        <table cellspacing="0" style="width:100%">
          <tr>
            <td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftBottom.gif" /></td>
            <td style="background-color:#FFFFFF"><img alt="" width="1" height="1" src="<?=$service['path']?>/image/owner/spacer.gif" /></td>
            <td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightBottom.gif" /></td>
          </tr>
        </table>
      </td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</html>
