<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';
?>
						<form method="post" action="<?php echo  $blogURL?>/owner/statistics">
							<div id="part-statistics-rank" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('리퍼러 순위')?></span></h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="number"><span class="text"><?php echo _t('순위')?></span></th>
											<th class="site"><span class="text"><?php echo _t('리퍼러')?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
$temp = getRefererStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo  $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="rank"><?php echo $i + 1?>.</td>
											<td class="site"><a href="http://<?php echo escapeJSInAttribute($record['host'])?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($record['host'])?></a> <span class="count">(<?php echo $record['count']?>)</span></td>
										</tr>
<?php
}
?>
									</tbody>
								</table>
							</div>
							
							<hr class="hidden" />
							
							<div id="part-statistics-log" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('리퍼러 로그')?></span></h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="number"><span class="text"><?php echo _t('날짜')?></span></th>
											<th class="site"><span class="text"><?php echo _t('주소')?></span></th>
										</tr>
									</thead>
									<tbody>
<?php
$temp = getRefererLogs();
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo  $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="date"><?php echo Timestamp::formatDate($record['referred'])?></td>
											<td class="address"><a href="<?php echo escapeJSInAttribute($record['url'])?>" onclick="window.open(this.href); return false;" title="<?php echo htmlspecialchars($record['url'])?>"><?php echo fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
										</tr>
<?php
}
?>
									</tbody>
								</table>
							</div>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>