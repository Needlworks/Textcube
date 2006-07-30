<?php
function _checkPeriod($period){
	if(is_numeric($period)){
		$year=0;
		$month=1;
		$day=1;
		switch(strlen($period)){
			case 8:
				$day=substr($period,6,2);
			case 6:
				$month=substr($period,4,2);
			case 4:
				$year=substr($period,0,4);
				return checkdate($month,$day,$year);
		}
	}
	return false;
}

function _addPeriod($period,$inc=1){
	if(_checkPeriod($period)!==false){
		switch(strlen($period)){
			case 4:
				return strftime('%Y',mktime(0,0,0,1,1,$period+$inc));
			case 6:
				return strftime('%Y%m',mktime(0,0,0,substr($period,4)+$inc,1,substr($period,0,4)));
			case 8:
				return strftime('%Y%m%d',mktime(0,0,0,substr($period,4,2),substr($period,6,2)+$inc,substr($period,0,4)));
		}
	}
	return false;
}

function _getTimeFromPeriod($period){
	if(is_numeric($period)){
		$year=0;
		$month=1;
		$day=1;
		switch(strlen($period)){
			case 8:
				$day=substr($period,6,2);
			case 6:
				$month=substr($period,4,2);
			case 4:
				$year=substr($period,0,4);
				if(checkdate($month,$day,$year))
					return mktime(0,0,0,$month,$day,$year);
		}
	}
	return false;
}

function _getCalendarView($calendar){
	global $blogURL;
	$current=$calendar['year'].$calendar['month'];
	$previous=_addPeriod($current,-1);
	$next=_addPeriod($current,1);
	$firstWeekday=date('w',mktime(0,0,0,$calendar['month'],1,$calendar['year']));
	$lastDay=date('t',mktime(0,0,0,$calendar['month'],1,$calendar['year']));
	$today=($current==Timestamp::get('Ym')?Timestamp::get('j'):null);
	ob_start();?>
<table cellpadding="0" cellspacing="1" style="width: 100%; table-layout: fixed">
<caption class="cal_month">
<a href="<?php echo $blogURL?>/archive/<?php echo $previous?>">&lt;&lt;</a>
&nbsp;
<a href="<?php echo $blogURL?>/archive/<?php echo $current?>"><?php echo Timestamp::format('%Y/%m',_getTimeFromPeriod($current))?></a>
&nbsp;
<a href="<?php echo $blogURL?>/archive/<?php echo $next?>">&gt;&gt;</a>
</caption>
<thead>
  <tr>
    <th class="cal_week2">S</th>
    <th class="cal_week1">M</th>
    <th class="cal_week1">T</th>
    <th class="cal_week1">W</th>
    <th class="cal_week1">T</th>
    <th class="cal_week1">F</th>
    <th class="cal_week1">S</th>
  </tr>
</thead>
<tbody>
  <tr>
<?php
	for($weekday=0;$weekday<$firstWeekday;$weekday++)
		echo '    <td class="cal_day1"></td>',CRLF;
	for($day=1;$weekday<7;$weekday++,$day++){
		if(isset($calendar['days'][$day]))
			echo '    <td class="',($day==$today?'cal_day4':'cal_day3'),"\"><a class=\"cal_click\" href=\"$blogURL/archive/$current",($day>9?$day:'0'.$day),"\">$day</a></td>",CRLF;
		else
			echo '    <td class="',($day==$today?'cal_day4':'cal_day3'),"\">$day</td>",CRLF;
	}
	echo '  </tr>',CRLF;
	while(true){
		echo '  <tr>',CRLF;
		for($weekday=0;($weekday<7)&&($day<=$lastDay);$weekday++,$day++)
			if(isset($calendar['days'][$day]))
				echo '    <td class="',($day==$today?'cal_day4':'cal_day3'),"\"><a class=\"cal_click\" href=\"$blogURL/archive/$current",($day>9?$day:'0'.$day),"\">$day</a></td>",CRLF;
			else
				echo '    <td class="',($day==$today?'cal_day4':'cal_day3'),"\">$day</td>",CRLF;
		if($day>$lastDay){
			for(;$weekday<7;$weekday++)
				echo '    <td class="cal_day2"></td>',CRLF;
			echo '  </tr>',CRLF;
			break;
		}
		echo '  </tr>',CRLF;
	}?>
</tbody>
</table>
<?php
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
} //end getCalendarView

function _getCalendar($owner,$period){
	global $database;
	$calendar=array('days'=>array());
	if(($period===true)||!_checkPeriod($period))
		$period=Timestamp::getYearMonth();
	$calendar['period']=$period;
	$calendar['year']=substr($period,0,4);
	$calendar['month']=substr($period,4,2);
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	$result=mysql_query("SELECT DISTINCT DAYOFMONTH(FROM_UNIXTIME(published)) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0 AND YEAR(FROM_UNIXTIME(published)) = {$calendar['year']} AND MONTH(FROM_UNIXTIME(published)) = {$calendar['month']}");
	if($result){
		while(list($day)=mysql_fetch_array($result))
			array_push($calendar['days'],$day);
	}
	$calendar['days']=array_flip($calendar['days']);
	return $calendar;
}

function SB_Calendar_Default($target) {
	global $owner, $period;

	$target .= _getCalendarView(_getCalendar($owner,isset($period)?$period:true));

	return $target;
}
?>