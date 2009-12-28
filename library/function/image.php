<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// img의 width/height에 맞춰 이미지를 리샘플링하는 함수. 썸네일 함수가 아님! 주의.
function resampleImage($imgString, $originSrc, $useAbsolutePath) {
	global $database, $serviceURL, $pathURL, $defaultURL;
	
	if (!extension_loaded('gd') || !file_exists($originSrc)) {
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

	$originFileName = basename($originSrc);

	// 여기로 넘어오는 값은 이미 getAttachmentBinder() 함수에서 고정값으로 변환된 값이므로 % 값은 고려할 필요 없음.
	if (preg_match('/width="([1-9][0-9]*)"/i', $imgString, $temp)) {
		$tempWidth = $temp[1];
	} else {
		$tempWidth = '';
	}

	if (preg_match('/height="([1-9][0-9]*)"/i', $imgString, $temp)) {
		$tempHeight = $temp[1];
	} else {
		$tempHeight = '';
	}

	$newTempFileName = preg_replace("/\\.([[:alnum:]]+)$/i", ".w{$tempWidth}-h{$tempHeight}.\\1", $originFileName);
	$tempSrc = ROOT."/cache/thumbnail/".getBlogId()."/".$newTempFileName;
	
	//$tempURL = "{$pathURL}/thumbnail/".getBlogId()."/".$newTempFileName;
//	if ($useAbsolutePath == true) {
	// From Textcube 1.6, thumbnail's URLs are also treated as absolute Path.
	$tempURL = "{$serviceURL}/thumbnail/".getBlogId()."/".$newTempFileName;
//	}

	if (file_exists($tempSrc)) {
		$imgString = preg_replace('/src="([^"]+)"/i', 'src="'.$tempURL.'"', $imgString);
		$imgString = preg_replace('/width="([^"]+)"/i', 'width="'.$tempWidth.'"', $imgString);
		$imgString = preg_replace('/height="([^"]+)"/i', 'height="'.$tempHeight.'"', $imgString);
		$imgString = preg_replace('/onclick="open_img\(\'([^\']+)\'\)"/', "onclick=\"open_img('$serviceURL/attach/".getBlogId()."/".$originFileName."')\"", $imgString);
	} else {
		$AttachedImage = new Image();
		$AttachedImage->imageFile = $originSrc;

		// 리샘플링 시작.
		if ($AttachedImage->resample($tempWidth, $tempHeight)) {
			// 리샘플링된 파일 저장.
			$AttachedImage->createThumbnailIntoFile($tempSrc);
			$imgString = preg_replace('/src="([^"]+)"/i', 'src="'.$tempURL.'"', $imgString);
			$imgString = preg_replace('/width="([^"]+)"/i', 'width="'.$tempWidth.'"', $imgString);
			$imgString = preg_replace('/height="([^"]+)"/i', 'height="'.$tempHeight.'"', $imgString);
			$imgString = preg_replace('/onclick="open_img\(\'([^\']+)\'\)"/', "onclick=\"open_img('$serviceURL/attach/".getBlogId()."/".$originFileName."')\"", $imgString);
		}

		unset($AttachedImage);
	}

	return $imgString;
}
?>
