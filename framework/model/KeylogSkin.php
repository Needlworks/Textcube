<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Model_KeylogSkin {
	var $outter;
	var $skin;
	var $keylog;
	var $keylogItem;

	function __construct($filename) {
		global $service, $serviceURL;
		if (!$sval = file_get_contents($filename))
			Utils_Respond::ErrorPage("KeywordSkin");
		$origPath = $serviceURL . substr($filename,strlen(ROOT));
		$origPath = substr($origPath, 0, 0 - strlen(Path::getBaseName($origPath)));
		$sval = str_replace('./', $origPath, $sval);
		replaceSkinTag($sval, 'html');
		replaceSkinTag($sval, 'head');
		replaceSkinTag($sval, 'body');
		list($sval, $this->keylogItem) = $this->cutSkinTag($sval, 'blog_rep');
		list($sval, $this->keylog) = $this->cutSkinTag($sval, 'blog');
		$this->outter = $sval;
	}
	
	function cutSkinTag($contents, $tag) {
		$tagSize = strlen($tag) + 4;
		$begin = strpos($contents, "<s_$tag>");
		if ($begin === false)
			return array($contents, '');
		$end = strpos($contents, "</s_$tag>", $begin + 5);
		if ($end === false)
			return array($contents, '');
		$inner = substr($contents, $begin + $tagSize, $end - $begin - $tagSize);
		$outter = substr($contents, 0, $begin) . "[##_{$tag}_##]" . substr($contents, $end + $tagSize + 1);
		return array($outter, $inner);
	}
}

function removeAllTags($contents) {
	handleTags($contents);
	$contents = preg_replace('/\[#M_[^|]*\|[^|]*\|/Us', '', str_replace('_M#]', '', preg_replace('/\[##_.+_##\]/Us', '', $contents)));
	$contents = preg_replace('@<(s_[0-9a-zA-Z_]+)>.*?</\1>@s', '', $contents);
	return $contents;	
}

function replaceSkinTag( & $contents, $tag) {
	$pattern[] = '/(<'.$tag.'.*>)\r?\n/Ui';
	$pattern[] = '/<\/'.$tag.'>/Ui';

	$replacement[] = '$1'.CRLF.'[##_SKIN_'.$tag.'_start_##]';
	$replacement[] = '[##_SKIN_'.$tag.'_end_##]$0';

	$contents = preg_replace($pattern, $replacement, $contents);
}

function insertGeneratorVersion(&$contents) {
	$pattern = '/(<head.*>)/Ui';
	$replacement = '$1'.CRLF.'<meta name="generator" content="'.TEXTCUBE_NAME.' '.TEXTCUBE_VERSION.'" />';

	$contents = preg_replace($pattern, $replacement, $contents);
}

function setTempTag($name) {
	return "[#####_#####_#####_{$name}_#####_#####_#####]";
}

function revertTempTags($content) {
	global $contentContainer;
	
	if(is_array($contentContainer)) {
		$keys = array_keys($contentContainer);
		for ($i=0; $i<count($keys); $i++) {
			$content = str_replace("[#####_#####_#####_{$keys[$i]}_#####_#####_#####]", $contentContainer[$keys[$i]], $content);
//			unset($contentContainer[$keys[$i]]);
		}
	}
	return $content;
}

?>
