<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';
?>
									<div id="part-statistics-rank" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('리퍼러 순위')?></span></h2>
										
										<table class="data-inbox" cellspacing="0" cellpadding="0" border="0">
<?
$temp = getRefererStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	if ($i == sizeof($temp) - 1) {
?>
											<tr class="tr-last-body overInactive" onmouseover="rolloverTableTr(this, 'over')" onmouseout="rolloverTableTr(this, 'out')">
												<td class="number" width="20"><?=$i + 1?>.</td>
												<td class="site"><a href="http://<?=escapeJSInAttribute($record['host'])?>" onclick="window.open(this.href); return false;"><?=htmlspecialchars($record['host'])?></a> <span class="count">(<?=$record['count']?>)</span></td>
											</tr>
<?
	} else {
?>
											<tr class="tr-body overInactive" onmouseover="rolloverTableTr(this, 'over')" onmouseout="rolloverTableTr(this, 'out')">
												<td class="number" width="20"><?=$i + 1?>.</td>
												<td class="site"><a href="http://<?=escapeJSInAttribute($record['host'])?>" onclick="window.open(this.href); return false;"><?=htmlspecialchars($record['host'])?></a> <span class="count">(<?=$record['count']?>)</span></td>
											</tr>
<?
	}
}
?>
										</table>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-statistics-log" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('리퍼러 로그')?></span></h2>
										
										<table class="data-inbox" cellspacing="0" cellpadding="0" border="0">
<?
$temp = getRefererLogs();
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	if ($i == sizeof($temp) - 1) {
?>
											<tr class="tr-last-body overInactive" onmouseover="rolloverTableTr(this, 'over')" onmouseout="rolloverTableTr(this, 'out')">
												<td class="date"><?=Timestamp::formatDate($record['referred'])?></td>
												<td class="address"><a href="<?=escapeJSInAttribute($record['url'])?>" onclick="window.open(this.href); return false;" title="<?=htmlspecialchars($record['url'])?>"><?=fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
											</tr>
<?
	} else {
?>
											<tr class="tr-body overInactive" onmouseover="rolloverTableTr(this, 'over')" onmouseout="rolloverTableTr(this, 'out')">
												<td class="date"><?=Timestamp::formatDate($record['referred'])?></td>
												<td class="address"><a href="<?=escapeJSInAttribute($record['url'])?>" onclick="window.open(this.href); return false;" title="<?=htmlspecialchars($record['url'])?>"><?=fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
											</tr>
<?
	}
}
?>
										</table>
									</div>
									
									<div class="clear"></div>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>