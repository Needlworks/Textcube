<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
$IV = array(
	'GET' => array(
		'f' => array('filename')
	)
);
require ROOT . '/library/preprocessor.php';
requireView('mobileView');
$imagePath = ROOT . "/attach/$blogid/{$_GET['f']}";
if ($fp = @fopen($imagePath, 'r')) {
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
		$canvasWidth = 240;
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
} else
	Respond::NotFoundPage();
?>
