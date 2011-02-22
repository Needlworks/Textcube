<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
		if($item['root'] == 'default') $item['root'] = 'Textcube Line';
		$itemsView .= str_replace(
			array(
				'[##_line_rep_regdate_##]',
				'[##_line_rep_content_##]',
				'[##_line_rep_author_##]',
				'[##_line_rep_source_##]',
				'[##_line_rep_permalink_##]'
			),
			array(
				fireEvent('ViewLineDate', $time, $item['created']),
				fireEvent('ViewLineContent', $item['content']),
				fireEvent('ViewLineAuthor', htmlspecialchars($item['author'])),
				fireEvent('ViewLineSource', htmlspecialchars($item['root'])),
				fireEvent('ViewLinePermalink', $item['permalink'])
			),
			$skin->lineItem
		);
	}
	$itemsView = '<div id="line-content">'.CRLF.$itemsView.CRLF.'</div>';
	dress('line_rep', $itemsView, $lineView);
	$buttonView = str_replace(
		array(
			'[##_line_onclick_more_##]'
		),
		array(
			'getMoreLineStream(2,20,\'bottom\');return false;'
		),
		$skin->lineButton
	);
	$buttonView = '<div id="line-more-page">'.CRLF.$buttonView.CRLF.'</div>';
	dress('line_button', $buttonView, $lineView);
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
