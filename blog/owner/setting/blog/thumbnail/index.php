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
		print ('alert("' . _t('변경하지 못했습니다.') . '");');
	} else { 
		requireComponent('Tattertools.Data.Attachment');
		Attachment::confirmFolder();

		if (move_uploaded_file($_FILES['waterMark']['tmp_name'], ROOT."/attach/$owner/watermark.gif")) {
			@chmod(ROOT . "/attach/$owner/watermark.gif", 0666);
		}
	}
}

// 워터마크 포지션.
$_POST['verticalType'];
$_POST['verticalPosition'];
$_POST['horizontalType'];
$_POST['horizontalPosition'];

// 썸네일 여백
$_POST['topPadding'];
$_POST['topPaddingManual'];
$_POST['bottomPadding'];
$_POST['bottomPaddingManual'];
$_POST['rightPadding'];
$_POST['rightPaddingManual'];
$_POST['leftPadding'];
$_POST['leftPaddingManual'];

header("Location: ".$_SERVER['HTTP_REFERER']);
?>