<?php
// TTML Formatter for Textcube 1.6
// (C) 2004-2009 Needlworks / Tatter Network Foundation

if(!function_exists('FM_TTML_bindAttachments')) require_once 'ttml.php';

function FM_TTML_format($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true, $bRssMode = false) {
	global $service;
	$path = ROOT . "/attach/$blogid";
	$url = "{$service['path']}/attach/$blogid";
	$view = FM_TTML_bindAttachments($id, $path, $url, $content, $useAbsolutePath, $bRssMode);
//	if (is_array($keywords)) $view = FM_TTML_bindKeywords($keywords, $view);
	$view = FM_TTML_bindTags($id, $view);
	return $view;
}

function FM_TTML_summary($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true) {
	global $blog;
	$view = FM_TTML_format($blogid, $id, $content, $keywords, $useAbsolutePath, true);
	if (!$blog['publishWholeOnRSS']) $view = UTF8::lessen(removeAllTags(stripHTML($view)), 255);
	return $view;
}

?>
