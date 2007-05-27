<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Image {
	function Image() {
		$this->extraPadding = 0;
		$this->imageFile = 
		$this->resultImageDevice = 
			NULL;
	}
	
	function resample($width, $height) {
		if (empty($width) && empty($height))
			return false;
		if (empty($this->imageFile) || !file_exists($this->imageFile))
			return false;
		
		// create an image device as image format.
		switch ($this->getImageType($this->imageFile)) {
			case "gif":
				if (imagetypes() & IMG_GIF) {
					$originImageDevice = imagecreatefromgif($this->imageFile);
				} else {
					return false;
				}
				break;
			case "jpg":
				if (imagetypes() & IMG_JPG) {
					$originImageDevice = imagecreatefromjpeg($this->imageFile);
				} else {
					return false;
				}
				break;
			case "png":
				if (imagetypes() & IMG_PNG) {
					$originImageDevice = imagecreatefrompng($this->imageFile);
				} else {
					return false;
				}
				break;
			case "wbmp":
				if (imagetypes() & IMG_WBMP) {
					$originImageDevice = imagecreatefromwbmp($this->imageFile);
				} else {
					return false;
				}
				break;
			case "xpm":
				if (imagetypes() & IMG_XPM) {
					$originImageDevice = imagecreatefromxpm($this->imageFile);
				} else {
					return false;
				}
				break;
			default:
				return false;
				break;
		}
		// 리샘플링은 최종단계에서 리샘플링만을 하는 기능임. 시스템을 예로 들면 OS의 기능에 해당함.
		// 이미지 프로세스는 어플리케이션의 기능으로 볼 수 있고, 따라서 이미지 리샘플링 중에는 이벤트가 끼어들면 안 됨.
		//$originImageDevice = fireEvent('BeforeResizeImage', $originImageDevice, $this);
		
		if (Path::getExtension($this->imageFile) == ".gif") {
			$this->resultImageDevice = imagecreate($width, $height);
		} else {
			$this->resultImageDevice = imagecreatetruecolor($width, $height);
		}
		
		$bgColorBy16 = $this->hexRGB("FFFFFF");
		$temp = imagecolorallocate($this->resultImageDevice, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
		imagefilledrectangle($this->resultImageDevice, 0, 0, $width, $height, $temp);
		imagecopyresampled($this->resultImageDevice, $originImageDevice, 0, 0, 0, 0, $width, $height, imagesx($originImageDevice), imagesy($originImageDevice));
		imagedestroy($originImageDevice);
		//$this->resultImageDevice = fireEvent('AfterResizeImage', $this->resultImageDevice, $this);
		
		return true;
	}
	
	function impressWaterMark($waterMarkFile, $position="left=10|bottom=10", $gamma=100) {
		if ($this->getImageType($waterMarkFile) == "png")
			return $this->_impressWaterMarkCore("PNG", $waterMarkFile, $position);
		else
			return $this->_impressWaterMarkCore("GIF", $waterMarkFile, $position, $gamma);
	}
	
	function _impressWaterMarkCore($type, $waterMarkFile, $position, $gamma=100) {
		if (empty($waterMarkFile))
			return false;
		if (!file_exists($waterMarkFile))
			return false;
		if (empty($this->resultImageDevice))
			return false;
		
		// validate gamma.
		if (!is_int($gamma)) {
			$gamma = 100;
		} else if ($gamma < 0) {
			$gamma = 0;
		} else if ($gamma > 100) {
			$gamma = 100;
		}
		
		list($waterMarkWidth, $waterMarkHeight, $waterMakrType) = getimagesize($waterMarkFile);
		
		// position of watermark.
		if (eregi("^(\-?[0-9A-Z]+) (\-?[0-9A-Z]+)$", $position, $temp)) {
			$resultWidth = imagesx($this->resultImageDevice);
			$resultHeight = imagesy($this->resultImageDevice);
			
			switch ($temp[1]) {
				case "left":
					$xPosition = $this->extraPadding;
					break;
				case "center":
					$xPosition = $resultWidth / 2 - $waterMarkWidth / 2;
					break;
				case "right":
					$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
					break;
				default:
					// if positive, calculate x value from left.
					if (eregi("^([1-9][0-9]*)$", $temp[1], $extra)) {
						if ($extra[1] > $resultWidth - $waterMarkWidth) {
							$xPosition = $resultWidth - $waterMarkWidth;
						} else {
							$xPosition = $extra[1];
						}
					// if negative, calculate x value from right.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[1], $extra)) {
						if ($resultWidth - $waterMarkWidth - abs($extra[1]) < 0) {
							$xPosition = 0;
						} else {
							$xPosition = $resultWidth - $waterMarkWidth - abs($extra[1]);
						}
					// in the case of 0.
					} else if ($temp[1] == "0") {
						$xPosition = 0;
					// the others. calculate x value from left.
					} else {
						$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
					}
			}
			
			switch ($temp[2]) {
				case "top":
					$yPosition = $this->extraPadding;
					break;
				case "middle":
					$yPosition = $resultHeight / 2 - $waterMarkHeight / 2;
					break;
				case "bottom":
					$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
					break;
				default:
					// if positive, calculate y value from top.
					if (eregi("^([1-9][0-9]*)$", $temp[2], $extra)) {
						if ($extra[1] > $resultHeight - $waterMarkHeight) {
							$yPosition = $resultHeight - $waterMarkHeight;
						} else {
							$yPosition = $extra[1];
						}
					// if negative, calculate y value from bottom.
					} else if (eregi("^(\-?[1-9][0-9]*)$", $temp[2], $extra)) {
						if ($resultHeight - $waterMarkHeight - abs($extra[1]) < 0) {
							$yPosition = 0;
						} else {
							$yPosition = $resultHeight - $waterMarkHeight - abs($extra[1]);
						}
					// in the case of 0.
					} else if ($temp[1] == "0") {
						$yPosition = 0;
					// the others. calculate y value from bottom.
					} else {
						$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
					}
			}
		} else {
			$xPosition = $resultWidth - $waterMarkWidth - $this->extraPadding;
			$yPosition = $resultHeight - $waterMarkHeight - $this->extraPadding;
		}
		
		// create watermark image device.
		switch ($waterMakrType) {
			case 1:
				$waterMarkDevice = imagecreatefromgif($waterMarkFile);
				break;
			case 2:
				$waterMarkDevice = imagecreatefromjpeg($waterMarkFile);
				break;
			case 3:
				$waterMarkDevice = imagecreatefrompng($waterMarkFile);
				break;
		}
		
		// PHP >= 4.0.6
		if (strtolower($type) == "png" && function_exists("imagealphablending")) {
			imagealphablending($this->resultImageDevice, true);
			imagecopy($this->resultImageDevice, $waterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight);
		} else {
			// if not support alpha channel, support GIF transparency.
			$tempWaterMarkDevice = imagecreatetruecolor($waterMarkWidth, $waterMarkHeight);
			
			$bgColorBy16 = $this->hexRGB("FF00FF");
			$temp = imagecolorallocate($tempWaterMarkDevice, $bgColorBy16['R'], $bgColorBy16['G'], $bgColorBy16['B']);
			imagecolortransparent($this->resultImageDevice, $temp);
			imagefill($tempWaterMarkDevice, 0, 0, $temp);
			imagecopy($tempWaterMarkDevice, $waterMarkDevice, 0, 0, 0, 0, $waterMarkWidth, $waterMarkHeight);
			
			if (function_exists("imagecopymerge"))
				imagecopymerge($this->resultImageDevice, $tempWaterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight, $gamma);
			else
				imagecopy($this->resultImageDevice, $tempWaterMarkDevice, $xPosition, $yPosition, 0, 0, $waterMarkWidth, $waterMarkHeight);
			
			imagedestroy($tempWaterMarkDevice);
		}
		
		imagedestroy($waterMarkDevice);
		return true;
	}
	
	function createThumbnailIntoFile($fileName) {
		if (empty($this->resultImageDevice))
			return false;
		
		imageinterlace($this->resultImageDevice);
		switch ($this->getImageType($this->imageFile)) {
			case "gif":
				imagegif($this->resultImageDevice, $fileName);
				break;
			case "png":
				imagepng($this->resultImageDevice, $fileName);
				break;
			case "wbmp":
				imagewbmp($this->resultImageDevice, $fileName);
				break;
			case "jpg":
			default:
				imagejpeg($this->resultImageDevice, $fileName, 80);
				break;
		}
		
		$this->resultImageDevice = NULL;
		
		return true;
	}
	
	function createThumbnailIntoCache() {
		header("Content-type: image/jpeg");
		imagejpeg($this->resultImageDevice);
		
		$originImageDevice = NULL;
		$this->resultImageDevice = NULL;
		
		return true;
	}
	
	function calcOptimizedImageSize($argWidth, $argHeight, $boxWidth=NULL, $boxHeight=NULL, $resizeFlag="reduce") {
		if (empty($boxWidth) && empty($boxHeight)) {
			return array($argWidth, $argHeight);
		} else if (!empty($boxWidth) && empty($boxHeight)) {
			if ($argWidth > $boxWidth) {
				$newWidth = $boxWidth;
				$newHeight = floor($argHeight * $newWidth / $argWidth);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
		} else if (empty($boxWidth) && !empty($boxHeight)) {
			if ($argHeight > $boxHeight) {
				$newHeight = $boxHeight;
				$newWidth = floor($argWidth * $newHeight / $argHeight);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
		} else {
			if ($argWidth > $boxWidth) {
				$newWidth = $boxWidth;
				$newHeight = floor($argHeight * $newWidth / $argWidth);
			} else {
				$newWidth = $argWidth;
				$newHeight = $argHeight;
			}
			
			if ($newHeight > $boxHeight) {
				$tempHeight = $newHeight;
				$newHeight = $boxHeight;
				$newWidth = floor($newWidth * $newHeight / $tempHeight);
			}
		}
		
		if ($argWidth * $argHeight > $newWidth * $newHeight) {
			if ($resizeFlag == "reduce" || $resizeFlag == "both") {
				$imgWidth = $newWidth;
				$imgHeight = $newHeight;
			} else {
				$imgWidth = $argWidth;
				$imgHeight = $argHeight;
			}
		} else if ($argWidth * $argHeight == $newWidth * $newHeight) {
			$imgWidth = $argWidth;
			$imgHeight = $argHeight;
		} else if ($argWidth * $argHeight < $newWidth * $newHeight) {
			if ($resizeFlag == "enlarge" || $resizeFlag == "both") {
				$imgWidth = $newWidth;
				$imgHeight = $newHeight;
			} else {
				$imgWidth = $argWidth;
				$imgHeight = $argHeight;
			}
		}
		
		return array($imgWidth, $imgHeight);
	}
	
	function getImageType($filename) {
		if (file_exists($filename)) {
			if (function_exists("exif_imagetype")) {
				$imageType = exif_imagetype($filename);
			} else {
				$tempInfo = getimagesize($filename);
				$imageType = $tempInfo[2];
			}
			
			switch ($imageType) {
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
	
	/* "FFFFFF" => array(255, 255, 255) */
	function hexRGB($hexstr) {
		$int = hexdec($hexstr);
		return array('R' => 0xFF & ($int >> 0x10), 'G' => 0xFF & ($int >> 0x8), 'B' => 0xFF & $int);
	}
}
?>