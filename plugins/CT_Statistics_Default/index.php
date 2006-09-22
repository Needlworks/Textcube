<?php
function CT_Statistics_Default($target) {
	global $owner;
	$stats = getStatistics($owner);
	
	$target .= '<ul class="CT_Statistics_Default">';
	$target .= '<li class="TotalCount"><h4>Total Counts</h4><div style="text-align:right; width:100%">';
	$target .= '<span style="font-size:200%">';
	$target .= number_format($stats['total']);
	$target .= '</span>';
	$target .= '</div></li>';
	
	$target .= '<li class="TodayCount"><h4>Today</h4><div style="text-align:right; width:100%">';
	$target .= '<span style="font-size:200%">';
	$target .= number_format($stats['today']);
	$target .= '</span>';
	$target .= '</div></li>';
	
	$target .= '<li class="YesterdayCount"><h4>Yesterday</h4><div style="text-align:right; width:100%">';
	$target .= '<span style="font-size:200%">';
	$target .= number_format($stats['yesterday']);
	$target .= '</span>';
	$target .= '</div></li>';
	$target .= '</ul>';


	return $target;
}
?>
