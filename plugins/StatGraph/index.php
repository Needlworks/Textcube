<?php
// Statistics Graph by Gendoh http://gendoh.tistory.com
// if installed at other directory, edit a location of count
// global $pluginURL;

function DisplayStatisticsGraph($target) 
{ 
	global $pluginURL, $owner; 
	$target = '<div><img src="' . $pluginURL .  '/count/count.php?owner=' . $owner . '" alt="Statistics Graph" title="Blog Visitors"></div>';
	return $target;
}
?>
