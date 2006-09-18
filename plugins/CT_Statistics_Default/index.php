<?php
function CT_Statistics_Default($target) {
	global $owner;
	$stats = getStatistics($owner);
	
	$target .= '<table class="CT_Statistics_Default">';
	$target .= '<tr class="TotalCount"><th>Total Counts</th><td>';
	$target .= number_format($stats['total']);
	$target .= '</td></tr>';
	
	$target .= '<tr class="TodayCount"><th>Today</th><td>';
	$target .= number_format($stats['today']);
	$target .= '</td></tr>';
	
	$target .= '<tr class="YesterdayCount"><th>Yesterday</th><td>';
	$target .= number_format($stats['yesterday']);
	$target .= '</td></tr>';
	$target .= '</table>';


	return $target;
}
?>
