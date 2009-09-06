<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'page' => array ('int','min'=>1),
		'tagId' => array ('int','min'=>1)	
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
requireModel('blog.entry');
/// Loads entry list.
$listWithPaging = getEntryListWithPagingByTag(getBlogId(), $_POST['tagId'], $_POST['page'], 20);
$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
$text['date'] = _t('등록일자');
$text['title'] = _t('제목');
$entryView = <<<EOS
<table class="data-inbox">
	<thead>
		<tr>
			<th class="selection"></th>
			<th class="title"><span class="text">{$text['title']}</span></th>
			<th class="date"><span class="text">{$text['date']}</span></th>
		</tr>
	</thead>
	<tbody>
EOS;
foreach ($list['items'] as $l) {
	$entryView .= '<tr>'.CRLF.
		'<td class="selection"></td>'.CRLF.
		'<td class="title">'.$l['title'].'</td>'.CRLF.
		'<td class="date">'.Timestamp::formatDate($l['published']).'</td>'.CRLF.
		'</tr>'.CRLF;
}

$entryView .= '</tbody>'.CRLF.'</table>';

$result = array('error'=>0,'entryView'=> $entryView);
Respond::PrintResult($result);
?>