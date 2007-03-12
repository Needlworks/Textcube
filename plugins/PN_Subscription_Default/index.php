<?php
/* Subscription statistics plugin for Tattertools 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : gendoh, inureyes, graphittie

   Created at       : 2006.9.21
   Last modified at : 2006.10.27
 
 This plugin shows RSS subscription statistics on administration menu.
 For the detail, visit http://forum.tattertools.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function AD_Subscription_Default()
{
	global $owner, $pluginMenuURL, $pluginSelfParam, $totalSubscribers, $updatedSubscribers;
	requireComponent( "Tattertools.Model.Statistics");
	requireComponent( "Tattertools.Function.misc");

	$temp = getSubscriptionStatistics($owner);
	$aggregatorInfo = organizeAggregatorInfo($temp);
?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									//removeItselfById('log-pages-submit');
								}
							//]]>
						</script>
						
						<div id="part-statistics-total" class="part">
							<h2 class="caption"><span class="main-text">RSSフィード統計</span></h2>
							<dl class="data-inbox">
								<dt class="number"><span class="text">全体統計</span></dt>
								<dd class="number"><span class="text"><?php echo $totalSubscribers;?> 人</span></dd>
								<dt class="aggregator"><span class="text">RSSリーダー</span></dt>
								<dd class="aggregator"><span class="text"><?php echo sizeof($aggregatorInfo);?> 種のRSSリーダーからRSSフィード情報を受信しています。</span></dd>
								<dt class="lastRSSupdate"><span class="text">最近 RSS更新日</span></dt>
								<dd class="lastRSSupdate"><span class="text"><?php echo misc::getUserSetting('LatestRSSrefresh',null)!=null ? Timestamp::format5(misc::getUserSetting('LatestRSSrefresh',null)) : '情報更新に失敗しました。';?></dd>
								<dt class="updatedAggregators"><span class="text">更新されたRSSリーダー</span></dt>
								<dd class="updatedAggregators"><span class="text"><?php echo $updatedSubscribers;?></span></dd>
							</dl>
						</div>

						<hr class="hidden" />

						<div id="part-statistics-rank" class="part">
							<h2 class="caption"><span class="main-text">RSSフィードランキング</span></h2>
							<div class="main-explain-box">
								<p class="explain">
									受信者数の情報が含まれていないWeb基盤RSSリーダーからは正確な統計情報が受けられません。<br />
									強調されたRSSリーダー情報は検索サイトやMetaプログラムのことです。
								</p>
							</div>
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="rank"><span class="text">ランキング</span></th>
										<th class="aggregator"><span class="text">リーダー</span></th>
										<th class="count"><span class="text">受信者数</span></th>
										<th class="subscribed"><span class="text">受信開始日</span></th>
										<th class="referred"><span class="text">最近更新日</span></th>
									</tr>
								</thead>
								<tbody>
<?php
	$i = 0;
	foreach ($aggregatorInfo as $agent => $info) {
		$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
		$className .= ($i == sizeof($aggregatorInfo) - 1) ? ' last-line' : '';
?>
									<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="rank"><?php echo $i + 1;?></td>
										<td class="aggregator"><?php 
		if($info['isRobot'])
			echo '<span class="robot">'.$agent.'</span>';
		else echo $agent;
?></td>
										<td class="count"><?php echo $info["subscribers"];?>人</td>
										<td class="subscribed"><?php echo Timestamp::formatDate($info["subscribed"]);?></td>
										<td class="referred"><?php echo Timestamp::formatDate($info["referred"]);?></td>
									</tr>
<?php
		$i++;
	}
?>
								</tbody>
							</table>
						</div>
						
						<div class="clear"></div>
<?php 
}

function getAggregatorName($useragent)
{
	if($useragent=='') return '未知のRSSリーダー';
	$agentPattern = array(
		'Bloglines' => 'Bloglines',
		'Allblog.net' => 'Allblog',
		'HanRSS' => 'HanRSS',
		'Netvibes' => 'Netvibes',
		'SharpReader' => 'Sharp Reader',
		'BlogBridge' => 'Blog Bridge',
		'Firefox' => 'Firefox ライブブックマーク',
		'Sage' => 'Sage (Firefox プラグイン)',
		'Google Desktop' => 'Google Desktop',
		'RSSOwl' => 'RSS Owl',
		'Eolin' => 'テト・ツールズRSSリーダー',
		'Safari' => 'Safari',
		'Feedfetcher-Google' => 'Google feedfetcher',
		'RssBandit' => 'RSS Bandit',
		'Yahoo! Slurp' => 'Yahoo! Slurp',
		'Mozilla/4.0 (compatible; MSIE 7.0' => 'MSIE 7',
		'FeedDemon' => 'FeedDemon',
		'UniversalFeedParser' => 'Universal Feed Parser',
		'nhn/1noon' => '初雪',
		'MSIE 6.0' => 'MSIE 6',
		'YeonMo' => 'YeonMo',
		'RMOM' => 'RMOM',
		'msnbot' => 'MSN Search',
		'FeedOnFeeds' => 'Feed On Feeds Personal aggregator',
		'Technoratibot' => 'Technorati',
		'sproose' => 'sproose bot'
	);
	$declinePattern = array(
		'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'=>'Internet Explorer 5.01'
	);
	foreach ($agentPattern as $agentName => $realname)
		if(strpos($useragent,$agentName)!==false) return $realname;
	foreach ($declinePattern as $agentName => $realname)
		if(strpos($useragent,$agentName)!==false) return false;
	return $useragent;
}

function getNumberOfSubscribers($useragent)
{
	$agentPattern = array(
		'Bloglines' => 'subscribers',
		'HanRSS' => 'subscribers',
		'Netvibes' => 'subscribers'
	);
	foreach ($agentPattern as $agentName => $keyword)
		if(preg_match('/([0-9]+)\s*'.$keyword.'/',$useragent,$matches)) return $matches[1];
	return 1;
}

function robotChecker($useragent)
{
	$robotPattern = array(
		'Allblog.net' => 1,
		'nhn/1noon' => 1,
		'Feedfetcher-Google' => 1,
		'Yahoo! Slurp' => 1,
		'RMOM' => 1,
		'msnbot' => 1,
		'Technoratibot' => 1,
		'sproose' => 1
	);
	foreach ($robotPattern as $agentName => $isRobot)
		if((strpos($useragent,$agentName)!==false)&&($isRobot)) return true;
	return false;
}

function organizeAggregatorInfo($info)
{
	requireComponent( "Tattertools.Function.misc");
	global $totalSubscribers, $updatedSubscribers;
	$aggregatorInfo = array();
	$totalSubscribers = 0;
	$updatedSubscribers = 0;
	$latestUpdatedTime = misc::getUserSetting('LatestRSSrefresh',null);
	for ($i=0; $i<sizeof($info); $i++) {
		$record = $info[$i];
		$aggregatorName = getAggregatorName($record['useragent']);
		if(!$aggregatorName) continue;
		$subscribers = getNumberOfSubscribers($record['useragent']);
		$startDate = $record['subscribed'];
		$referred = $record['referred'];
		if(time()- $referred > 259200) continue;
		if(array_key_exists($aggregatorName,$aggregatorInfo)) {
			if(($subscribers > $aggregatorInfo[$aggregatorName]['subscribers'])&&($subscribers!==1)) {
				$totalSubscribers -= $aggregatorInfo[$aggregatorName]['subscribers'];
				$totalSubscribers += $subscribers;
				if(isset($latestUpdatedTime) && $latestUpdatedTime - $referred < 0) {
					$updatedSubscribers -=$aggregatorInfo[$aggregatorName]['subscribers'];
					$updatedSubscribers += $subscribers;
				}
				$aggregatorInfo[$aggregatorName]['subscribers'] = $subscribers;
			} else if($subscribers==1) {
				$aggregatorInfo[$aggregatorName]['subscribers'] += $subscribers;
				$totalSubscribers += $subscribers;
				if(isset($latestUpdatedTime) && $latestUpdatedTime - $referred < 0) {
					$updatedSubscribers += $subscribers;
				}
			}

			if($aggregatorInfo[$aggregatorName]['subscribed'] > $startDate)
				$aggregatorInfo[$aggregatorName]['subscribed'] = $startDate;
			if($aggregatorInfo[$aggregatorName]['referred'] < $referred)
				$aggregatorInfo[$aggregatorName]['referred'] = $referred;
		}
		else {
			$aggregatorInfo[$aggregatorName]['subscribers'] = $subscribers;
			$aggregatorInfo[$aggregatorName]['subscribed'] = $startDate;
			$aggregatorInfo[$aggregatorName]['referred'] = $referred;
			$totalSubscribers += $subscribers;
		}
		$aggregatorInfo[$aggregatorName]['isRobot'] = robotChecker($record['useragent']);
	}
	arsort($aggregatorInfo);
	return $aggregatorInfo;
}

function organizeRobotInfo($info)
{
}

function getSubscriptionStatistics($owner) {
	global $database;
	$statistics = array();
	if ($result = mysql_query("select ip, host, useragent, subscribed, referred from {$database['prefix']}SubscriptionStatistics where owner = $owner order by referred desc")) {
		while ($record = mysql_fetch_array($result))
			array_push($statistics, $record);
	}
	return $statistics;
}

function getSubscriptionLogsWithPage($page, $count) {  
	global $database, $owner;
	requireComponent( "Tattertools.Model.Statistics");
	return Statistics::fetchWithPaging("SELECT ip, host, useragent, referred FROM {$database['prefix']}SubscriptionLogs WHERE owner = $owner ORDER BY referred DESC", $page, $count);  
}  

function getSubscriptionLogs() {
	global $database, $owner;
	return DBQuery::queryAll("SELECT ip, host, useragent, referred FROM {$database['prefix']}SubscriptionLogs WHERE owner = $owner ORDER BY referred DESC LIMIT 1000");
}

function updateSubscriptionStatistics($target, $mother) {
	global $owner, $database, $blogURL;
	$period = Timestamp::getDate();
	requireComponent('Tattertools.Data.Filter');
	if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']))
		return;
	$ip = mysql_tt_escape_string($_SERVER['REMOTE_ADDR']);
	$host = mysql_tt_escape_string(isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '');
	$useragent = mysql_tt_escape_string(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
	mysql_query("insert into {$database['prefix']}SubscriptionLogs values($owner, '$ip', '$host', '$useragent', UNIX_TIMESTAMP())");
	mysql_query("delete from {$database['prefix']}SubscriptionLogs where referred < UNIX_TIMESTAMP() - 604800");
	if (!mysql_query("update {$database['prefix']}SubscriptionStatistics set referred = UNIX_TIMESTAMP() where owner = $owner and ip = '$ip' and host = '$host' and useragent = '$useragent'") || (mysql_affected_rows() == 0))
		mysql_query("insert into {$database['prefix']}SubscriptionStatistics values($owner, '$ip', '$host', '$useragent', UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
	return $target;
}

function AD_Subscription_setTime($target) {
	requireComponent( "Tattertools.Function.misc");
	misc::setUserSetting('LatestRSSrefresh',time());
	return true;
}
?>