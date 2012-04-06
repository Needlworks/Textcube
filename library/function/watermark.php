<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// 사용되지 않는 함수이나 만약을 위해 남겨둠.
function isLarge($target, $maxX, $maxY) {
	if (!file_exists($target)) return false;
	$size = getImageSize($target);
	$sx = $size[0];
	$sy = $size[1];
	if (strpos($maxX, "%") && strpos($maxY, "%")) {
		return false;
	}
	if (($sx > $maxX) || ($sy > $maxY)) {
		return true;
	} else {
		return false;
	}
}

// 사용되지 않는 함수이나 만약을 위해 남겨둠.
function resizing($maxX, $maxY, $src_file, $tag_file) {
	if (!file_exists($src_file)) return false;
	list($width, $height, $type, $attr) = getimagesize($src_file);
	if ($type == 1) {
		$src_img = imagecreatefromgif($src_file);
	} else if ($type == 2) {
		$src_img = imagecreatefromjpeg($src_file);
	} else if ($type == 3) {
		$src_img = imagecreatefrompng($src_file);
	}
	$sx = imagesx($src_img);
	$sy = imagesy($src_img);
	$xratio = $sx / $maxX;
	$yratio = $sy / $maxY;
	$ratio = max($xratio, $yratio);
	$targ_Y = $sy / $ratio;
	$targ_X = $sx / $ratio;
	$dst_img = ImageCreateTrueColor($targ_X, $targ_Y);
	$colorSetting = ImageColorAllocate($dst_img, 255, 255, 255);
	ImageFilledRectangle($dst_img, 0, 0, $maxX, $maxY, $colorSetting);
	ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $targ_X, $targ_Y, $sx, $sy);
	if ($type == 1) {
		imagegif($dst_img, $tag_file, 100);
	} else if ($type == 2) {
		imagejpeg($dst_img, $tag_file, 100);
	} else if ($type == 3) {
		imagepng($dst_img, $tag_file, 100);
	}
	ImageDestroy($dst_img);
	ImageDestroy($src_img);
	return true;
}

function deleteAllThumbnails($path) {
	deleteFilesByRegExp($path, "*");
	return true;
}

function getWaterMarkPosition() {
	$waterMarkPosition = Setting::getBlogSettingGlobal("waterMarkPosition", "left=10|bottom=10");

	list($horizontalPos, $verticalPos) = explode("|", $waterMarkPosition);
	$horizontalPos = explode("=", $horizontalPos);
	$verticalPos = explode("=", $verticalPos);

	if ($horizontalPos[0] == "left") {
		if ($horizontalPos[1] > 0) {
			$horizontalValue = $horizontalPos[1];
		} else {
			$horizontalValue = "left";
		}
	} else if ($horizontalPos[0] == "center") {
		$horizontalValue = "center";
	} else if ($horizontalPos[0] == "right") {
		if ($horizontalPos[1] > 0) {
			$horizontalValue = $horizontalPos[1] - $horizontalPos[1] * 2;
		} else {
			$horizontalValue = "right";
		}
	}
	if ($verticalPos[0] == "top") {
		if ($verticalPos[1] > 0) {
			$verticalValue = $verticalPos[1];
		} else {
			$verticalValue = "top";
		}
	} else if ($verticalPos[0] == "middle") {
		$verticalValue = "middle";
	} else if ($verticalPos[0] == "bottom") {
		if ($verticalPos[1] > 0) {
			$verticalValue = $verticalPos[1] - $verticalPos[1] * 2;
		} else {
			$verticalValue = "bottom";
		}
	}

	return "$horizontalValue $verticalValue";
}

function getWaterMarkGamma() {
	return 100;//intval(Setting::getBlogSettingGlobal("gammaForWaterMark", "100"));
}

function getThumbnailPadding() {
	$thumbnailPadding = Setting::getBlogSettingGlobal("thumbnailPadding", false);
	if ($thumbnailPadding == false) {
		return array("top" => 0, "right" => 0, "bottom" => 0, "left" => 0);
	} else {
		$tempArray = explode("|", $thumbnailPadding);
		return array("top" => intval($tempArray[0]), "right" => intval($tempArray[1]), "bottom" => intval($tempArray[2]), "left" => intval($tempArray[3]));
	}
}

function getThumbnailPaddingColor() {
	return Setting::getBlogSettingGlobal("thumbnailPaddingColor", "FFFFFF");
}
?>
