<?php
function CT_Statistics_Default($target) {
	global $owner;
	$stats = getStatistics($owner);
	$target .= '<dl>';
	$target .= '<dt class="TotalCount">Total Counts : </dt>';
	$target .= '<dd class="TotalCount">';
	$target .= number_format($stats['total']);
	$target .= '</dd>';
	$target .= '<dt class="TodayCount">Today : </dt>';
	$target .= '<dd class="TodayCount">';
	$target .= number_format($stats['today']);
	$target .= '</dd>';
	$target .= '<dt class="YesterdayCount">Yesterday : </dt>';
	$target .= '<dd class="YesterdayCount">';
	$target .= number_format($stats['yesterday']);
	$target .= '</dd>';
	$target .= '</dl>';
	return $target;
}
?>
