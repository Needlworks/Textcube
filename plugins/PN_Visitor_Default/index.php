<?php
/* Visitor statistics plugin for Textcube 2.0
   ------------------------------------------
   Version 2.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : gendoh, inureyes, graphittie

   Created at       : 2006.9.21
   Last modified at : 2015.6.30

 This plugin shows visitor statistics on administration menu.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function PN_Visitor_Default()
{
	$context = Model_Context::getInstance();
	$blogid = getBlogId();
	$stats = Statistics::getStatistics($blogid);
	$date = isset($_GET['date']) ? $_GET['date'] : date('Ym', strtotime("now"));
?>
<!-- This tab space below this line is inserted for the indentation of original admin page -->
						<script type="text/javascript">
							//<![CDATA[
<?php
	if(Acl::check('group.owners')) {
?>
								function setTotalStatistics() {
									if (confirm(_t('방문자의 수를 초기화하면 방문객의 수가 0이 됩니다.\n정말 초기화하시겠습니까?'))) {
										var request = new HTTPRequest("GET", "<?php echo $context->getProperty('plugin.uri.handler');?>/PN_Visitor_Default_set&ajaxcall");
										request.onSuccess = function() {
											//document.getElementById("total").innerHTML = 0;
											window.location = '<?php echo $context->getProperty('plugin.uri.menu');?>';
											return true;
										}
										request.onError = function() {
											alert("<?php echo _t('저장하지 못했습니다.');?>");
											return false;
										}
										request.send();
									}
								}
<?php
	}
?>

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

					 	<form method="post" action="<?php echo $context->getProperty('plugin.uri.handler');?>PN_Visitor_Default_set">
					 		<div id="part-statistics-visitor" class="part">
					 			<h2 class="caption"><span class="main-text"><?php echo _t('방문자 통계정보를 보여줍니다');?></span></h2>

						 		<div id="statistics-counter-inbox" class="data-inbox">
									<div class="title">
										<span class="label"><span class="text"><?php echo _t('현재까지의 방문자 수');?></span></span>
										<span class="divider"> : </span>
										<span id="total"><?php echo number_format($stats['total']);?></span>
									</div>
<?php
	if(Acl::check('group.owners')) {
?>
									<a class="init-button button" href="<?php echo $context->getProperty('plugin.uri.handler');?>/PN_Visitor_Default_set" onclick="setTotalStatistics(); return false;"><span class="text"><?php echo _t('초기화');?></span></a>
<?php
	}
?>
								</div>

								<hr class="hidden" />

								<table id="statistics-month-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text"><?php echo _t('월별 방문자 수');?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
$temp = Statistics::getMonthlyStatistics($blogid);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];

	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="window.location.href='<?php echo $context->getProperty('plugin.uri.menu');?>&amp;date=<?php echo $record['datemark'];?>'">
											<td class="date"><a href="<?php echo $context->getProperty('plugin.uri.menu');?>&amp;date=<?php echo $record['datemark'];?>"><?php echo Timestamp::formatDate2(Utils_Misc::getTimeFromPeriod($record['datemark']));?></a></td>
											<td class="count"><a href="<?php echo $context->getProperty('plugin.uri.menu');?>&amp;date=<?php echo $record['datemark'];?>"><?php echo $record['visits'];?></a></td>
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
											<th colspan="2"><span class="text"><?php echo _t('일별 방문자 수');?></span></th>
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
											<td class="date"><?php echo Timestamp::formatDate(Utils_Misc::getTimeFromPeriod($record['datemark']));?></td>
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

function PN_Visitor_Default_set()
{
	$blogid = getBlogId();
	$isAjaxRequest = isset($_REQUEST['ajaxcall']) ? true : false;
	if ($isAjaxRequest) {
		$result = Statistics::setTotalStatistics($blogid) ? 0 : -1;
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$result</error>\n</response>");
		exit;
	} else {
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
}
?>
