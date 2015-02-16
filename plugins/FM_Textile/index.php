<?php
// Textile formatter for Textcube 1.10.3
// Library by Threshold state.
// Driver by Jeongkyu Shin. (inureyes@gmail.com)
// 2008.1.21
// Last updated : 2015. 2. 16

if(!class_exists('Textile')) require_once 'classTextile.php';

function FM_Textile_format($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true, $bRssMode = false) {
	$context = Model_Context::getInstance();
	$textile = new Textile();
	$path = __TEXTCUBE_ATTACH_DIR__."/$blogid";
	$url = $context->getProperty("service.path")."/attach/$blogid";
	if(!function_exists('FM_TTML_bindAttachments')) { // To reduce the amount of loading code!
		require_once 'ttml.php';
	}
	$view = FM_TTML_bindAttachments($id, $path, $url, $content, $useAbsolutePath, $bRssMode);
	$view = FM_TTML_preserve_TTML_type_tags($view);
	$view = $textile->TextileThis($view);
	$view = FM_TTML_restore_TTML_type_tags($view);
	$view = FM_TTML_bindTags($id, $view);
	return $view;
}

function FM_Textile_summary($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true) {
	$context = Model_Context::getInstance();

	$view = FM_Textile_format($blogid, $id, $content, $keywords, $useAbsolutePath, true);
	if (!$context->getProperty("blog.publishWholeOnRSS")) $view = Utils_Unicode::lessen(removeAllTags(stripHTML($view)), 255);
	return $view;
}
?>
