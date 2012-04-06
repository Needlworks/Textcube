<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'page' => array ('int','min'=>1),
		'lines' => array ('int','min'=>1, 'default'=>15),
		'category' => array('string','mandatory'=>false),
		'keyword' => array('string','mandatory'=>false)	
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$conditions = array();
$conditions['blogid'] = getBlogId();
$conditions['page'] = $_POST['page'];
if(isset($_POST['category'])) $conditions['category'] = $_POST['category'];
if(isset($_POST['keyword'])) $conditions['keyword'] = $_POST['keyword'];
$conditions['linesforpage'] = $_POST['lines'];

$skin = new Skin($context->getProperty('skin.skin'));
	
$line = Model_Line::getInstance();
$stream = $line->getWithConditions($conditions);

$contentView = '';

foreach ($stream as $item) {
	$time = Timestamp::getHumanReadable($item['created']);
	if($item['root'] == 'default') $item['root'] = 'Textcube Line';
	$contentView .= str_replace(
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

if(empty($stream)) {
	$contentView = '';
	$buttonView = '';
} else {
	$buttonView = str_replace(
		array(
			'[##_line_onclick_more_##]'
		),
		array(
			'getMoreLineStream('.($conditions['page']+1).','.$conditions['linesforpage'].',\'bottom\');return false;'
		),
		$skin->lineButton
	);
}
$result = array('error'=>0,'contentView'=> $contentView,'buttonView'=>$buttonView);
Respond::PrintResult($result);
?>
