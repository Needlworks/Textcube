<?php
function CT_Start_Default($target) {
	global $owner, $blogURL;
	$target .= '<ul>';
	$target .= '<li><a href="'.$blogURL.'/owner/entry/post">'. _t('새 글을 씁니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin">'. _t('스킨을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin/setting">'. _t('블로그에 표시되는 값들을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/entry/category">'. _t('카테고리를 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/plugin">'. _t('플러그인을 켜거나 끕니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/reader">'. _t('RSS 리더를 봅니다').'</a></li>'.CRLF;
	$target .= '</ul>';
	return $target;
}
?>
