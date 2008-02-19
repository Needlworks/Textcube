<?php
// Statistics Graph by Gendoh http://gendoh.com
// if installed at other directory, edit a location of count
// global $pluginURL;

function DisplayStatisticsGraph($target) 
{ 
	global $pluginURL, $owner; 
	$target = '<div style="overflow:hidden; width:100%; text-align:center" ><img src="' . $pluginURL .  '/count/count.php?blogid=' . getBlogId() . '" alt="Statistics Graph" title="Blog Visitors" /></div>';
	return $target;
}
?>
