<?php
// Statistics Graph by Gendoh http://gendoh.com
// if installed at other directory, edit a location of count

function DisplayStatisticsGraph($target) 
{
	$context = Model_Context::getInstance();
	$target = '<div style="overflow:hidden; width:100%; text-align:center" ><img src="' . $context->getProperty('plugin.uri','') .  '/count/count.php?blogid=' . getBlogId() . '" alt="Statistics Graph" title="Blog Visitors" /></div>';
	return $target;
}
?>
