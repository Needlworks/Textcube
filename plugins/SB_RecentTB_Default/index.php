<?php
// lib/model/trackback.php : 34 line
function _getRecentTrackbacks($owner) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND isFiltered = 0 ORDER BY written DESC LIMIT {$skinSetting['trackbacksOnRecent']}" : "SELECT t.* FROM {$database['prefix']}Trackbacks t, {$database['prefix']}Entries e WHERE t.owner = $owner AND t.owner = e.owner AND t.entry = e.id AND e.draft = 0 AND e.visibility >= 2 AND isFiltered = 0 ORDER BY t.written DESC LIMIT {$skinSetting['trackbacksOnRecent']}";
	if ($result = mysql_query($sql)) {
		while ($trackback = mysql_fetch_array($result))
			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

// lib/view/view.php : 924 line
function _getRecentTrackbacksView($trackbacks, $template) {
	global $blogURL, $skinSetting;
	ob_start();
	foreach ($trackbacks as $trackback) {
		$view = "$template";
		dress('rcttb_rep_link', "$blogURL/{$trackback['entry']}#trackback{$trackback['id']}", $view);
		dress('rcttb_rep_desc', htmlspecialchars(UTF8::lessenAsEm($trackback['subject'], $skinSetting['recentTrackbackLength'])), $view);
		dress('rcttb_rep_time', fireEvent('ViewRecentTrackbackDate', Timestamp::format2($trackback['written'])), $view);
		dress('rcttb_rep_name', htmlspecialchars(UTF8::lessenAsEm($trackback['site'], $skinSetting['recentTrackbackLength'])), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

// lib/piece/blog/end.php : 49 line
function SB_RecentTB_Default($target) {
	global $owner;

	$target .= '<ul id="recentComments">';
	$target .= _getRecentTrackbacksView(_getRecentTrackbacks($owner),'<li> <a href="[##_rcttb_rep_link_##]">[##_rcttb_rep_desc_##]. </a><br />
             <span class="name">[##_rcttb_rep_name_##]</span> <span class="date">[##_rcttb_rep_time_##]</span> </li>');
	$target .= '</ul>';

	return $target;
}
?>