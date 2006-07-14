<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';

// 워터마크 처리.
if ($_POST['deleteWaterMark'] == "yes") {
	unlink(ROOT."/attach/$owner/watermark.gif");
}

if (!empty($_FILES['waterMark']['tmp_name'])) {
	$fileExt = Path::getExtension($_FILES['waterMark']['name']);
	
	if (($fileExt != '.gif') && ($fileExt != '.jpg') && ($fileExt != '.png')) {
		print('alert("' . _t('변경하지 못했습니다.') . '");');
	} else { 
		requireComponent('Tattertools.Data.Attachment');
		Attachment::confirmFolder();

		if (move_uploaded_file($_FILES['waterMark']['tmp_name'], ROOT."/attach/$owner/watermark.gif")) {
			@chmod(ROOT . "/attach/$owner/watermark.gif", 0666);
			deleteThumbnails(ROOT."/cache/thumbnail/$owner");
		}
	}
}

// 워터마크 포지션.
$bErrorFlag = false;
if (strtolower($_POST['verticalType']) != "top" && strtolower($_POST['verticalType']) != "bottom")
	$bErrorFlag = true;
if (strtolower($_POST['horizontalType']) != "left" && strtolower($_POST['horizontalType']) != "right")
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['verticalPosition'], $temp))
	$bErrorFlag = true;
if (!eregi("^[0-9]+$", $_POST['horizontalPosition'], $temp))
	$bErrorFlag = true;

if ($bErrorFlag == false) {
	$_POST['verticalPosition'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['verticalPosition']);
	$_POST['verticalPosition'] = empty($_POST['verticalPosition']) ? 0 : $_POST['verticalPosition'];
	$_POST['horizontalPosition'] = eregi_replace("^0*([0-9]*)$", '\1', $_POST['horizontalPosition']);
	$_POST['horizontalPosition'] = empty($_POST['horizontalPosition']) ? 0 : $_POST['horizontalPosition'];
	
	$strNewPosition = "{$_POST['horizontalType']}={$_POST['horizontalPosition']}|{$_POST['verticalType']}={$_POST['verticalPosition']}";
	$strOldPosition = fetchQueryCell("SELECT `value` FROM `{$database['prefix']}userSettings` WHERE `user` = $owner AND `name` = 'waterMarkPosition'");
	
	if ($strOldPosition == false) {
		executeQuery("INSERT `{$database['prefix']}userSettings` (`user`, `name`, `value`) VALUES ($owner, 'waterMarkPosition', '$strNewPosition')");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewPosition != $strOldPosition) {
		executeQuery("UPDATE `{$database['prefix']}userSettings` SET `value` = '$strNewPosition' WHERE `user` = $owner AND `name` = 'waterMarkPosition'");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
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
	
	$strNewPadding = "{$_POST['topPadding']}|{$_POST['bottomPadding']}|{$_POST['rightPadding']}|{$_POST['leftPadding']}";
	$strOldPadding = fetchQueryCell("SELECT `value` FROM `{$database['prefix']}userSettings` WHERE `user` = $owner AND `name` = 'thumbnailPadding'");
	
	if ($strOldPadding == false) {
		executeQuery("INSERT `{$database['prefix']}userSettings` (`user`, `name`, `value`) VALUES ($owner, 'thumbnailPadding', '$strNewPadding')");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewPadding != $strOldPadding) {
		executeQuery("UPDATE `{$database['prefix']}userSettings` SET `value` = '$strNewPadding' WHERE `user` = $owner AND `name` = 'thumbnailPadding'");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
	}
}

// 썸네일 여백 색상.
if (eregi("^#?([A-F0-9]{3,6})$", $_POST['paddingColor'], $temp)) {
	$strNewColor = $temp[1];
	$strOldColor = fetchQueryCell("SELECT `value` FROM `{$database['prefix']}userSettings` WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
	
	if ($strOldColor == false) {
		executeQuery("INSERT `{$database['prefix']}userSettings` (`user`, `name`, `value`) VALUES ($owner, 'thumbnailPaddingColor', '$strNewColor')");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
	} else if ($strNewColor != $strOldColor) {
		executeQuery("UPDATE `{$database['prefix']}userSettings` SET `value` = '$strNewColor' WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
		deleteThumbnails(ROOT."/cache/thumbnail/$owner");
	}
} else {
	print(_t('색상지정이 올바르지 않습니다.'));
}

header("Location: ".$_SERVER['HTTP_REFERER']);
?>