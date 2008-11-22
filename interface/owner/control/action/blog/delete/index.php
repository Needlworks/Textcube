<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'item' => array('string')
	) 
);
require ROOT . '/library/includeForBlogOwner.php';

requireStrictRoute();
requirePrivilege('group.creators');

$items = split(",",$_GET['item']);

if (in_array(getServiceSetting("defaultBlogId",1),$items)) {
	$result = _t('대표 블로그는 삭제할 수 없습니다.');
	respond::PrintResult(array('error' => -1 , 'result' =>$result));
}

foreach ($items as $item) {
	$result = removeBlog($item);
	if ($result!==true) {
		respond::PrintResult(array('error' => -1 , 'result' =>$result));
	}
}
respond::PrintResult(array('error' => 0));
?>
