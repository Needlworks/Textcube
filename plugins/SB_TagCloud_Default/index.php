<?php
// lib/model/tag.php : 50 line
function _getTagFrequencyRange(){
	global $database,$owner;
	$max=$min=0;
	$result=mysql_query("select count(entry) cnt from {$database['prefix']}TagRelations where owner = $owner group by tag order by cnt desc limit 1");
	if($result){
		if(list($count)=mysql_fetch_array($result))
			$max=$count;
	}
	$result=mysql_query("select count(entry) cnt from {$database['prefix']}TagRelations where owner = $owner group by tag order by cnt limit 1");
	if($result){
		if(list($count)=mysql_fetch_array($result))
			$min=$count;
	}
	return array($max,$min);
}

// lib/model/tag.php : 67 line
function _getTagFrequency($tag,$max,$min){
	global $database,$owner;
	$count=fetchQueryCell("select count(*) from {$database['prefix']}Tags t, {$database['prefix']}TagRelations r where t.id=r.tag and r.owner = $owner and t.name = '".mysql_escape_string($tag)."'");
	$dist=$max/3;
	if($count==$min)
		return 5;
	elseif($count==$max)
		return 1;
	elseif($count>=$min+($dist*2))
		return 2;
	elseif($count>=$min+$dist)
		return 3;
	else
		return 4;
}

// lib/model/tag.php : 20 line
function _getRandomTags($owner) {
	global $database, $skinSetting;
	$tags = array();
	$aux = ($skinSetting['tagsOnTagbox'] == - 1) ? '' : "limit {$skinSetting['tagsOnTagbox']}";
	if ($skinSetting['tagboxAlign'] == 1)
		$result = mysql_query("select name, count(*) cnt from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner GROUP BY name ORDER BY cnt DESC $aux");
	else if ($skinSetting['tagboxAlign'] == 2)
		$result = mysql_query("select distinct name from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner ORDER BY name $aux");
	else
		$result = mysql_query("select name from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner GROUP BY name ORDER BY RAND() $aux");
	if ($result) {
		while (list($tag) = mysql_fetch_row($result))
			array_push($tags, $tag);
	}
	return $tags;
}

// lib/view/view.php : 950 line
function _getRandomTagsView($tags, $template) {
	global $blogURL;
	ob_start();
	list($maxTagFreq, $minTagFreq) = _getTagFrequencyRange();
	foreach ($tags as $tag) {
		$view = $template;
		dress('tag_link', "$blogURL/tag/" . encodeURL($tag), $view);
		dress('tag_name', htmlspecialchars($tag), $view);
		dress('tag_class', "cloud" . _getTagFrequency($tag, $maxTagFreq, $minTagFreq), $view);
		print $view;
	}
	$view = ob_get_contents();
	ob_end_clean();
	return $view;
}

// lib/piece/blog/end.php : 33 line
function SB_TagCloud_Default($target) {
	global $owner;

	$target .= '<ul><li>';
	$target .= _getRandomTagsView(_getRandomTags($owner),' <a href="[##_tag_link_##]" class="[##_tag_class_##]">[##_tag_name_##]</a>');
	$target .= '</li></ul>';

	return $target;
}
?>