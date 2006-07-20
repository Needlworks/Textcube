<?php
function SB_Counter_Default($target) {
	global $stats;

	$counter = 'Total : [##_count_total_##] <br /> Today : [##_count_today_##] Yesterday : [##_count_yesterday_##]';

	dress('count_total',$stats['total'],$counter);
	dress('count_today',$stats['today'],$counter);
	dress('count_yesterday',$stats['yesterday'],$counter);

	return $target.$counter;
}
?>