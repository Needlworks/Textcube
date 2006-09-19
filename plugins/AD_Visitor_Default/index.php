<?php

function AD_Visitor_Default()
{
	global $owner, $pluginMenuURL, $pluginAccessURL, $pluginHandlerURL;
requireComponent( "Tattertools.Model.Statistics");
$stats = Statistics::getStatistics($owner);
$date = isset($_GET['date']) ? $_GET['date'] : '';
?>
<!-- This tab space below this line is inserted for the indentation of original admin page -->
						<script type="text/javascript">
							//<![CDATA[
								function setTotalStatistics() {
									if (confirm("방문자의 수를 초기화하면 방문객의 수가 0이 됩니다.\n정말 초기화하시겠습니까?")) {
										var request = new HTTPRequest("GET", "<?php echo $pluginHandlerURL;?>/AD_Visitor_Default_set");
										request.onSuccess = function() {
											//document.getElementById("total").innerHTML = 0;
											window.location = '<?php echo $pluginMenuURL;?>';
											return true;
										}
										request.onError = function() {
											alert("저장하지 못했습니다.");
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
					 			<h2 class="caption"><span class="main-text">방문자 통계정보를 보여줍니다</span></h2>
					 			
						 		<div id="statistics-counter-inbox" class="data-inbox">
									<div class="title">
										<span class="label"><span class="text">현재까지의 방문자 수</span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo number_format($stats['total']);?></span>
									</div>
									<a class="init-button button" href="<?php echo $pluginHandlerURL;?>/AD_Visitor_Default_set" onclick="setTotalStatistics(); return false;"><span class="text">초기화</span></a>
								</div>
							
								<hr class="hidden" />
								
								<table id="statistics-month-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text">월별 방문자 수</span></th>
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
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="window.location.href='<?php echo $pluginMenuURL;?>&date=<?php echo $record['date'];?>'">
											<td class="date"><a href="<?php echo $pluginMenuURL;?>&date=<?php echo $record['date'];?>"><?php echo Timestamp::formatDate2(getTimeFromPeriod($record['date']));?></a></td>
											<td class="count"><a href="<?php echo $pluginMenuURL;?>&date=<?php echo $record['date'];?>"><?php echo $record['visits'];?></a></td>
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
											<th colspan="2"><span class="text">일별 방문자 수</span></th>
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
											<td class="date"><?php echo Timestamp::formatDate(getTimeFromPeriod($record['date']));?></td>
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
	$isAjaxRequest = checkAjaxRequest();
	$isAjaxRequest ? respondResultPage(setTotalStatistics($owner) ? 0 : -1) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
