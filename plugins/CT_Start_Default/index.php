<?php
/* Start plugin for Textcube 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : inureyes, gendoh, graphittie

   Created at       : 2006.8.22
   Last modified at : 2006.10.30

 This plugin adds start panel on 'quilt'.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function CT_Start_Default($target) {
	requireComponent("Eolin.PHP.Core");
	requireComponent( "Textcube.Function.misc");
	global $owner, $blogURL, $database;
	$target .= '<ul>';
	$target .= '<li><a href="'.$blogURL.'/owner/entry/post">'. _t('새 글을 씁니다').'</a></li>'.CRLF;

	$latestEntryId = misc::getUserSettingGlobal('LatestEditedEntry',0);
	if($latestEntryId !== 0){
		$latestEntry = CT_Start_Default_getEntry($owner,$latestEntryId);
		if($latestEntry!=false){
			$target .= '<li><a href="'.$blogURL.'/owner/entry/edit/'.$latestEntry['id'].'">'. _f('최근글(%1) 수정', htmlspecialchars(UTF8::lessenAsEm($latestEntry['title'],10))).'</a></li>';
		}
	}

	$target .= '<li><a href="'.$blogURL.'/owner/skin">'. _t('스킨을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin/sidebar">'. _t('사이드바 구성을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/skin/setting">'. _t('블로그에 표시되는 값들을 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/entry/category">'. _t('카테고리를 변경합니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/plugin">'. _t('플러그인을 켜거나 끕니다').'</a></li>'.CRLF;
	$target .= '<li><a href="'.$blogURL.'/owner/reader">'. _t('RSS 리더를 봅니다').'</a></li>'.CRLF;
	$target .= '</ul>';
	return $target;
}

function CT_Start_Default_getEntry($owner, $id, $draft = false) {
	global $database;
	if ($id == 0) {
		if ($draft) {
			if (!$id = getDraftEntryId())
				return;
		} else {
			if (!doesHaveOwnership())
				return;
			deleteAttachments($owner, 0);
			return array('id' => 0, 'draft' => 0, 'visibility' => 0, 'category' => 0, 'location' => '', 'title' => '', 'content' => '', 'acceptComment' => 1, 'acceptTrackback' => 1, 'published' => time(), 'slogan' => '');
		}
	}
	if ($draft) {
		$entry = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 1");
		if (!$entry)
			return;
		if ($entry['published'] == 1)
			$entry['republish'] = true;
		else if ($entry['published'] != 0)
			$entry['appointed'] = $entry['published'];
		if ($id != 0)
			$entry['published'] = DBQuery::queryCell("SELECT published FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0");
		return $entry;
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND visibility > 0';
		$entry = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0 $visibility");
		if (!$entry)
			return;
		if ($entry['visibility'] < 0)
			$entry['appointed'] = $entry['published'];
		return $entry;
	}
}
?>
