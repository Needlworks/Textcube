<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'blogid'=>array('id'),
		'acltype'=>array('string'),
		'userid'=>array('int'),
		'switch'=>array('int')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
requirePrivilege('group.creators');
if (changeACLonBlog($_GET['blogid'],$_GET['acltype'],$_GET['userid'],$_GET['switch'])) {
	return Respond::ResultPage(true);
}
Respond::ResultPage(false);
?>
