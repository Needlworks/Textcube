<?php
/* Statistics plugin for Textcube 1.8
   ----------------------------------
   Version 1.6
   Tatter Network Foundation development team / Needlworks.

   Creator          : inureyes
   Maintainer       : inureyes, gendoh

   Created at       : 2006.8.15
   Last modified at : 2010.8.24

 This plugin adds simple statistics information panel on 'quilt'.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function CT_Statistics_Default($target) {
	$blogid = getBlogId();
	$stats = Statistics::getStatistics($blogid);
	
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
