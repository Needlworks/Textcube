<?php
/* Referer statistics plugin for Textcube 1.7
   ----------------------------------
   Version 1.7
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : gendoh, inureyes, graphittie

   Created at       : 2006.8.15
   Last modified at : 2009.6.17
 
 This plugin shows referer statistics on administration menu.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function PN_Referer_Default()
{
	global $pluginMenuURL, $pluginSelfParam;
	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['page']))
		$_GET['page'] = $_POST['page'];
	
	$page = Setting::getBlogSetting('RowsPerPageReferer',20);
	if (empty($_POST['perPage'])) {  
		$perPage = $page;  
	} else if ($page != $_POST['perPage']) { 
		Setting::setBlogSetting('RowsPerPageReferer',$_POST['perPage']);  
		$perPage = $_POST['perPage'];  
	} else {  
		$perPage = $_POST['perPage'];  
	}  
?>
						<div id="part-statistics-rank" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t("리퍼러 순위");?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="number"><span class="text"><?php echo _t("순위");?></span></th>
										<th class="site"><span class="text"><?php echo _t("리퍼러");?></span></th>
									</tr>
								</thead>
								<tbody>
<?php
	$temp = Statistics::getRefererStatistics(getBlogId());
	for ($i=0; $i<count($temp); $i++) {
		$record = $temp[$i];
		
		$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="rank"><?php echo $i + 1;?>.</td>
										<td class="site"><a href="http://<?php echo Utils_Misc::escapeJSInAttribute($record['host']);?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($record['host']);?></a> <span class="count">(<?php echo $record['count'];?>)</span></td>
									</tr>
<?php
	}
?>
								</tbody>
							</table>
						</div>
						
						<hr class="hidden" />
						
						<form id="part-statistics-log" class="part" method="post" action="<?php echo $pluginMenuURL;?>">
							<h2 class="caption"><span class="main-text"><?php echo _t("리퍼러 로그");?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="number"><span class="text">날짜</span></th>
										<th class="site"><span class="text">주소</span></th>
									</tr>
								</thead>
								<tbody>
<?php
	$more = false;
	list($referers, $paging) = Statistics::getRefererLogsWithPage($_GET['page'], $perPage);
	for ($i=0; $i<count($referers); $i++) {
		$record = $referers[$i];
		
		$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($i == sizeof($referers) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="date"><?php echo Timestamp::formatDate($record['referred']);?></td>
										<td class="address"><a href="<?php echo Utils_Misc::escapeJSInAttribute($record['url']);?>" onclick="window.open(this.href); return false;" title="<?php echo htmlspecialchars($record['url']);?>"><?php echo fireEvent('ViewRefererURL', htmlspecialchars(Utils_Unicode::lessenAsEm($record['url'], 70)), $record);?></a></td>
									</tr>
<?php
	}
?>
								</tbody>
							</table>
							
							<div class="data-subbox">
								<div id="page-section" class="section">
									<div id="page-navigation">
										<span id="page-list">
<?php
	$paging['prefix'] = $pluginSelfParam . '&page=';
	$pagingTemplate = '[##_paging_rep_##]';
	$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
	echo str_repeat("\t", 8).Paging::getPagingView($paging, $pagingTemplate, $pagingItemTemplate).CRLF;
?>
										</span>
									</div>
									<div class="page-count">
										<?php echo Utils_Misc::getArrayValue(explode('%1', '한 페이지에 목록 %1건 표시'), 0);?>
										<select name="perPage" onchange="document.getElementById('part-statistics-log').submit()">					
<?php
	for ($i = 10; $i <= 30; $i += 5) {
		if ($i == $perPage) {
?>
											<option value="<?php echo $i;?>" selected="selected"><?php echo $i;?></option>
<?php
		} else {
?>
											<option value="<?php echo $i;?>"><?php echo $i;?></option>
<?php
		}
	}
?>
										</select>
										<?php echo Utils_Misc::getArrayValue(explode('%1', '한 페이지에 목록 %1건 표시'), 1);?>
									</div>
								</div>
							</div>
						</form>
						
						<div class="clear"></div>
<?php 
}
?>
