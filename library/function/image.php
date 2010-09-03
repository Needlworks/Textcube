<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// img의 width/height에 맞춰 이미지를 리샘플링하는 함수. 썸네일 함수가 아님! 주의.
function resampleImage($imgString, $filename, $useAbsolutePath = true) {
	$blogid = getBlogId();
	$context = Model_Context::getInstance();

	if (!extension_loaded('gd') || !file_exists(ROOT . "/attach/{$blogid}/{$filename}")) {
		return $imgString;
	}

	if (!is_dir(ROOT."/cache/thumbnail")) {
		@mkdir(ROOT."/cache/thumbnail");
		@chmod(ROOT."/cache/thumbnail", 0777);
	}

	if (!is_dir(ROOT."/cache/thumbnail/".getBlogId())) {
		@mkdir(ROOT."/cache/thumbnail/".getBlogId());
		@chmod(ROOT."/cache/thumbnail/".getBlogId(), 0777);
	}

	$origImageSrc = ($useAbsolutePath ? $context->getProperty('uri.service') : $context->getProperty('uri.path')) . "/attach/{$blogid}/{$filename}";
	$tempWidth = $tempHeight = '';
	if (preg_match('/width="([1-9][0-9]*)"/i', $imgString, $temp))
		$tempWidth = $temp[1];

	if (preg_match('/height="([1-9][0-9]*)"/i', $imgString, $temp))
		$tempHeight = $temp[1];
	
	if (!empty($tempWidth) && is_numeric($tempWidth) && !empty($tempHeight) && is_numeric($tempHeight))
		$resizeImage = getImageResizer($filename, array('width' => $tempWidth, 'height' => $tempHeight, 'absolute' => $useAbsolutePath));
	else if (!empty($tempWidth) && !is_numeric($tempWidth) && empty($tempHeight))
		$resizeImage = getImageResizer($filename, array('width' => $tempWidth, 'absolute' => $useAbsolutePath));
	else if (empty($tempWidth) && !empty($tempHeight) && is_numeric($tempHeight))
		$resizeImage = getImageResizer($filename, array('height' => $tempHeight, 'absolute' => $useAbsolutePath));
	else 
		return $imgString;

	if ($resizeImage === false) return $imgString;
	
	if (basename($resizeImage[0]) == $filename) return $imgString;

	$resizeImageSrc = $resizeImage[0];
	$resizeImageWidth = $resizeImage[1];
	$resizeImageHeight = $resizeImage[2];

	$imgString = preg_replace('/src="([^"]+)"/i', 'src="'.$resizeImageSrc.'"', $imgString);
	$imgString = preg_replace('/width="([^"]+)"/i', 'width="'.$resizeImageWidth.'"', $imgString);
	$imgString = preg_replace('/height="([^"]+)"/i', 'height="'.$resizeImageHeight.'"', $imgString);
	$imgString = preg_replace('/onclick="open_img\(\'([^\']+)\'\)"/', "onclick=\"open_img('{$origImageSrc}')\"", $imgString);

	return $imgString;
}

// 본문으로 부터 포함된 모든 첨부이미지 파일명 추출
function getAttachmentExtracts($content, $blogid = null){
	if (is_null($blogid)) $blogid = getBlogId();

	$result = $temp = array();
	if (preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG)\|(.[^\[]*)_##\]/mi', $content, $matches)) {
		foreach ($matches[0] as $image) {
			$split = explode("|", $image);
			if (!in_array($split[1], $temp)) $temp[] = $split[1];
		}
	}

	if (preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/mi', $content, $matches)) {
		foreach ($matches[2] as $image)
			if (!in_array(basename($image), $temp)) $temp[] = basename($image);
	}

	foreach($temp as $filename) {
		if (preg_match('/(.+)\.w(\d{1,})\-h(\d{1,})\.(.+)/', $filename, $matches))
			$filename = $matches[1].'.'.$matches[4];

		if (file_exists(ROOT."/attach/{$blogid}/{$filename}") && !in_array($filename, $result))
			$result[] = $filename;
	}

	return $result;
}

function getImageResizer($filename, $options = null, $blogid = null) {
	// version 1.2.5
	// usages :
	// $options = array('size'=>100) // resize & crop to square
	// $options = array('width'=>100) // resize by width 
	// $options = array('width'=>100, 'height'=>50) // resize & crop by width and height
	// $options = array('force'=>true) // refresh image
	// result : $url, $width, $height, $path

	$context = Model_Context::getInstance();

	if (is_null($blogid)) $blogid = getBlogId();
	$force = isset($options['force']) ? $options['force'] : false;
	$absolute = isset($options['absolute']) ? $options['absolute'] : true;

	$originSrc = ROOT."/attach/{$blogid}/{$filename}";
	$originURL = ($absolute ? $context->getProperty('uri.service'):$context->getProperty('uri.path'))."/attach/{$blogid}/{$filename}";

	if (!file_exists($originSrc)) return false;

	$imageInfo = getimagesize($originSrc);
	if ($imageInfo === false || count($imageInfo) < 1) return false;
	$originWidth = $imageInfo[0];
	$originHeight = $imageInfo[1];

	if (!extension_loaded('gd')) return array($originURL, $originWidth, $originHeight, $originSrc);

	if (!is_dir(ROOT."/cache/thumbnail")) {
		@mkdir(ROOT."/cache/thumbnail");
		@chmod(ROOT."/cache/thumbnail", 0777);
	}
	if (!is_dir(ROOT."/cache/thumbnail/" . $blogid)) {
		@mkdir(ROOT."/cache/thumbnail/" .$blogid);
		@chmod(ROOT."/cache/thumbnail/" . $blogid, 0777);
	}

	requireComponent('Textcube.Function.Image');
	$objResize = new Image();
	$objResize->imageFile = $originSrc;
	if (isset($options['size']) && is_numeric($options['size'])) {
		if ($imageInfo[0] > $imageInfo[1])
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $options['size']);
		else
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $options['size'], null);

		$resizeWidth = $resizeHeight = $options['size'];
	} else if (isset($options['width']) && is_numeric($options['width']) && isset($options['height']) && is_numeric($options['height'])) {
		if ($options['width'] / $options['height'] > intval($imageInfo[0]) / intval($imageInfo[1]))
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $options['width'], NULL);
		else
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $options['height']);

		$resizeWidth = $options['width'];
		$resizeHeight = $options['height'];
	} else {
		if (isset($options['width']) && is_numeric($options['width'])) {
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $options['width'], NULL);
		} elseif (isset($options['height']) && is_numeric($options['height'])) {
			list($tempWidth, $tempHeight) = $objResize->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $options['height']);
		} else {
			unset($objResize);
			return array($originURL, $originWidth, $originHeight, $originSrc);
		}

		$resizeWidth = $tempWidth; 
		$resizeHeight = $tempHeight;
	}
	$resizeFilename = preg_replace("/\\.([[:alnum:]]+)$/i", ".w{$resizeWidth}-h{$resizeHeight}.\\1", $filename);
	$resizeSrc = ROOT . "/cache/thumbnail/{$blogid}/{$resizeFilename}";
	$resizeURL = ($absolute ? $context->getProperty('uri.service'):$context->getProperty('uri.path')) . "/cache/thumbnail/{$blogid}/{$resizeFilename}";

	if($force) @unlink($resizeSrc);

	if (file_exists($resizeSrc)) { 
		unset($objResize);
		return array($resizeURL, $resizeWidth, $resizeHeight, $resizeSrc);		
	}
	if ($objResize->resample($tempWidth, $tempHeight)) {
		if (isset($options['width']) && is_numeric($options['width']) && isset($options['height']) && is_numeric($options['height'])) {
			@$objResize->cropRectBySize($options['width'], $options['height']);
		} else if (isset($options['size']) && is_numeric($options['size'])) {
			@$objResize->cropRectBySize($options['size'], $options['size']);
		}
		if ($objResize->saveAsFile($resizeSrc)) {
			@chmod($resizeSrc, 0666);
			unset($objResize);
			return array($resizeURL, $resizeWidth, $resizeHeight, $resizeSrc);	
		}
	}
	unset($objResize);
	return array($originURL, $originWidth, $originHeight, $originSrc);
}
?>
