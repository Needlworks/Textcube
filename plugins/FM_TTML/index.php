<?php
// TTML Formatter for Textcube 1.10.3
// (C) 2004-2016 Needlworks / Tatter Network Foundation

if(!function_exists('FM_TTML_bindAttachments')) require_once 'ttml.php';

function FM_TTML_format($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true, $bRssMode = false) {
	$context = Model_Context::getInstance();
	$path = __TEXTCUBE_ATTACH_DIR__."/$blogid";
	$url = $context->getProperty("service.path")."/attach/$blogid";
	$view = FM_TTML_bindAttachments($id, $path, $url, $content, $useAbsolutePath, $bRssMode);
//	if (is_array($keywords)) $view = FM_TTML_bindKeywords($keywords, $view);
	$view = FM_TTML_bindTags($id, $view);
	return $view;
}

function FM_TTML_summary($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true) {
	$context = Model_Context::getInstance();
	$view = FM_TTML_format($blogid, $id, $content, $keywords, $useAbsolutePath, true);
	if (!$context->getProperty("blog.publishWholeOnRSS")) $view = Utils_Unicode::lessen(removeAllTags(stripHTML($view)), 255);
	return $view;
}
?>
