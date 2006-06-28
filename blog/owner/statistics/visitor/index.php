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
									if (confirm("<?=_t('방문자의 수를 초기화하면 방문객의 수가 0이 됩니다.\n정말 초기화하시겠습니까?')?>")) {
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
							//]]>
						</script>
					 		
					 	<form method="post" action="<?=$blogURL?>/owner/statistics/visitor/set/0">
					 		<div id="part-statistics-visitor" class="part">
					 			<h2 class="caption"><span class="main-text"><?=_t('방문자 통계정보를 보여줍니다')?></span></h2>
					 			
						 		<div id="statistics-counter-inbox" class="data-inbox">
									<div class="title">
										<span class="label"><span class="text"><?=_t('현재까지의 방문자 수')?></span></span>
										<span class="divider"> : </span>
										<span id="total"><?=number_format($stats['total'])?></span>
									</div>
									<a class="init-button button" href="<?=$blogURL?>/owner/statistics/visitor/set/0?javascript=disabled" onclick="setTotalStatistics(); return false;"><span class="text"><?=_t('초기화')?></span></a>
								</div>
							
								<hr class="hidden" />
								
								<table id="statistics-month-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text"><?=_t('월별 방문자 수')?></span></th>
										</tr>
									</thead>
									<tbody>
<?
$temp = getMonthlyStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')" onclick="window.location.href='<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>'">
											<td class="date"><a href="<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>"><?=Timestamp::formatDate2(getTimeFromPeriod($record['date']))?></a></td>
											<td class="count"><a href="<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>"><?=$record['visits']?></a></td>
										</tr>
<?
}
?>
									</tbody>
								</table>
								
								<hr class="hidden" />
								
								<table id="statistics-day-inbox" class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th colspan="2"><span class="text"><?=_t('일별 방문자 수')?></span></th>
										</tr>
									</thead>
									<tbody>
<?
if (isset($suri['id'])) {
	$temp = getDailyStatistics($suri['id']);
	for ($i=0; $i<sizeof($temp); $i++) {
		$record = $temp[$i];
		
		$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className?> inactive-class">
											<td class="date"><?=Timestamp::formatDate(getTimeFromPeriod($record['date']))?></td>
											<td class="count"><?=$record['visits']?></td>
										</tr>
<?
	}
}
?>
									</tbody>
								</table>
							</div>
						</form>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>