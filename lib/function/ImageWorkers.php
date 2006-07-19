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

// img의 width/height에 맞춰 이미지를 리샘플링하는 함수. 썸네일 함수가 아님! 주의.
function makeThumbnail($imgString, $originSrc) {
	global $database, $owner, $blogURL, $waterMarkArray, $paddingArray;
	
	if (!extension_loaded('gd') || eregi(' src="http://[^"]+"', $imgString, $extra)) {
		return $imgString;
	}
		
	if (eregi('class="tt-thumbnail"', $imgString, $extra)) {
		$originFileName = basename($originSrc);
		
		// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음. 
		if (ereg('width="([1-9][0-9]*)"', $imgString, $temp)) {
			$tempWidth = $temp[1];
		}
		
		// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음. 
		if (ereg('height="([1-9][0-9]*)"', $imgString, $temp)) {
			$tempHeight = $temp[1];
		}
		
		$newTempFileName = eregi_replace("\.([[:alnum:]]+)$", ".thumbnail.\\1", $originFileName);
		$tempSrc = ROOT."/cache/thumbnail/$owner/$newTempFileName";
		
		// 보안상 cache 디렉토리를 공개하지 않도록 남겨놓는다.
		$tempURL = $blogURL."/thumbnail/$owner/$newTempFileName";
		
		if (!file_exists($tempSrc)) {
			$originImageInfo = getimagesize($originSrc);
			
			// 축소된 사이즈의 이미지면 리사이즈.
			if ($originImageInfo[0] > $tempWidth || $originImageInfo[1] > $tempHeight) {
				// 새 썸네일 생성.
				@copy(ROOT."/attach/$owner/$originFileName", $tempSrc);
				if (resampleImage($tempWidth, $tempHeight, $tempSrc, "reduce", "file", $paddingArray, $waterMarkArray)) {
					$tempImageInfo = getImagesize($tempSrc);
					$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
					$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempImageInfo[0].'"', $imgString);
					$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempImageInfo[1].'"', $imgString);
				} else {
					@unlink($tempSrc);
				}
			// 원본 사이즈 그대로이거나 확대 이미지여도, 워터마크나 여백이 존재하면 썸네일 생성.
			} else if (($originImageInfo[0] <= $tempWidth || $originImageInfo[1] <= $tempHeight) && (file_exists($waterMarkPath) || !empty($padding))) {
				// 새 썸네일 생성.
				@copy(ROOT."/attach/$owner/$originFileName", $tempSrc);
				if (resampleImage($tempWidth, $tempHeight, $tempSrc, "reduce", "file", $paddingArray, $waterMarkArray)) {
					$tempImageInfo = getImagesize($tempSrc);
					$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
					$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempImageInfo[0].'"', $imgString);
					$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempImageInfo[1].'"', $imgString);
				} else {
					@unlink($tempSrc);
				}
			}
		} else {
			$thumbnailImageInfo = getimagesize($tempSrc);
			$resizedWidth = $tempWidth - $paddingArray['left'] - $paddingArray['right'];
			$resizedHeight = ceil($tempHeight * $resizedWidth / $tempWidth) + $paddingArray['top'] + $paddingArray['bottom'];
			
			// 축소된 사이즈의 이미지면 리사이즈.
			if ($thumbnailImageInfo[0] > $tempWidth || $thumbnailImageInfo[1] > $resizedHeight) {
				// 이 파일과 관련된 기존 파일을 지운다.
				deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner/", "^".eregi_replace("\.([[:alnum:]]+)$", "\.", $originFileName));
				
				// 새 썸네일 생성.
				@copy(ROOT."/attach/$owner/$originFileName", $tempSrc);
				if (resampleImage($tempWidth, $tempHeight, $tempSrc, "reduce", "file", $paddingArray, $waterMarkArray)) {
					$tempImageInfo = getImagesize($tempSrc);
					$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
					$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempImageInfo[0].'"', $imgString);
					$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempImageInfo[1].'"', $imgString);
				} else {
					@unlink($tempSrc);
				}
			} else {
				// 리사이즈된 파일이 이미 존재하므로 통과.
				$tempImageInfo = getImagesize($tempSrc);
				$imgString = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $imgString);
				$imgString = eregi_replace('width="([^"]+)"', 'width="'.$tempImageInfo[0].'"', $imgString);
				$imgString = eregi_replace('height="([^"]+)"', 'height="'.$tempImageInfo[1].'"', $imgString);
			}
		}
	} else {
		// 에러.
	}

	return $imgString;
}

function resampleImage($width=NULL, $height=NULL, $fileName=NULL, $resizeFlag=NULL, $outputType="file", $padding=NULL, $waterMark=NULL)
{
	$path = eregi("/$", dirname($fileName), $temp) ? dirname($fileName) : dirname($fileName).'/';
	$fileName = basename($fileName);
	
	// 원하는 크기가 정해지지 않았으면 그냥 돌려줌.
	if (empty($width) && empty($height)) {
		return true;
	}
	
	// 원본파일이 존재하는가.
	if ($tempInfo = getimagesize($path.$fileName)) {
		$originWidth = $tempInfo[0];
		$originHeight = $tempInfo[1];
	} else {
		return false;
	}
	
	// 리사이징 스타일은 'both'가 디폴트.
	if (empty($resizeFlag) || ($resizeFlag != "enlarge" && $resizeFlag != "reduce" && $resizeFlag != "both")) {
		$resizeFlag = "both";
	}
	
	// 출력방식 유효성 검사.
	if ($outputType != "file" && $outputType != "browser") {
		$outputType = "file";
	}
	
	// 여백 유효성 검사.
	if (!is_null($padding)) {
		if (!isset($padding['top']) || !is_int($padding['top'])) {
			$padding['top'] = 0;
		}
		if (!isset($padding['right']) || !is_int($padding['right'])) {
			$padding['right'] = 0;
		}
		if (!isset($padding['bottom']) || !is_int($padding['bottom'])) {
			$padding['bottom'] = 0;
		}
		if (!isset($padding['left']) || !is_int($padding['left'])) {
			$padding['left'] = 0;
		}
	}
	
	// bgColor 유효성 검사.
	if (!eregi("^[0-9A-F]{3,6}$", $padding['bgColor'], $temp)) {
	//if (!eregi("^[0-9A-F]{3,6}$", $padding['bgColor'], $temp) && $padding['bgColor'] != "transparent") {
		$padding['bgColor'] = "FFFFFF";
	}
	
	// 이미지 크기 조정.
	if (!is_null($width) && is_null($height)) {
		if ($width > $originWidth) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgWidth = $width;
			} else if ($resizeFlag == "reduce") {
				$imgWidth = $originWidth;
			}
			$imgHeight = ceil($originHeight * $imgWidth / $originWidth);
		} else if ($width < $originWidth) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgWidth = $width;
			} else if ($resizeFlag == "enlarge") {
				$imgWidth = $originWidth;
			}
			$imgHeight = ceil($originHeight * $imgWidth / $originWidth);
		} else {
			return true;
		}
	} else if (is_null($width) && !is_null($height)) {
		if ($height > $originHeight) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgHeight = $height;
			} else if ($resizeFlag == "reduce") {
				$imgHeight = $originWidth;
			}
			$imgWidth = ceil($originWidth * $imgHeight / $originHeight);
		} else if ($height < $originHeight) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgHeight = $height;
			} else if ($resizeFlag == "enlarge") {
				$imgHeight = $originHeight;
			}
			$imgWidth = ceil($originWidth * $imgHeight / $originHeight);
		} else {
			return true;
		}
	} else if (!is_null($width) && !is_null($height)) {
		if ($width > $originWidth) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgWidth = $width;
			} else if ($resizeFlag == "reduce") {
				$imgWidth = $originWidth;
			}
		} else if ($width < $originWidth) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgWidth = $width;
			} else if ($resizeFlag == "enlarge") {
				$imgWidth = $originWidth;
			}
		} else {
			$imgWidth = $width;
		}
		
		if ($height > $originHeight) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgHeight = $height;
			} else if ($resizeFlag == "reduce") {
				$imgHeight = $originWidth;
			}
		} else if ($height < $originHeight) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgHeight = $height;
			} else if ($resizeFlag == "enlarge") {
				$imgHeight = $originHeight;
			}
		} else {
			$imgHeight = $height;
		}
		
		if ($imgWidth == $originWidth && $imgHeight == $originHeight) {
			return true;
		}
	}
	
	// 여백 값이 존재한다면 $width, $height의 값은 여백값을 포함한 크기이다. 따라서 여백 값을 뺀다.
	// |--------------------- $width ---------------------|
	// |-- 좌측 여백 --||-- $new_width --||-- 우측 여백 --|
	$imgWidth = $imgWidth - $padding['left'] - $padding['right'];
	($imgWidth < 0) ? $imgWidth = 0 : NULL;
	if (($imgWidth + $padding['left'] + $padding['right']) > 0) {
		$imgHeight = ceil($imgHeight * $imgWidth / ($imgWidth + $padding['left'] + $padding['right']));
	} else {
		$imgHeight = ceil($imgHeight * $imgWidth / 0.001);
	}
	($imgHeight < 0) ? $imgHeight = 0 : NULL;
	
	// 원본의 포맷(확장자가 아님)에 해당하는 이미지 생성.
	switch (getImageType($path.$fileName)) {
		case "gif":
			if (imagetypes() & IMG_GIF) {
				$tempSource = imagecreatefromgif($path.$fileName);
			} else {
				return false;
			}
			break;
		case "jpg":
			if (imagetypes() & IMG_JPG) {
				$tempSource = imagecreatefromjpeg($path.$fileName);
			} else {
				return false;
			}
			break;
		case "png":
			if (imagetypes() & IMG_PNG) {
				$tempSource = imagecreatefrompng($path.$fileName);
			} else {
				return false;
			}
			break;
		case "wbmp":
			if (imagetypes() & IMG_WBMP) {
				$tempSource = imagecreatefromwbmp($path.$fileName);
			} else {
				return false;
			}
			break;
		case "xpm":
			if (imagetypes() & IMG_XPM) {
				$tempSource = imagecreatefromxpm($path.$fileName);
			} else {
				return false;
			}
			break;
		default:
			return false;
			break;
	}
	
	// 임시 이미지 파일명 생성.
	srand((double) microtime()*1000000);
	$tempImage = rand(0, 100000);
	
	// 새로운 트루타입 이미지 디바이스를 생성.
	if (getFileExtension($fileName) == "gif") {
		$tempResultImage = imagecreate($imgWidth + $padding['left'] + $padding['right'], $imgHeight + $padding['top'] + $padding['bottom']);
	} else {
		$tempResultImage = imagecreatetruecolor($imgWidth + $padding['left'] + $padding['right'], $imgHeight + $padding['top'] + $padding['bottom']);
	}
	
	// 이미지 디바이스의 여백 배경색을 채운다.
	if ($padding['bgColor'] == "transparent") {
		$bgColorBy16 = hexRGB("FF0000");
		$temp = imagecolorallocate($tempResultImage, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
		imagefilledrectangle($tempResultImage, 0, 0, $imgWidth + $padding['left'] + $padding['right'], $imgHeight + $padding['top'] + $padding['bottom'], $temp);
		imagecolortransparent($tempResultImage, $temp);
	} else {
		//imagealphablending($tempResultImage, 0); //bgColor가 대신 alpha blending을 막아줌.
		$bgColorBy16 = hexRGB($padding['bgColor']);
		$temp = imagecolorallocate($tempResultImage, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
		imagefilledrectangle($tempResultImage, 0, 0, $imgWidth + $padding['left'] + $padding['right'], $imgHeight + $padding['top'] + $padding['bottom'], $temp);
	}
	
	// 이미지 디바이스에 크기가 조정된 원본 이미지를 여백을 적용하여 붙인다.
	imagecopyresampled($tempResultImage, $tempSource, $padding['left'], $padding['top'], 0, 0, $imgWidth, $imgHeight, imagesx($tempSource), imagesy($tempSource));
	
	// 워터 마크 붙이기.
	if ($waterMarkInfo = getimagesize($waterMark['path'])) {
		$waterMarkWidth = $waterMarkInfo[0];
		$waterMarkHeight = $waterMarkInfo[1];
		
		// 워터 마크 포지션.
		if (eregi("^(\-?[0-9A-Z]+) (\-?[0-9A-Z]+)$", $waterMark['position'], $temp)) {
			$resultWidth = imagesx($tempResultImage);
			$resultHeight = imagesy($tempResultImage);
			
			$extraPadding = 0;
			switch ($temp[1]) {
				case "left":
					if ($waterMarkWidth > ($resultWidth - $extraPadding)) {
						$waterMarkWidth = $resultWidth - $extraPadding;
						$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkWidth);
					}
					$xPosition = $extraPadding;
					break;
				case "center":
					if ($waterMarkWidth > $resultWidth) {
						$waterMarkWidth = $resultWidth;
						$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
					}
					$xPosition = ($imgWidth + $padding['left'] + $padding['right']) / 2 - $waterMarkWidth / 2;
					break;
				case "right":
					if ($waterMarkWidth > ($resultWidth - $extraPadding)) {
						$waterMarkWidth = $resultWidth - $extraPadding;
						$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
					}
					$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth - $extraPadding;
					break;
				default:
					// 양수인 경우, 왼쪽부터 x좌표 값을 계산한다.
					if (eregi("^([1-9][0-9]*)$", $temp[1], $extra)) {
						if ($waterMarkWidth > ($resultWidth - $extra[1])) {
							$waterMarkWidth = $resultWidth - $extra[1];
							$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
						}
						if ($extra[1] > $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth) {
							$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth;
						} else {
							$xPosition = $extra[1];
						}
					// 음수인 경우, 오른쪽부터 x좌표 값을 계산한다.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[1], $extra)) {
						if ($waterMarkWidth > ($resultWidth + $extra[1])) {
							$waterMarkWidth = $resultWidth + $extra[1];
							$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
						}
						if ($imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth + $extra[1] < 0) {
							$xPosition = 0;
						} else {
							$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth + $extra[1];
						}
					// 0인 경우.
					} else if ($temp[1] == "0") {
						if ($waterMarkWidth > $resultWidth) {
							$waterMarkWidth = $resultWidth;
							$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
						}
						$xPosition = 0;
					// 나머지 경우는 임의 여백으로 우측에 붙인다.
					} else {
						if ($waterMarkWidth > ($resultWidth - $extraPadding)) {
							$waterMarkWidth = $resultWidth - $extraPadding;
							$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
						}
						$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth - $extraPadding;
					}
			}
			
			switch ($temp[2]) {
				case "top":
					if ($waterMarkHeight > ($resultHeight - $extraPadding)) {
						$tempHeight = $waterMarkHeight;
						$waterMarkHeight = $resultHeight - $extraPadding;
						$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
					}
					$yPosition = $extraPadding;
					break;
				case "middle":
					if ($waterMarkHeight > $resultHeight) {
						$tempHeight = $waterMarkHeight;
						$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
						$waterMarkHeight = $resultHeight;
					}
					$yPosition = ($imgHeight + $padding['top'] + $padding['bottom']) / 2 - $waterMarkHeight / 2;
					break;
				case "bottom":
					if ($waterMarkHeight > ($resultHeight - $extraPadding)) {
						$tempHeight = $waterMarkHeight;
						$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
						$waterMarkHeight = $resultHeight - $extraPadding;
					}
					$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight - $extraPadding;
					break;
				default:
					// 양수인 경우, 위부터 y좌표 값을 계산한다.
					if (eregi("^([1-9][0-9]*)$", $temp[2], $extra)) {
						if ($waterMarkHeight > ($resultHeight - $extra[1])) {
							$tempHeight = $waterMarkHeight;
							$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
							$waterMarkHeight = $resultHeight - $extra[1];
						}
						if ($extra[1] > $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight) {
							$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight;
						} else {
							$yPosition = $extra[1];
						}
					// 음수인 경우, 아래부터 y좌표 값을 계산한다.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[2], $extra)) {
						if ($waterMarkHeight > ($resultHeight - $extra[1])) {
							$tempHeight = $waterMarkHeight;
							$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
							$waterMarkHeight = $resultHeight - $extra[1];
						}
						if ($imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight + $extra[1] < 0) {
							$yPosition = 0;
						} else {
							$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight + $extra[1];
						}
					// 0인 경우.
					} else if ($temp[1] == "0") {
						if ($waterMarkHeight > $resultHeight) {
							$tempHeight = $waterMarkHeight;
							$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
							$waterMarkHeight = $resultHeight;
						}
						$yPosition = 0;
					// 나머지 경우는 임의 여백으로 아래에 붙인다.
					} else {
						if ($waterMarkHeight > ($resultHeight - $extraPadding)) {
							$tempHeight = $waterMarkHeight;
							$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $tempHeight);
							$waterMarkHeight = $resultHeight - $extraPadding;
						}
						$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight - $extraPadding;
					}
			}
		} else {
			if ($waterMarkWidth > ($resultWidth - $extraPadding)) {
				$waterMarkWidth = $resultWidth - $extraPadding;
				$waterMarkHeight = ceil($waterMarkHeight * $waterMarkWidth / $waterMarkInfo[0]);
			}
			$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkInfo[0] - $extraPadding;
			
			if ($waterMarkHeight > ($resultHeight - $extraPadding)) {
				$waterMarkWidth = ceil($waterMarkWidth * $waterMarkHeight / $waterMarkInfo[1]);
				$waterMarkHeight = $resultHeight - $extraPadding;
			}
			$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkInfo[1] - $extraPadding;
		}
		
		// 감마값 유효성 검사.
		if (!is_int($waterMark['gamma'])) {
			$waterMark['gamma'] = 100;
		} else if ($waterMark['gamma'] < 0) {
			$waterMark['gamma'] = 0;
		} else if ($waterMark['gamma'] > 100) {
			$waterMark['gamma'] = 100;
		}
		
		// 워터 마크 이미지 디바이스 생성.
		if ($waterMarkInfo[2] == 1) {
			$tempWaterMark = imagecreatefromgif($waterMark['path']);
		} else if ($waterMarkInfo[2] == 2) {
			$tempWaterMark = imagecreatefromjpeg($waterMark['path']);
		} else if ($waterMarkInfo[2] == 3) {
			$tempWaterMark = imagecreatefrompng($waterMark['path']);
		}
		
		if ($waterMarkInfo[0] != $waterMarkWidth || $waterMarkInfo[1] != $waterMarkHeight) {
			// 새로운 트루타입 이미지 디바이스를 생성.
			$tempWaterMarkSource = imagecreatetruecolor($waterMarkWidth, $waterMarkHeight);
			
			//imagealphablending($tempResultImage, 0); //bgColor가 대신 alpha blending을 막아줌.
			//$bgColorBy16 = hexRGB("FFFFFF");
			//$temp = imagecolorallocate($tempWaterMark, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
			//imagefilledrectangle($tempWaterMark, 0, 0, $waterMarkWidth, $waterMarkHeight, $temp);
			
			// 이미지 디바이스에 크기가 조정된 원본 이미지를 여백을 적용하여 붙인다.
			imagecopyresampled($tempWaterMarkSource, $tempWaterMark, 0, 0, 0, 0, $waterMarkWidth, $waterMarkHeight, imagesx($tempWaterMark), imagesy($tempWaterMark));
		}
		
		if (function_exists("imagecopymerge")) {
			imagecopymerge($tempResultImage, $tempWaterMarkSource, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight, $waterMark['gamma']);
		} else {
			imagecopy($tempResultImage, $tempWaterMarkSource, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight);
		}
	}
	
	// 알맞는 포맷으로 저장.
	if ($outputType == "file") {
		if (getFileExtension($fileName) == "gif") {
			imageinterlace($tempResultImage);
			imagegif($tempResultImage, $path.$tempImage);
		} else if (getFileExtension($fileName) == "jpg" || getFileExtension($fileName) == "jpeg") {
			imageinterlace($tempResultImage);
			imagejpeg($tempResultImage, $path.$tempImage, 80);
		} else if (getFileExtension($fileName) == "png") {
			imagepng($tempResultImage, $path.$tempImage);
		} else if (getFileExtension($fileName) == "wbmp") {
			imagewbmp($tempResultImage, $path.$tempImage);
		}
		
		// 임시 이미지 삭제.
		imagedestroy($tempResultImage);
		imagedestroy($tempSource);
		if (file_exists($waterMark['path'])) {
			imagedestroy($tempWaterMarkSource);
		}
		
		if (file_exists($path.$fileName)) {
			unlink($path.$fileName);
		}
		
		// 원래 이미지 명칭으로 리네임.
		rename($path.$tempImage, $path.$fileName);
		return true;
	// 브라우저로 전송.
	} else {
		header("Content-type: image/jpeg");
		imagejpeg($tempResultImage);
		return $bResult;
		
		/*header("Content-type: image/".(getFileExtension($fileName) == "jpg" ? "jpeg" : getFileExtension($fileName)));
		// getFileExtension()와 getImageType()의 차이에 주의할 것.
		
		$bResult = false;
		switch (getFileExtension($fileName)) {
			case "gif":
				imageinterlace($tempResultImage);
				imagegif($tempResultImage);
				$bResult = true;
				break;
			case "jpg":
			case "jpeg":
				imageinterlace($tempResultImage);
				imagejpeg($tempResultImage);
				$bResult = true;
				break;
			case "png":
				imagepng($tempResultImage);
				$bResult = true;
				break;
			case "wbmp":
				imagewbmp($tempResultImage);
				$bResult = true;
				break;
		}
		return $bResult;*/
	}
}

function hexRGB($hexstr)
{
	$int = hexdec($hexstr);
	return array('R' => 0xFF & ($int >> 0x10), 'G' => 0xFF & ($int >> 0x8), 'B' => 0xFF & $int);
}

function getImageType($filename)
{
	if (file_exists($filename)) {
		if (function_exists("exif_imagetype")) {
			$imageType = exif_imagetype($filename);
		} else {
			$tempInfo = getimagesize($filename);
			$imageType = $tempInfo[2];
		}
		
		switch ($imageType) {
			// 상수를 사용하면 에러? 확인 못함.
			case IMAGETYPE_GIF:
				$extension = 'gif';
				break;
			case IMAGETYPE_JPEG:
				$extension = 'jpg';
				break;
			case IMAGETYPE_PNG:
				$extension = 'png';
				break;
			case IMAGETYPE_SWF:
				$extension = 'swf';
				break;
			case IMAGETYPE_PSD:
				$extension = 'psd';
				break;
			case IMAGETYPE_BMP:
				$extension = 'bmp';
				break;
			case IMAGETYPE_TIFF_II:
			case IMAGETYPE_TIFF_MM:
				$extension = 'tiff';
				break;
			case IMAGETYPE_JPC:
				$extension = 'jpc';
				break;
			case IMAGETYPE_JP2:
				$extension = 'jp2';
				break;
			case IMAGETYPE_JPX:
				$extension = 'jpx';
				break;
			case IMAGETYPE_JB2:
				$extension = 'jb2';
				break;
			case IMAGETYPE_SWC:
				$extension = 'swc';
				break;
			case IMAGETYPE_IFF:
				$extension = 'aiff';
				break;
			case IMAGETYPE_WBMP:
				$extension = 'wbmp';
				break;
			case IMAGETYPE_XBM:
				$extension = 'xbm';
				break;
			default:
				$extension = false;
		}
	} else {
		$extension = false;
	}
	
	return $extension;
}

function deleteAllThumbnails($path) {
	deleteFilesByRegExp($path, "*");
	return true;
}

function getWaterMarkPosition() {
	global $database, $owner;
	
	$waterMarkPosition = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'waterMarkPosition'");
	if ($waterMarkPosition == false) {
		$waterMarkPosition = "left=10|bottom=10";
	}
	
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
	global $database, $owner;
	
	$gammaForWaterMark = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'gammaForWaterMark'");
	if ($gammaForWaterMark == false) {
		return 100;
	} else {
		return intval($gammaForWaterMark);
	}
}

function getThumbnailPadding() {
	global $database, $owner;
	
	$thumbnailPadding = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'thumbnailPadding'");
	if ($thumbnailPadding == false) {
		return array("top" => 25, "right" => 25, "bottom" => 25, "left" => 25);
	} else {
		$tempArray = explode("|", $thumbnailPadding);
		return array("top" => intval($tempArray[0]), "right" => intval($tempArray[1]), "bottom" => intval($tempArray[2]), "left" => intval($tempArray[3]));
	}
}

function getThumbnailPaddingColor() {
	global $database, $owner;
	
	$bgColorForPadding = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
	if ($bgColorForPadding == false) {
		return "FFFFFF"; 
	} else {
		return $bgColorForPadding;
	}
}
?>
