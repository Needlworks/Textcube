<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'f' => array('filename'),
		'm' => array('string', 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
$imagePath = __TEXTCUBE_ATTACH_DIR__."/$blogid/{$_GET['f']}";
if ($fp = @fopen($imagePath, 'r')) {
	if(isset($_GET['m']) && !empty($_GET['m'])){
		if (file_exists($imagePath)) {
			$imageInfo = getimagesize($imagePath);
			$cropSize = $_GET['m'];
			$objThumbnail = new Utils_Image();
			if ($imageInfo[0] > $imageInfo[1])
				list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $cropSize);
			else
				list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $cropSize, null);
			
			$objThumbnail->imageFile = $imagePath;
			if ($objThumbnail->resample($tempWidth, $tempHeight) && $objThumbnail->cropRectBySize($cropSize, $cropSize)) {
				$objThumbnail->saveAsCache();
			}
			unset($objThumbnail);
		}
	}else{
		$imageInfo = @getimagesize($imagePath);
		if (function_exists('gd_info')) {
			switch ($imageInfo[2]) {
				case 1:
					$image = imagecreatefromgif($imagePath);
					break;
				case 2:
					$image = imagecreatefromjpeg($imagePath);
					break;
				case 3:
					$image = imagecreatefrompng($imagePath);
					break;
				case 6:
					$image = imagecreatefromwbmp($imagePath);
					break;
				default:
					Respond::NotFoundPage();
			}
			$canvasWidth = 640;
			$canvasHeight = round($imageInfo[1] * ($canvasWidth / $imageInfo[0]));
			if ($imageInfo[0] > $canvasWidth) {
				$canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
				imagealphablending($canvas, 0);
				imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, 0x7f000000);
				imagecopyresampled($canvas, $image, 0, 0, 0, 0, $canvasWidth, $canvasHeight, $imageInfo[0], $imageInfo[1]);
			} else
				$canvas = $image;
			header('Content-type: image/jpeg');
			imagejpeg($canvas);
		} else {
			while (!feof($fp)) {
				echo fread($fp, 8192);
				flush();
			}
		}
		fclose($fp);
	}
} else
	Respond::NotFoundPage();
?>
