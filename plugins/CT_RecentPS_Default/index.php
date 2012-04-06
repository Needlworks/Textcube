<?php
/* Recent Entries plugin for Textcube 1.8
   ----------------------------------
   Version 1.8
   Tatter Network Foundation development team / Needlworks.

   Creator          : Peris
   Maintainer       : Peris, inureyes, graphittie

   Created at       : 2006.7.25
   Last modified at : 2009.10.10
 
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
	$query = DBModel::getInstance();
	$query->reset('Entries');
	$query->setQualifier('blogid','equals',$blogid);
	$query->setQualifier('draft','equals',0);
	if(doesHaveOwnership()) {
		$query->setQualifier('visibility','bigger',0);
	}
	$query->setQualifier('category','bigger or same',0);
	$query->setLimit(8);
	$query->setOrder('published','desc');
	$result = $query->getAll('id,title,comments');
	if(!empty($result)) return $result;
	else return array();
}

function _getRecentEntriesView($entries,$template){
	global $blogURL,$skinSetting;
	$context = Model_Context::getInstance();
	ob_start();
	foreach($entries as $entry){
		$view = $template;
		Misc::dress('rctps_rep_link',$context->getProperty('uri.blog')."/".$entry['id'],$view);
		Misc::dress('rctps_rep_edit_link',$context->getProperty('uri.blog')."/owner/entry/edit/".$entry['id'],$view);
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
