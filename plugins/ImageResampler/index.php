<?php
// {{{ resampleImage()

// make image thumbnail for Tattertools 1.1 above.
function resampleImage($target, $mother) {
	global $skinSetting, $owner, $blogURL, $pluginURL;
	
	if (!extension_loaded('gd')) {
		return $target;
	}
	
	$pluginName = array_pop(explode('/', $pluginURL));
	$pluginConfig = ROOT."/cache/thumbnail/$owner/config.ini";
	
	if (!is_dir(ROOT."/cache/thumbnail/$owner")) { 
		@mkdir(ROOT."/cache/thumbnail");
		@chmod(ROOT."/cache/thumbnail", 0777);
		@mkdir(ROOT."/cache/thumbnail/$owner");
		@chmod(ROOT."/cache/thumbnail/$owner", 0777);
		
		$fp = fopen($pluginConfig, "w+");
		fclose($fp);
		@chmod($pluginConfig, 0777);
	}
	
	/* 사용자 설정 ************************************************************************************************/
	// 워터 마크 파일이 있는 곳.
	$waterMarkPath = ROOT."/skin/{$skinSetting['skin']}/images/img_watermark.png";
	
	// 워터 마크가 들어갈 장소의 x, y 좌표.
	// - x는 "left, center, right, 숫자", y는 "top, middle, bottom, 숫자" 중 입력할 수 있습니다.
	// - 숫자로 위치를 지정하실 경우 양수일 때는 좌측 상단 모서리가, 음수일 때는 우측 하단 모서리가 기준입니다.
	$waterMarkPosition = "center -5";
	
	// 워터 마크의 투명도.
	// - 100이면 완전불투명.
	// - 0이면 완전투명.(즉, 안 한거나 마찬가지.)
	$gammaForWaterMark = 100;
	
	// 여백의 크기.
	$padding = array("top" => 25, "right" => 25, "bottom" => 25, "left" => 25);
	
	// 여백의 색상.
	$bgColorForPadding = "FFFFFF"; // 투명은 transparent로 사용하도록 짰으나 IE 때문에(!!) 막았습니다. 땡스 빌을 탓하삼.
	/* ************************************************************************************************************/
	
	// 설정 파일에서 여백과 워터마크 정보를 가져온다.
	$initThumnails = false;
	$tempConfig = file($pluginConfig);
	ksort($padding);
	
	if (empty($tempConfig)) {
		$initThumnails = true;
	} else {
		foreach ($tempConfig as $line) {
			list($name, $value) = split("( )*=( )*", trim($line));
			switch ($name) {
				case "padding":
					if ($value != implode("-", $padding)) {
						$initThumnails = true;
					}
					break;
				case "waterMarkSize":
					$temp = @stat($waterMarkPath);
					$waterMarkSize = $temp[7];
					if ($value != $waterMarkSize) {
						$initThumnails = true;
					}
					break;
				case "waterMarkPosition":
					if ($value != $waterMarkPosition) {
						$initThumnails = true;
					}
					break;
			}
		}
	}
	
	if ($initThumnails == true) {
		// 임시 폴더의 리사이즈 파일 전부 삭제.
		deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner", "*");
		
		// 설정 파일에 새 여백과 워터마크 저장.
		$fp = fopen($pluginConfig, "w+");
		fwrite($fp, 'padding = '.implode("-", $padding).CRLF);
		fwrite($fp, "waterMarkSize = ".$waterMarkSize.CRLF);
		fwrite($fp, "waterMarkPosition = ".$waterMarkPosition);
		fclose($fp);
	}
	
	$waterMarkArray = array();
	$waterMarkArray['path'] = $waterMarkPath;
	$waterMarkArray['position'] = $waterMarkPosition;
	$waterMarkArray['gamma'] = $gammaForWaterMark;
	
	$paddingArray = array();
	$paddingArray['top'] = $padding['top'];
	$paddingArray['right'] = $padding['right'];
	$paddingArray['bottom'] = $padding['bottom'];
	$paddingArray['left'] = $padding['left'];
	$paddingArray['bgColor'] = $bgColorForPadding;
	
	if (eregi('class="tt-thumbnail"', $target, $extra)) {
		$originSrc = $mother;
		$originFileName = basename($originSrc);
		
		// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음. 
		if (ereg('width="([1-9][0-9]*%?)"', $target, $temp)) {
			$tempWidth = $temp[1];
		}
		
		// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음. 
		if (ereg('height="([1-9][0-9]*)"', $target, $temp)) {
			$tempHeight = $temp[1];
		}
		
		$originImageInfo = getimagesize(ROOT."/attach/$owner/$originFileName");
		$newTempFileName = eregi_replace("\.([[:alnum:]]+)$", ".x$tempWidth-y$tempHeight.\\1", $originFileName);
		$tempSrc = ROOT."/cache/thumbnail/$owner/$newTempFileName";
		// 보안상 cache 디렉토리를 공개하지 않도록 남겨놓는다.
		$tempURL = $blogURL."/thumbnail/$owner/$newTempFileName";
		
		if (!file_exists($tempSrc) || ($originImageInfo[0] > $tempWidth || $originImageInfo[1] > $tempHeight)) {
			// 이 파일과 관련된 기존 파일을 지운다.
			deleteFilesByRegExp(ROOT."/cache/thumbnail/$owner/", "^".eregi_replace("\.([[:alnum:]]+)$", "\.", $originFileName));
			
			// 새 썸네일 생성.
			@copy(ROOT."/attach/$owner/$originFileName", $tempSrc);
			if (resizeImage($tempWidth, $tempHeight, $tempSrc, "reduce", "file", $paddingArray, $waterMarkArray)) {
				$target = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $target);
			} else {
				@unlink($tempSrc);
			}
		} else {
			// 파일이 이미 존재하므로 통과.
			$target = eregi_replace('src="([^"]+)"', 'src="'.$tempURL.'"', $target);
		}
	} else {
		// 에러.
	}

	return $target;
}

// }}}
// {{{ resizeImage()

/**
 * Enlarge or reduce the image's size(width/height).
 *
 * @param string $fileName include the path.
 * @param integer $width.
 * @param integer $height.
 * @param string $resizeFlag.
 *		① "enlarge" - force an original image to be enlarged. reduce is not activated.
 *		② "reduce" - force an original image to be reduced. enlarge is not activated.
 *		③ "both" - enlarge and reduce.
 * @param string $outputType.
 *		① "file" - default. overwrite an original file.
 *		② "browser" - output header for brower directly.
 * @param array padding.
 *      ① 'top' => top padding.
 *		② 'right' => right padding.
 *		③ 'bottom' => bottom padding.
 *		④ 'left' => left padding.
 * @param array $waterMark.
 *      ① 'path' => water mark file path.
 *		② 'position' => position to be merged in a original file.
 *		③ 'gamma' => transparency level. min is 0, max is 100.
 * @access public
 * @return boolean/mime error / image mime.
 *		①  false - error.
 *		②  true - process success or not apply any process. this' not a error.
 */
function resizeImage($width=NULL, $height=NULL, $fileName=NULL, $resizeFlag=NULL, $outputType="file", $padding=NULL, $waterMark=NULL)
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
	
	// 여백 값이 존재한다면 $width, $height의 값은 여백값을 포함한 크기이다. 따라서 여백 값을 뺀다.
	// |--------------------- $width ---------------------|
	// |-- 좌측 여백 --||-- $new_width --||-- 우측 여백 --|
	$width = $width - $padding['left'] - $padding['right'];
	($width < 0) ? $width = 0 : NULL;
	$height = $height - $padding['top'] - $padding['bottom'];
	($height < 0) ? $height = 0 : NULL;
	
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
		
		// 워터 마크 이미지 디바이스 생성.
		if ($waterInfo[2] == 1) {
			$tempWaterMarkSource = imagecreatefromgif($waterMark['path']);
		} else if ($waterMarkInfo[2] == 2) {
			$tempWaterMarkSource = imagecreatefromjpeg($waterMark['path']);
		} else if ($waterMarkInfo[2] == 3) {
			$tempWaterMarkSource = imagecreatefrompng($waterMark['path']);
		}
		
		// 워터 마크 포지션.
		if (eregi("^(\-?[0-9A-Z]+) (\-?[0-9A-Z]+)$", $waterMark['position'], $temp)) {
			$extraPadding = 0;
			switch ($temp[1]) {
				case "left":
					$xPosition = 5;
					break;
				case "center":
					$xPosition = ($imgWidth + $padding['left'] + $padding['right']) / 2 - $waterMarkInfo[0] / 2;
					break;
				case "right":
					$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth - $extraPadding;
					break;
				default:
					// 양수인 경우, 왼쪽부터 x좌표 값을 계산한다.
					if (eregi("^([1-9][0-9]*)$", $temp[1], $extra)) {
						if ($extra[1] > $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth) {
							$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth;
						} else {
							$xPosition = $extra[1];
						}
					// 음수인 경우, 오른쪽부터 x좌표 값을 계산한다.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[1], $extra)) {
						if ($imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth + $extra[1] < 0) {
							$xPosition = 0;
						} else {
							$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth + $extra[1];
						}
					// 0인 경우.
					} else if (eregi("^0$", $temp[1], $extra)) {
						$xPosition = 0;
					// 나머지 경우는 임의 여백으로 우측에 붙인다.
					} else {
						$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth - $extraPadding;
					}
			}
			
			switch ($temp[2]) {
				case "top":
					$yPosition = 5;
					break;
				case "middle":
					$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] / 2 - $waterMarkInfo[1] / 2;
					break;
				case "bottom":
					$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight - $extraPadding;
					break;
				default:
					// 양수인 경우, 위부터 y좌표 값을 계산한다.
					if (eregi("^([1-9][0-9]*)$", $temp[2], $extra)) {
						if ($extra[1] > $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight) {
							$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight;
						} else {
							$yPosition = $extra[1];
						}
					// 음수인 경우, 아래부터 y좌표 값을 계산한다.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[2], $extra)) {
						if ($imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight + $extra[1] < 0) {
							$yPosition = 0;
						} else {
							$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight + $extra[1];
						}
					// 0인 경우.
					} else if (eregi("^0$", $temp[1], $extra)) {
						$yPosition = 0;
					// 나머지 경우는 임의 여백으로 아래에 붙인다.
					} else {
						$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight - $extraPadding;
					}
			}
		} else {
			$xPosition = $imgWidth + $padding['left'] + $padding['right'] - $waterMarkWidth - $extraPadding;
			$yPosition = $imgHeight + $padding['top'] + $padding['bottom'] - $waterMarkHeight - $extraPadding;
		}
		
		// 감마값 유효성 검사.
		if (!is_int($waterMark['gamma'])) {
			$waterMark['gamma'] = 100;
		} else if ($waterMark['gamma'] < 0) {
			$waterMark['gamma'] = 0;
		} else if ($waterMark['gamma'] > 100) {
			$waterMark['gamma'] = 100;
		}
		
		if (function_exists("imagecopymerge")) {
			imagecopymerge($tempResultImage, $tempWaterMarkSource, $xPosition, $yPosition, 0, 0, imagesx($tempWaterMarkSource), imagesy($tempWaterMarkSource), $waterMark['gamma']);
		} else {
			imagecopy($tempResultImage, $tempWaterMarkSource, $xPosition, $yPosition, 0, 0, imagesx($tempWaterMarkSource), imagesy($tempWaterMarkSource));
		}
	}
	
	// 알맞는 포맷으로 저장.
	if ($outputType == "file") {
		
		if (getFileExtension($fileName) == "gif") {
			imageinterlace($tempResultImage);
			imagegif($tempResultImage, $path.$tempImage);
		} else if (getFileExtension($fileName) == "jpg" || getFileExtension($fileName) == "jpeg") {
			imageinterlace($tempResultImage);
			imagejpeg($tempResultImage, $path.$tempImage);
		} else if (getFileExtension($fileName) == "png") {
			imagepng($tempResultImage, $path.$tempImage);
		} else if (getFileExtension($fileName) == "wbmp") {
			imagewbmp($tempResultImage, $path.$tempImage);
		}
		
		// 임시 이미지 삭제.
		imagedestroy($tempResultImage);
		imagedestroy($tempSource);
		imagedestroy($tempWaterMarkSource);
		
		if (file_exists($path.$fileName)) {
			unlink($path.$fileName);
		}
		
		// 원래 이미지 명칭으로 리네임.
		rename($path.$tempImage, $path.$fileName);
		return true;
	// 브라우저로 전송.
	} else {
		header("Content-type: image/".getFileExtension($fileName));
		// getFileExtension()와 getImageType()의 차이에 주의할 것.
		if (getFileExtension($fileName) == "gif") {
			imageinterlace($tempResultImage);
			imagegif($tempResultImage);
		} else if (getFileExtension($fileName) == "jpg" || getFileExtension($fileName) == "jpeg") {
			imageinterlace($tempResultImage);
			imagejpeg($tempResultImage);
		} else if (getFileExtension($fileName) == "png") {
			imagepng($tempResultImage);
		} else if (getFileExtension($fileName) == "wbmp") {
			imagewbmp($tempResultImage);
		} else {
			return false;
		}
		return true;
	}
}

// }}}
// {{{ hexRGB()

function hexRGB($hexstr)
{
	$int = hexdec($hexstr);
	return array('R' => 0xFF & ($int >> 0x10), 'G' => 0xFF & ($int >> 0x8), 'B' => 0xFF & $int);
}

// }}}
// {{{ deleteFilesByRegExp()

/**
 * Delete files by regular expression in a particular path.
 *
 * @param string $path directory path.
 * @param string $regexp regular expression for files to be deleted.
 * @access public
 * @return boolean true
 */
 
function deleteFilesByRegExp($path, $regexp) {
	$path = eregi("/$", $path, $temp) ? $path : $path."/";
	
	$handle = opendir($path);
	while ($tempFile = readdir($handle)) {
		if ($regexp == "*" || eregi("$regexp", $tempFile, $temp)) {
			@unlink($path.$tempFile);
		}
	}
	return true;
}

// }}}
// {{{ getImageType()
	
	/**
	 * Get the extension of a certain file.
	 *
	 * @param string $filename the filename that includes the path.
	 * @access public
	 * @return string $extension file extension string.
	 */
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
				// 상수를 사용하면 에러? 확인 못 함.
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
	
	// }}}
?>
