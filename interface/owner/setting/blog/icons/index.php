<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
	
$IV = array(
	'POST' => array(
		'mode' => array(array('0','1'), 'mandatory' => false),
		'blogIconSize' => array(array('0','16','32','48'), 'mandatory' => false),
		'deleteLogo' => array('string', 'default' => NULL),
		'deleteBlogIcon' => array('string', 'default' => NULL),
		'deleteFavicon' => array('string', 'default' => NULL),
		'useBlogIconAsIphoneShortcut' => array('bool','default'=>false)
	),
	'FILES' => array(
		'blogIcon' => array('file', 'mandatory' => false),
		'favicon' => array('file', 'mandatory' => false),
		'logo' => array('file', 'mandatory' => false)
	),
	'SERVER' => array(
		'HTTP_REFERER' => array('string')
	)
);

require ROOT . '/library/preprocessor.php';
$errorText = array();

// 로고 처리.
if ($_POST['deleteLogo'] == "yes") {
	removeBlogLogo($blogid);
	array_push($errorText, _t('로고를 초기화 하였습니다.'));
}

if (!empty($_FILES['logo']['tmp_name'])) {
	$fileExt = Path::getExtension($_FILES['logo']['name']);
	
	if (($fileExt != '.gif') && ($fileExt != '.jpg') && ($fileExt != '.png')) {
		array_push($errorText, _t('로고를 변경하지 못했습니다.'));
	} else { 
		if (changeBlogLogo($blogid, $_FILES['logo']) === false) {
			array_push($errorText, _t('로고를 변경하지 못했습니다.'));
		} else {
			array_push($errorText, _t('로고를 변경하였습니다.'));
		}
	}
}

// 파비콘 처리.
if ($_POST['deleteFavicon'] == "yes") {
	unlink(ROOT."/attach/$blogid/favicon.ico");
	array_push($errorText, _t('파비콘을 초기화 하였습니다.'));
}

if (!empty($_FILES['favicon']['tmp_name'])) {
	if (Path::getExtension($_FILES['favicon']['name']) != '.ico') {
		array_push($errorText, _t('파비콘을 변경하지 못했습니다.'));
	} else { 
		Attachment::confirmFolder();
	
		if (move_uploaded_file($_FILES['favicon']['tmp_name'], ROOT."/attach/$blogid/favicon.ico")) {
			@chmod(ROOT . "/attach/$blogid/favicon.ico", 0666);
			array_push($errorText, _t('파비콘을 변경하였습니다.'));
		}
	}
}

// 블로그 아이콘 처리.
if ($_POST['deleteBlogIcon'] == "yes") {
	unlink(ROOT."/attach/$blogid/index.gif");
	array_push($errorText, _t('블로그 아이콘을 초기화 하였습니다.'));
}

if (!empty($_FILES['blogIcon']['tmp_name'])) {
	$fileExt = Path::getExtension($_FILES['blogIcon']['name']);
	
	if (!in_array($fileExt, array('.gif', '.jpg', '.jpeg', '.png'))) {
		array_push($errorText, _t('블로그 아이콘을 변경하지 못했습니다.'));
	} else { 
		Attachment::confirmFolder();
		
		if (move_uploaded_file($_FILES['blogIcon']['tmp_name'], ROOT . "/attach/$blogid/index.gif")) {
			@chmod(ROOT . "/attach/$blogid/index.gif", 0666);
			array_push($errorText, _t('블로그 아이콘을 변경하였습니다.'));
		} else {
			
		}
	}
}
Setting::setBlogSettingGlobal('useBlogIconAsIphoneShortcut',$_POST['useBlogIconAsIphoneShortcut']);
if (!empty($errorText)) {
	$errorText = urlencode(implode('<br />',$errorText));
} else {
	$errorText = urlencode(_T('저장되었습니다'));
}
	$url = $_SERVER['HTTP_REFERER'];
	$pos = strpos($url, '?message=');
	if ($pos != false) {
		$url = substr($url, 0, $pos);
	}

	header("Location: ".$url . '?message=' . $errorText);

?>
