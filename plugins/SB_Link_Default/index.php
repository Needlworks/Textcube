<?php
function _getLinksView($links,$template){
	global $blogURL,$skinSetting;
	ob_start();
	foreach($links as $link){
		$view="$template";
		dress('link_url',htmlspecialchars($link['url']),$view);
		dress('link_site',fireEvent('ViewLink',htmlspecialchars(UTF8::lessenAsEm($link['name'],$skinSetting['linkLength']))),$view);
		print $view;
	}
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
}

function _getLinks($owner){
	global $database;
	$links=array();
	if($result=mysql_query("select * from {$database['prefix']}Links where owner = $owner ORDER BY name")){
		while($link=mysql_fetch_array($result))
			array_push($links,$link);
	}
	return $links;
}

function SB_Link_Default($target) {
	global $owner;

	$target .= '<ul>';
	$target .= _getLinksView(_getLinks($owner),'<li> <a href="[##_link_url_##]" onclick="window.open(this.href); return false"> [##_link_site_##].</a> </li>');
	$target .= '</ul>';

	return $target;
}
?>