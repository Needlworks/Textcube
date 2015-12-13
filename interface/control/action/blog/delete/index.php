<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'item' => array('string')
	) 
);
require ROOT . '/library/preprocessor.php';

requireStrictRoute();
requirePrivilege('group.creators');

$items = explode(",",$_GET['item']);

if (in_array(getServiceSetting("defaultBlogId",1),$items)) {
	$result = _t('대표 블로그는 삭제할 수 없습니다.');
	Respond::PrintResult(array('error' => -1 , 'result' =>$result));
}

foreach ($items as $item) {
	$result = removeBlog($item);
	if ($result!==true) {
		Respond::PrintResult(array('error' => -1 , 'result' =>$result));
	}
}
Respond::PrintResult(array('error' => 0));
?>
