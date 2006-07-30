<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'perPage' => array('int', 1, 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';

$page = getUserSetting('rowsPerPage', 20);
if (empty($_POST['perPage'])) {  
	$perPage = $page;  
} else if ($page != $_POST['perPage']) {  
	setUserSetting('rowsPerPage', $_POST['perPage']);  
	$perPage = $_POST['perPage'];  
} else {  
	$perPage = $_POST['perPage'];  
}  

?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									removeItselfById('log-pages-submit');
								}
							//]]>
						</script>
						
						
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
									<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
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
						
						<form id="part-statistics-log" class="part" method="post" action="<?php echo $blogURL?>/owner/statistics/referer">
							<h2 class="caption"><span class="main-text"><?php echo _t('리퍼러 로그')?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="number"><span class="text"><?php echo _t('날짜')?></span></th>
										<th class="site"><span class="text"><?php echo _t('주소')?></span></th>
									</tr>
								</thead>
								<tbody>
<?
$more = false;
list($refereres, $paging) = getRefererLogsWithPage($suri['page'], $perPage);
for ($i=0; $i<sizeof($refereres); $i++) {
	$record = $refereres[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($referers) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="date"><?php echo Timestamp::formatDate($record['referred'])?></td>
										<td class="address"><a href="<?php echo escapeJSInAttribute($record['url'])?>" onclick="window.open(this.href); return false;" title="<?php echo htmlspecialchars($record['url'])?>"><?php echo fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
									</tr>
<?php
}
?>
								</tbody>
							</table>
							
							<hr class="hidden" />
							
							<div class="data-subbox">
								<div id="page-section" class="section">
									<div id="page-navigation">
										<span id="page-list">
<?php
//$paging['onclick_url'] = 'document.getElementById('list-form').page.value=';
//$paging['onclick_prefix'] = '';
//$paging['onclick_postfix'] = '; document.getElementById('list-form').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
echo str_repeat("\t", 8).getPagingView($paging, $pagingTemplate, $pagingItemTemplate).CRLF;
?>
										</span>
									</div>
									<div class="page-count">
										<?php echo getArrayValue(explode('%1', _t('한 페이지에 목록 %1건 표시')), 0)?>
										<select name="perPage" onchange="document.getElementById('part-statistics-log').submit()">					
<?php
for ($i = 10; $i <= 100; $i += 5) {
	if ($i == $perPage) {
?>
											<option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php
	} else {
?>
											<option value="<?=$i?>"><?=$i?></option>
<?php
	}
}
?>
										</select>
										<?php echo getArrayValue(explode('%1', _t('한 페이지에 목록 %1건 표시')), 1)?>
										
										<input type="submit" id="log-pages-submit" value="<?php echo _t('갱신')?>" />
									</div>
								</div>
							</div>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
