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
	dress('line_rep', $itemsView, $lineView);
	$lineView = fireEvent('ViewLine', $lineView, $lines);
	dress('line_rssurl',$context->getProperty('uri.default').'/rss/line',$lineView);
	dress('line_atomurl',$context->getProperty('uri.default').'/atom/line',$lineView);

//	if(empty($lines)) $lineView = $lineView.CRLF.'[##_paging_line_##]';
	
	dress('line', $lineView, $view);
	if(isset($cache)) { 
		$cache->contents = $lineView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
