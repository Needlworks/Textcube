<?php
/* Start plugin for Tattertools 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : inureyes, gendoh, graphittie

   Created at       : 2006.8.22
   Last modified at : 2006.10.10

 This plugin adds start panel on 'quilt'.
 For the detail, visit http://forum.tattertools.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function CT_Start_Default($target) {
	global $owner, $blogURL;
	$target .= '<ul>';
	$target .= '<li><a href="'.$blogURL.'/owner/entry/post">'. _t('새 글을 씁니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin">'. _t('스킨을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin/sidebar">'. _t('사이드바 구성을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin/setting">'. _t('블로그에 표시되는 값들을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/entry/category">'. _t('카테고리를 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/plugin">'. _t('플러그인을 켜거나 끕니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/reader">'. _t('RSS 리더를 봅니다').'</a></li>'.CRLF;
	$target .= '</ul>';
	return $target;
}
?>
