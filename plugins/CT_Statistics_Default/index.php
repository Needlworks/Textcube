<?php
function CT_Statistics_Default($target) {
	global $owner;
	$stats = getStatistics($owner);
	$target .= '<dl>';
	$target .= '<dt class="TotalCount">Total Counts : </dt>';
	$target .= '<dd class="TotalCount">';
	$target .= number_format($stats['total']);
	$target .= '</dd>';
	$target .= '</dl>';
	return $target;
}
?>