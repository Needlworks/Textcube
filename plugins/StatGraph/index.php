<?
// Statistics Graph by Gendoh http://process.kaist.ac.kr/~gendoh
// if installed at other directory, edit a location of count
//global $pluginURL;

function DisplayStatisticsGraph($target) 
{ 
	global $pluginURL, $owner; 
	$target = '<div><img src="' . $pluginURL .  '/count/count.php?owner=' . $owner . '"></div>';
	return $target;
}
?>
