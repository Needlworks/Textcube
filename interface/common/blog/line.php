<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


if (isset($cache->contents)) {
	dress('line', $cache->contents, $view);
} else if (isset($lines) && isset($skin)) {
	$lineView = $skin->line;
	$itemsView = '';
	$printDate = '';
	foreach ($lines as $item) {
		$time = Timestamp::getHumanReadable($item['created']);
		/*
		if($printDate != Timestamp::formatDate($item['created'])) {
			$time = Timestamp::format5($item['created']);
			$printDate = Timestamp::formatDate($item['created']);
		} else {
			$time = Timestamp::format('%H:%M',$item['created']);
		}*/
		$itemsView .= str_replace(
			array(
				'[##_line_rep_regdate_##]',
				'[##_line_rep_content_##]'
			),
			array(
				fireEvent('ViewLineDate', $time, $item['created']),
				fireEvent('ViewLineContent', htmlspecialchars($item['content']))
			),
			$skin->lineItem
		);
	}
	dress('line_rep', $itemsView, $lineView);
	$lineView = fireEvent('ViewLine', $lineView, $lines);
	dress('line_rssurl',$defaultURL.'/rss/line',$lineView);
	dress('line_atomurl',$defaultURL.'/atom/line',$lineView);

//	if(empty($lines)) $lineView = $lineView.CRLF.'[##_paging_line_##]';
	
	dress('line', $lineView, $view);
	if(isset($cache)) { 
		$cache->contents = $lineView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
