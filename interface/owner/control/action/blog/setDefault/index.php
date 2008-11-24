<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'blogid' => array('id')
	) 
);
require ROOT . '/library/preprocessor.php';

requireStrictRoute();
requirePrivilege('group.creators');

if ( setDefaultBlog($_GET['blogid'])) {
	respond::PrintResult(array('error' => 0));
}
else {
	$result = _t('블로그가 존재하지 않거나, 블로그의 소유자가 전체 관리자가 아닙니다.');
	respond::PrintResult(array('error' => -1 , 'result' =>$result));
}
?>
