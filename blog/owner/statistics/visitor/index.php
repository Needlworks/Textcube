<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$stats = getStatistics($owner);
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu40.php';
?>
									<script type="text/javascript">
										//<![CDATA[
											function setTotalStatistics() {
												if (confirm("<?=_t('방문자의 수를 초기화하면 방문객의 수가 0 이 됩니다. 정말 초기화합니까?')?>")) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/statistics/visitor/set/0");
													request.onSuccess = function() {
														document.getElementById("total").innerHTML = 0;
														return true;
													}
													request.onError = function() {
														alert("<?=_t('저장하지 못했습니다.')?>");
														return false;
													}
													request.send();
												}
											}
											
											function addCommas(nStr)
											{
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
										//]]>
									</script>
					 			
					 				<div id="part-statistics-counter" class="part">
						 				<div class="data-inbox">
							 				<div class="title">
							 					<span class="label"><span class="text"><?=_t('현재까지의 방문자 수')?></span></span>
							 					<span class="divider"> : </span>
							 					<span id="total"><?=number_format($stats['total'])?></span>
											</div>
											<a class="init-button button" href="#void" onclick="setTotalStatistics()"><span><?=_t('초기화')?></span></a>
										</div>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-statistics-month" class="part">
										<table class="data-inbox" cellspacing="0" cellpadding="0">
											<thead>
												<tr>
													<td colspan="2"><span class="text"><?=_t('월별 방문자 수')?></span></td>
												</tr>
											</thead>
											<tbody>
<?
$temp = getMonthlyStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	if ($i == sizeof($temp) - 1) {
?>
												<tr class="tr-last-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="location.href='<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>'">
													<td class="date"><?=Timestamp::formatDate2(getTimeFromPeriod($record['date']))?></td>
													<td class="count"><?=$record['visits']?></td>
												</tr>
<?
	} else {
?>
												<tr class="tr-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="location.href='<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>'">
													<td class="date"><?=Timestamp::formatDate2(getTimeFromPeriod($record['date']))?></td>
													<td class="count"><?=$record['visits']?></td>
												</tr>
<?
	}
}
?>
											</tbody>
										</table>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-statistics-day" class="part">
										<table class="data-inbox" cellspacing="0" cellpadding="0">
											<thead>
												<tr>
													<td colspan="2"><span class="text"><?=_t('일별 방문자 수')?></span></td>
												</tr>
											</thead>
											<tbody>
<?
if (isset($suri['id'])) {
	$temp = getDailyStatistics($suri['id']);
	for ($i=0; $i<sizeof($temp); $i++) {
		$record = $temp[$i];
		
		if ($i == sizeof($temp) - 1) {
?>
												<tr class="tr-last-body inactive-class">
													<td class="date"><?=Timestamp::formatDate(getTimeFromPeriod($record['date']))?></td>
													<td class="count"><?=$record['visits']?></td>
												</tr>
<?
		} else {
?>
												<tr class="tr-body inactive-class">
													<td class="date"><?=Timestamp::formatDate(getTimeFromPeriod($record['date']))?></td>
													<td class="count"><?=$record['visits']?></td>
												</tr>
<?
		}
	}
}
?>
											</tbody>
										</table>
									</div>
									
									<div class="clear"></div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>