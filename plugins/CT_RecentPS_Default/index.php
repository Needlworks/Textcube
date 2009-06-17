<?php
/* Recent Entries plugin for Textcube 1.7
   ----------------------------------
   Version 1.7
   Tatter Network Foundation development team / Needlworks.

   Creator          : Peris
   Maintainer       : Peris, inureyes, graphittie

   Created at       : 2006.7.25
   Last modified at : 2008.4.18
 
 This plugin shows recent entries on 'quilt'.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/

function _getRecentEntries($blogid){
	global $database,$skinSetting;
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	$result=POD::queryAll("SELECT id, title, comments FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 $visibility AND category >= 0 ORDER BY published DESC LIMIT {$skinSetting['entriesonrecent']}");
	if(!empty($result)) return $result;
	else return array();
}

function _getRecentEntriesView($entries,$template){
	requireComponent("Eolin.PHP.Core");
	requireComponent("Textcube.Function.misc");
	global $blogURL,$skinSetting;
	ob_start();
	foreach($entries as $entry){
		$view = $template;
		Misc::dress('rctps_rep_link',"$blogURL/{$entry['id']}",$view);
		Misc::dress('rctps_rep_edit_link',"$blogURL/owner/entry/edit/{$entry['id']}",$view);
		Misc::dress('rctps_rep_title',htmlspecialchars(UTF8::lessenAsEm($entry['title'],30)),$view);
		Misc::dress('rctps_rep_rp_cnt',"<span id=\"commentCountOnRecentEntries{$entry['id']}\">".($entry['comments']>0?"({$entry['comments']})":'').'</span>',$view);
		print $view;
	}
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
}

function CT_RecentPS_Default($target) {
	global $blogid,$pluginURL;

	$target .= '<ol>'.CRLF;
	$target .= _getRecentEntriesView(_getRecentEntries($blogid),'											<li><a class="edit-link" href="[##_rctps_rep_edit_link_##]" style="background-image: url(\'' . $pluginURL . '/images/edit.gif\'); background-position: left center; background-repeat: no-repeat; display: block; float: left; height: 12px; margin: 2px 5px 0 0; width: 12px;" title="' . _t('이 포스트를 편집합니다.') . '"><span class="text" style="display: none;">[편집하기]</span></a> <a href="[##_rctps_rep_link_##]" title="' . _t('포스트를 보여줍니다.') . '">[##_rctps_rep_title_##]</a> <span class="cnt">[##_rctps_rep_rp_cnt_##]</span></li>'.CRLF);
	$target .= '										</ol>'.CRLF;

	return $target;
}
?>
