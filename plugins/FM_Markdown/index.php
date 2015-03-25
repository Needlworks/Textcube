<?php
// Markdown formatter for Textcube 2.0
// By Jeongkyu Shin. (inureyes@gmail.com)

if(!class_exists('MarkdownExtra')) {
	require_once dirname(__FILE__) . '/markdown-lib/MarkdownInterface.php';
	require_once dirname(__FILE__) . '/markdown-lib/Markdown.php';
	require_once dirname(__FILE__) . '/markdown-lib/MarkdownExtra.php';
}

function FM_Markdown_format($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true, $bRssMode = false) {
	$context = Model_Context::getInstance();
	$path = __TEXTCUBE_ATTACH_DIR__."/".$context->getProperty('blog.id');
	$url = $context->getProperty('service.path')."/attach/".$context->getProperty('blog.id');;
	if(!function_exists('FM_TTML_bindAttachments')) { // To reduce the amount of loading code!
		require_once dirname(__FILE__) . '/ttml.php';
	}
	$view = FM_TTML_bindAttachments($id, $path, $url, $content, $useAbsolutePath, $bRssMode);
	$view = FM_TTML_preserve_TTML_type_tags($view);
	$view = \Michelf\MarkdownExtra::defaultTransform($view);
	$view = FM_TTML_restore_TTML_type_tags($view);
	$view = FM_TTML_bindTags($id, $view);
	return $view;
}

function FM_Markdown_summary($blogid, $id, $content, $keywords = array(), $useAbsolutePath = true) {
	$context = Model_Context::getInstance();
	$view = FM_Markdown_format($blogid, $id, $content, $keywords, $useAbsolutePath, true);
    if (!$context->getProperty('blog.publishWholeOnRSS',true)) $view = Utils_Unicode::lessen(removeAllTags(stripHTML($view)), 255);
	return $view;
}

function FM_Markdown_html_to_markdown($content) {
	require_once dirname(__FILE__) . '/markdown-lib/HTML_To_Markdown.php';
	$markdown = new HTML_To_Markdown($content);
	return $markdown->output();
}
?>
