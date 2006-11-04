<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');

$IV = array(
	'POST' => array(
		'deleteWaterMark' => array('string', 'default' => "no"),
		'horizontalType' => array('string', 'default' => "left"),
		'verticalType' => array('string', 'default' => "top"),
		'horizontalPosition' => array('int', 'default' => 0),
		'verticalPosition' => array('int', 'default' => 0),
		'topPadding' => array('any', 'mandatory' => false),
		'rightPadding' => array('any', 'mandatory' => false),
		'bottomPadding' => array('any', 'mandatory' => false),
		'leftPadding' => array('any', 'mandatory' => false),
		'topPaddingManual' => array('int', 'mandatory' => false),
		'rightPaddingManual' => array('int', 'mandatory' => false),
		'bottomPaddingManual' => array('int', 'mandatory' => false),
		'leftPaddingManual' => array('int', 'mandatory' => false),
		'paddingColor' => array('string', 'default' => "FFFFFF"),
		'useResamplingAsDefault' => array('string', 'mandatory' => false),
		'useWatermarkAsDefault' => array('string', 'mandatory' => false)
		),
	'FILES' => array(
		'waterMark' => array('file', 'mandatory' => false)
		)
	);

require ROOT . '/lib/includeForOwner.php';

$isAjaxRequest = false; // checkAjaxRequest();
$errorArray = array();

// 기본 설정
if (isset($_POST['useResamplingAsDefault']) && ($_POST['useResamplingAsDefault'] == "yes")) {
	setUserSetting("resamplingDefault", "yes");
} else if (getUserSetting("resamplingDefault") == "yes") {
	removeUserSetting("resamplingDefault");
	removeUserSetting("waterMarkDefault");
	deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner/", "*");
}

if (isset($_POST['useWatermarkAsDefault']) && ($_POST['useWatermarkAsDefault'] == "yes")) {
	setUserSetting("waterMarkDefault", "yes");
} else  if (getUserSetting("waterMarkDefault") == "yes") {
	removeUserSetting("waterMarkDefault");
	deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner/", "*");
}

// 워터마크 처리.
if (isset($_POST['deleteWaterMark']) && ($_POST['deleteWaterMark'] == "yes")) {
	unlink(ROOT."/attach/$owner/watermark.gif");
}

// 업로드된 새 워터마크가 있는 경우.
if (!empty($_FILES['waterMark']['tmp_name'])) {
	$fileExt = Path::getExtension($_FILES['waterMark']['name']);
	
	if (!in_array($fileExt, array('.gif', '.jpg', '.png'))) {
		if ($isAjaxRequest) {
			printRespond(array('error' => 1, 'msg' => _t('변경하지 못했습니다.')));
		}
		// TODO : --???? error handling needed
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	} else { 
		requireComponent('Tattertools.Data.Attachment');
		Attachment::confirmFolder();

		if (move_uploaded_file($_FILES['waterMark']['tmp_name'], ROOT."/attach/$owner/watermark.gif")) {
			@chmod(ROOT . "/attach/$owner/watermark.gif", 0666);
			deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
		}
	}
} else if (!file_exists(ROOT."/attach/$owner/watermark.gif")) {
	$_POST['horizontalType'] = "left";
	$_POST['verticalType'] = "top";
	$_POST['horizontalPosition'] = "0";
	$_POST['verticalPosition'] = "0";
}

$bErrorFlag = false;
if (!in_array(strtolower($_POST['verticalType']), array("top", "middle", "bottom")))
	$bErrorFlag = true;
if (!in_array(strtolower($_POST['horizontalType']), array("left", "center", "right")))
	$bErrorFlag = true;
if (!preg_match("/^[0-9]+$/", $_POST['verticalPosition']))
	$bErrorFlag = true;
if (!preg_match("/^[0-9]+$/", $_POST['horizontalPosition']))
	$bErrorFlag = true;

// 워터마크 포지션 값에 오류가 있으면 이전 값으로 되돌림.
if ($bErrorFlag == true) {
	$errorArray['position'] = true;
} else {
	$_POST['verticalPosition'] = preg_replace("/^0*/", '', $_POST['verticalPosition']);
	$_POST['verticalPosition'] = empty($_POST['verticalPosition']) ? 0 : $_POST['verticalPosition'];
	$_POST['horizontalPosition'] = preg_replace("/^0*/", '', $_POST['horizontalPosition']);
	$_POST['horizontalPosition'] = empty($_POST['horizontalPosition']) ? 0 : $_POST['horizontalPosition'];
	
	$strNewPosition = "{$_POST['horizontalType']}={$_POST['horizontalPosition']}|{$_POST['verticalType']}={$_POST['verticalPosition']}";
	$strOldPosition = getUserSetting("waterMarkPosition");
	
	if (is_null($strOldPosition) || $strNewPosition != $strOldPosition) {
		setUserSetting('waterMarkPosition', $strNewPosition);
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	}
}
	
// 썸네일 여백.
if ($_POST['topPadding'] == "direct")
	$_POST['topPadding'] = $_POST['topPaddingManual'];
if ($_POST['bottomPadding'] == "direct")
	$_POST['bottomPadding'] = $_POST['bottomPaddingManual'];
if ($_POST['rightPadding'] == "direct")
	$_POST['rightPadding'] = $_POST['rightPaddingManual'];
if ($_POST['leftPadding'] == "direct")
	$_POST['leftPadding'] = $_POST['leftPaddingManual'];

$bErrorFlag = false;
if (!preg_match("/^[0-9]+$/", $_POST['topPadding']))
	$bErrorFlag = true;
if (!preg_match("/^[0-9]+$/", $_POST['bottomPadding']))
	$bErrorFlag = true;
if (!preg_match("/^[0-9]+$/", $_POST['rightPadding']))
	$bErrorFlag = true;
if (!preg_match("/^[0-9]+$/", $_POST['leftPadding']))
	$bErrorFlag = true;

// 썸네일 여백 값에 오류가 있으면 이전 값으로 되돌림.
if ($bErrorFlag == true)
	$errorArray['padding'] = true;

if ($bErrorFlag == false) {
	$_POST['topPadding'] = preg_replace("/^0*/", '', $_POST['topPadding']);
	$_POST['bottomPadding'] = preg_replace("/^0*/", '', $_POST['bottomPadding']);
	$_POST['rightPadding'] = preg_replace("/^0*/", '', $_POST['rightPadding']);
	$_POST['leftPadding'] = preg_replace("/^0*/", '', $_POST['leftPadding']);
	
	if (empty($_POST['topPadding']))
		$_POST['topPadding'] = 0;
	if (empty($_POST['bottomPadding']))
		$_POST['bottomPadding'] = 0;
	if (empty($_POST['rightPadding']))
		$_POST['rightPadding'] = 0;
	if (empty($_POST['leftPadding']))
		$_POST['leftPadding'] = 0;
	
	$strNewPadding = "{$_POST['topPadding']}|{$_POST['rightPadding']}|{$_POST['bottomPadding']}|{$_POST['leftPadding']}";
	$strOldPadding = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = ");
	
	if ($strOldPadding == false || $strNewPadding != $strOldPadding) {
		setUserSetting('thumbnailPadding', $strNewPadding);
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	}
}

// 썸네일 여백 색상.
if (preg_match("/^#?([A-F0-9]{3,6})$/i", $_POST['paddingColor'], $temp)) {
	$strNewColor = $temp[1];
	$strOldColor = getUserSetting("thumbnailPaddingColor");
	
	if (is_null($strOldColor) && $strNewColor != $strOldColor) {
		setUserSetting('thumbnailPaddingColor', $strNewColor);
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	}
} else {
	$errorArray['paddingColor'] = true;
}

if (count($errorArray) > 0)
	$errorResult['error'] = 1;
else
	$errorResult['error'] = 0;

$isAjaxRequest ? printRespond($errorResult) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>