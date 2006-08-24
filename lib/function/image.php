<?php
// 사용되지 않는 함수이나 만약을 위해 남겨둠.
function isLarge($target, $maxX, $maxY) {
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
	$waterMarkPosition = getUserSetting("waterMarkPosition", "left=10|bottom=10");
	
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
	return 100;//intval(getUserSetting("gammaForWaterMark", "100"));
}

function getThumbnailPadding() {
	$thumbnailPadding = getUserSetting("thumbnailPadding", false);
	if ($thumbnailPadding == false) {
		return array("top" => 0, "right" => 0, "bottom" => 0, "left" => 0);
	} else {
		$tempArray = explode("|", $thumbnailPadding);
		return array("top" => intval($tempArray[0]), "right" => intval($tempArray[1]), "bottom" => intval($tempArray[2]), "left" => intval($tempArray[3]));
	}
}

function getThumbnailPaddingColor() {
	return getUserSetting("thumbnailPaddingColor", "FFFFFF");
}

// img의 width/height에 맞춰 이미지를 리샘플링하는 함수. 썸네일 함수가 아님! 주의.
function makeThumbnail($imgString, $originSrc, $paddingArray=NULL, $waterMarkArray=NULL) {
	global $database, $owner, $blogURL;
	$contentWidth = getContentWidth();
	
	if (!eregi(' src="http://[^"]+"', $imgString) && eregi('class="tt-thumbnail"', $imgString, $extra)) {
		$originFileName = basename($originSrc);
		
		// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음. 
		if (ereg('width="([1-9][0-9]*)"', $imgString, $temp)) {
			$tempWidth = $temp[1];
		}
		
		if (ereg('height="([1-9][0-9]*)"', $imgString, $temp)) {
			$tempHeight = $temp[1];
		}
		
		$newTempFileName = eregi_replace("\.([[:alnum:]]+)$", ".x$tempWidth-y$tempHeight.thumbnail.\\1", $originFileName);
		$tempSrc = ROOT."/cache/thumbnail/$owner/".$newTempFileName;
		
		// 보안상 cache 디렉토리를 공개하지 않도록 남겨놓는다.
		$tempURL = $blogURL."/thumbnail/$owner/".$newTempFileName;
		
		$checkResult = Image::checkExistingThumbnail($originSrc, $tempSrc, $tempWidth, $tempHeight, $paddingArray, $waterMarkArray);
		switch ($checkResult) {
			case 1:
				deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner/", "^".eregi_replace("\.([[:alnum:]]+)$", "\.", $originFileName));
			case 2:
				$isSuccessful = true;
				$AttachedImage = new Image();
				$AttachedImage->imageFile = $originSrc;
				
				if ($AttachedImage->resample($tempWidth, $tempHeight, $paddingArray)) {
					$waterMarkType = $AttachedImage->getImageType($waterMarkArray['path']);
					$AttachedImage->impressWaterMark($waterMarkArray['path'], $waterMarkArray['position'], $waterMarkArray['gamma']);
					if ($AttachedImage->createThumbnailIntoFile($tempSrc)) {
						$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
						$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempWidth.'"', $imgString);
						$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempHeight.'"', $imgString);
					} else {
						$isSuccessful = false;
					}
				} else {
					$isSuccessful = false;
				}
				
				if ($isSuccessful == false) {
					$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempWidth.'"', $imgString);
					$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempHeight.'"', $imgString);
				}
				
				unset($AttachedImage);
				break;
			default:
				$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
				$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempWidth.'"', $imgString);
				$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempHeight.'"', $imgString);
				break;
		}
	}
	
	return $imgString;
}
?>