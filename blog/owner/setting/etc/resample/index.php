<?php
define('ROOT', '../../../../..');

$IV = array(
	'POST' => array(
		'ajaxRequest' => array('string', 'mandatory' => false),
		'deleteWaterMark' => array('string', 'mandatory' => false),
		'horizontalType' => array('string', 'mandatory' => false),
		'verticalType' => array('string', 'mandatory' => false),
		'horizontalPosition' => array('int', 'mandatory' => false),
		'verticalPosition' => array('int', 'mandatory' => false),
		'topPadding' => array('any', 'mandatory' => false),
		'rightPadding' => array('any', 'mandatory' => false),
		'bottomPadding' => array('any', 'mandatory' => false),
		'leftPadding' => array('any', 'mandatory' => false),
		'topPaddingManual' => array('int', 'mandatory' => false),
		'rightPaddingManual' => array('int', 'mandatory' => false),
		'bottomPaddingManual' => array('int', 'mandatory' => false),
		'leftPaddingManual' => array('int', 'mandatory' => false),
		'paddingColor' => array('string', 'default' => "FFFFFF")
	),
	'FILES' => array(
		'waterMark' => array('file')
	)
);

require ROOT . '/lib/includeForOwner.php';

$isAjaxRequest = false; // checkAjaxRequest();
$errorArray = array();

// 워터마크 처리.
if ($_POST['deleteWaterMark'] == "yes") {
	unlink(ROOT."/attach/$owner/watermark.gif");
}

$forceToInit = false;
if (!empty($_FILES['waterMark']['tmp_name'])) {
	$fileExt = Path::getExtension($_FILES['waterMark']['name']);
	
	if (($fileExt != '.gif') && ($fileExt != '.jpg') && ($fileExt != '.png')) {
		print('alert("' . _t('변경하지 못했습니다.') . '");');
	} else { 
		requireComponent('Tattertools.Data.Attachment');
		Attachment::confirmFolder();

		if (move_uploaded_file($_FILES['waterMark']['tmp_name'], ROOT."/attach/$owner/watermark.gif")) {
			@chmod(ROOT . "/attach/$owner/watermark.gif", 0666);
			deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
		}
	}
} else if (!file_exists(ROOT."/attach/$owner/watermark.gif")) {
	$forceToInit = true;
}

// 워터마크 포지션.
if ($forceToInit == true) {
	$_POST['horizontalType'] = "left";
	$_POST['verticalType'] = "top";
	$_POST['horizontalPosition'] = "0";
	$_POST['verticalPosition'] = "0";
}

$bErrorFlag = false;
if (strtolower($_POST['verticalType']) != "top" && strtolower($_POST['verticalType']) != "middle" && strtolower($_POST['verticalType']) != "bottom")
	$bErrorFlag = true;
if (strtolower($_POST['horizontalType']) != "left" && strtolower($_POST['horizontalType']) != "center" && strtolower($_POST['horizontalType']) != "right")
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['verticalPosition'], $temp))
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['horizontalPosition'], $temp))
	$bErrorFlag = true;

// 워터마크 포지션 값에 오류가 있으면 이전 값으로 되돌림.
if ($bErrorFlag == true)
	$errorArray['position'] = getUserSetting("waterMarkPosition", "left=10|bottom=10");

if ($bErrorFlag == false) {
	$_POST['verticalPosition'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['verticalPosition']);
	$_POST['verticalPosition'] = empty($_POST['verticalPosition']) ? 0 : $_POST['verticalPosition'];
	$_POST['horizontalPosition'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['horizontalPosition']);
	$_POST['horizontalPosition'] = empty($_POST['horizontalPosition']) ? 0 : $_POST['horizontalPosition'];
	
	$strNewPosition = "{$_POST['horizontalType']}={$_POST['horizontalPosition']}|{$_POST['verticalType']}={$_POST['verticalPosition']}";
	$strOldPosition = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'waterMarkPosition'");
	
	if ($strOldPosition == false) {
		DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'waterMarkPosition', '$strNewPosition')");
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewPosition != $strOldPosition) {
		DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '$strNewPosition' WHERE `user` = $owner AND `name` = 'waterMarkPosition'");
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
if (!eregi("^[0-9]+$", $_POST['topPadding'], $temp))
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['bottomPadding'], $temp))
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['rightPadding'], $temp))
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['leftPadding'], $temp))
	$bErrorFlag = true;

// 썸네일 여백 값에 오류가 있으면 이전 값으로 되돌림.
if ($bErrorFlag == true)
	$errorArray['padding'] = getThumbnailPadding();

if ($bErrorFlag == false) {
	$_POST['topPadding'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['topPadding']);
	$_POST['bottomPadding'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['bottomPadding']);
	$_POST['rightPadding'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['rightPadding']);
	$_POST['leftPadding'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['leftPadding']);
	
	if (empty($_POST['topPadding']))
		$_POST['topPadding'] = 0;
	if (empty($_POST['bottomPadding']))
		$_POST['bottomPadding'] = 0;
	if (empty($_POST['rightPadding']))
		$_POST['rightPadding'] = 0;
	if (empty($_POST['leftPadding']))
		$_POST['leftPadding'] = 0;
	
	$strNewPadding = "{$_POST['topPadding']}|{$_POST['rightPadding']}|{$_POST['bottomPadding']}|{$_POST['leftPadding']}";
	$strOldPadding = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'thumbnailPadding'");
	
	if ($strOldPadding == false) {
		DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'thumbnailPadding', '$strNewPadding')");
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewPadding != $strOldPadding) {
		DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '$strNewPadding' WHERE `user` = $owner AND `name` = 'thumbnailPadding'");
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	}
}

// 썸네일 여백 색상.
if (eregi("^#?([A-F0-9]{3,6})$", $_POST['paddingColor'], $temp)) {
	$strNewColor = $temp[1];
	$strOldColor = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
	
	if ($strOldColor == false) {
		DBQuery::execute("INSERT `{$database['prefix']}UserSettings` (`user`, `name`, `value`) VALUES ($owner, 'thumbnailPaddingColor', '$strNewColor')");
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewColor != $strOldColor) {
		DBQuery::execute("UPDATE `{$database['prefix']}UserSettings` SET `value` = '$strNewColor' WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
		deleteAllThumbnails(ROOT."/cache/thumbnail/$owner");
	}
} else {
	$errorArray['paddingColor'] = getThumbnailPaddingColor();
}

if (count($errorArray) > 0)
	$errorArray['error'] = 1;
else
	$errorArray['error'] = 0;

$isAjaxRequest ? printRespond($errorArray) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>