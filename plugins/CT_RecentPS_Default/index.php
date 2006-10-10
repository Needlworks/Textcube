<?php
// lib/model/entry.php : 263 line
function _getRecentEntries($owner){
	global $database,$skinSetting;
	$entries=array();
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	$result=mysql_query("SELECT id, title, comments FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 ORDER BY published DESC LIMIT {$skinSetting['entriesOnRecent']}");
	while($entry=mysql_fetch_array($result)){
		array_push($entries,$entry);
	}
	return $entries;
}

// lib/view/view.php : 889 line
function _getRecentEntriesView($entries,$template){
	global $blogURL,$skinSetting;
	ob_start();
	foreach($entries as $entry){
		$view = $template;
		dress('rctps_rep_link',"$blogURL/{$entry['id']}",$view);
		dress('rctps_rep_edit_link',"$blogURL/owner/entry/edit/{$entry['id']}",$view);	
		dress('rctps_rep_title',htmlspecialchars(UTF8::lessenAsEm($entry['title'],$skinSetting['recentEntryLength'])),$view);
		dress('rctps_rep_rp_cnt',"<span id=\"commentCountOnRecentEntries{$entry['id']}\">".($entry['comments']>0?"({$entry['comments']})":'').'</span>',$view);
		print $view;
	}
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
}

// lib/piece/blog/end.php : 47 line
function CT_RecentPS_Default($target) {
	global $owner,$pluginURL;

	$target .= '<ul>';
	$target .= _getRecentEntriesView(_getRecentEntries($owner),'<li><a href="[##_rctps_rep_edit_link_##]"><img src="'.$pluginURL.'/images/edit.png" style="border:none;" /></a><a href="[##_rctps_rep_link_##]"> [##_rctps_rep_title_##]</a> <span class="cnt">[##_rctps_rep_rp_cnt_##]</span> </li>');
	$target .= '</ul>';

	return $target;
}
?>
