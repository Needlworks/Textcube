<?php
/* Visitor statistics plugin for Tattertools 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : gendoh, inureyes, graphittie

   Created at       : 2006.9.21
   Last modified at : 2006.10.27
 
 This plugin shows visitor statistics on administration menu.
 For the detail, visit http://forum.tattertools.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function AD_Visitor_Default()
{
	global $owner, $pluginMenuURL, $pluginAccessURL, $pluginHandlerURL;
	requireComponent( "Tattertools.Model.Statistics");
	requireComponent('Tattertools.Function.misc');
	$stats = Statistics::getStatistics($owner);
	$date = isset($_GET['date']) ? $_GET['date'] : date('Ym', strtotime("now"));
?>
<!-- This tab space below this line is inserted for the indentation of original admin page -->
						<script type="text/javascript">
							//<![CDATA[
								function setTotalStatistics() {
									if (confirm("訪問者統計を初期化すると統計が 0になります。\nよろしいですか?")) {
										var request = new HTTPRequest("GET", "<?php echo $pluginHandlerURL;?>/AD_Visitor_Default_set&ajaxcall");
										request.onSuccess = function() {
											//document.getElementById("total").innerHTML = 0;
											window.location = '<?php echo $pluginMenuURL;?>';
											return true;
										}
										request.onError = function() {
											alert("保存に失敗しました。");
											return false;
										}
										request.send();
									}
								}
								
								function addCommas(nStr) {
									nStr += '';
									x = nStr.split('.');
									x1 = x[0];
									x2 = x.length > 1 ? '.' + x[1] : '';
									var rgx = /(\d+)(\d{3})/;
									while (rgx.test(x1)) {
										x1 = x1.replace(rgx, '$1' + ',' + '$2');
									}
									return x1 + x2;
								}
								
								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									tempDiv = document.createElement("DIV");
									tempDiv.style.clear = "both";
									document.getElementById("part-statistics-visitor").appendChild(tempDiv);
								}
							//]]>
						</script>
					 		
					 	<form method="post" action="<?php echo $pluginHandlerURL;?>AD_Visitor_Default_set">
					 		<div id="part-statistics-visitor" class="part">
					 			<h2 class="caption"><span class="main-text">訪問者統計を表示します。</span></h2>
					 			
						 		<div id="statistics-counter-inbox" class="data-inbox">
									<div class="title">
										<span class="label"><span class="text">全体統計</span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo number_format($stats['total']);?></span>
									</div>
									<a class="init-button button" href="<?php echo $pluginHandlerURL;?>/AD_Visitor_Default_set" onclick="setTotalStatistics(); return false;"><span class="text">初期化</span></a>
								</div>
							
								<hr class="hidden" />
								
								<table id="statistics-month-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text">月別統計</span></th>
										</tr>
									</thead>
									<tbody>
<?php
$temp = Statistics::getMonthlyStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="window.location.href='<?php echo $pluginMenuURL;?>&amp;date=<?php echo $record['date'];?>'">
											<td class="date"><a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $record['date'];?>"><?php echo Timestamp::formatDate2(misc::getTimeFromPeriod($record['date']));?></a></td>
											<td class="count"><a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $record['date'];?>"><?php echo $record['visits'];?></a></td>
										</tr>
<?php
}
?>
									</tbody>
								</table>
								
								<hr class="hidden" />
								
								<table id="statistics-day-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text">日別統計</span></th>
										</tr>
									</thead>
									<tbody>
<?php
if (isset($date)) {
	$temp = Statistics::getDailyStatistics($date);
	for ($i=0; $i<sizeof($temp); $i++) {
		$record = $temp[$i];
		
		$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className;?> inactive-class">
											<td class="date"><?php echo Timestamp::formatDate(misc::getTimeFromPeriod($record['date']));?></td>
											<td class="count"><?php echo $record['visits'];?></td>
										</tr>
<?php
	}
}
?>
									</tbody>
								</table>
								
							</div>
						</form>
						
<?php 
}

function AD_Visitor_Default_set()
{
	global $owner;
	requireComponent( "Tattertools.Model.Statistics");
	$isAjaxRequest = isset($_REQUEST['ajaxcall']) ? true : false;
	if ($isAjaxRequest) {
		$result = Statistics::setTotalStatistics($owner) ? 0 : -1;
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$result</error>\n</response>");
		exit;
	} else {
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
}
?>