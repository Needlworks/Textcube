<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
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

$d = _t('삭제');
$conditions['template'] = <<<EOS
			<dl id="line_[##_id_##]" class="line">
				<dt class="date">[##_date_##]</dt>
				<dd class="content">[##_content_##]</dd>
				<dd class="permalink"><a href="[##_permalink_##]" class="permalink">at [##_root_##]</a></dd>
				<dd class="delete input-button" onclick="deleteLine('[##_id_##]');return false;"><span class="text">{$d}</span></dd>
			</dl>
EOS;
$conditions['dress'] = array('id'=>'id','date'=>'created','content'=>'content','permalink'=>'permalink','root'=>'root');
$line = Model_Line::getInstance();
$contentView = $line->getFormattedList($conditions);
if(empty($contentView)) {
	$contentView = '
			<dl class="end">
				<dt class="date"></dt>
				<dd class="content">'._t('더이상 라인이 없습니다').'</dd></dl>';
	$buttonView = '';
} else {
	$m = _t('더 보기');
	$buttonView = '<input type="submit" class="more-button input-button" value="'._t('더 보기').'" onclick="getMoreContent('.($conditions['page']+1).','.$conditions['linesforpage'].',\'bottom\');return false;" />';
}
$result = array('error'=>0,'contentView'=> $contentView,'buttonView'=>$buttonView);
Respond::PrintResult($result);
?>
