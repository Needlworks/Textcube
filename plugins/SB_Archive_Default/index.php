<?php
function _getPeriodLabel($period){
	$name=strval($period);
	switch(strlen($name)){
		case 4:
			return $name;
		case 6:
			return substr($name,0,4).'/'.substr($name,4);
		case 8:
			return substr($name,0,4).'/'.substr($name,4,2).'/'.substr($name,6).'';
	}
}

function _getArchivesView($archives,$template){
	global $blogURL;
	ob_start();
	foreach($archives as $archive){
		$view="$template";
		dress('archive_rep_link',"$blogURL/archive/{$archive['period']}",$view);
		dress('archive_rep_date',_getPeriodLabel($archive['period']),$view);
		dress('archive_rep_count',$archive['count'],$view);
		print $view;
	}
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
}

function _getArchives($owner){
	global $database;
	$archives=array();
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	$result=mysql_query("SELECT EXTRACT(year_month FROM FROM_UNIXTIME(published)) period, COUNT(*) count FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 GROUP BY period ORDER BY period DESC LIMIT 5");
	if($result){
		while($archive=mysql_fetch_array($result))
			array_push($archives,$archive);
	}
	return $archives;
}

function SB_Archive_Default($target) {
	global $owner;

	$target .= '<ul>';
	$target .= _getArchivesView(_getArchives($owner),'<li> <a href="[##_archive_rep_link_##]">[##_archive_rep_date_##] </a> <span class="cnt">([##_archive_rep_count_##])</span> </li>');
	$target .= '</ul>';

	return $target;
}
?>