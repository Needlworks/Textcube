<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'acltype'=>array('string'),
		'userid'=>array('int'),
		'switch'=>array('int')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (changeACLonBlog(getBlogId(),$_POST['acltype'],$_POST['userid'],$_POST['switch'])) {
	return Respond::ResultPage(true);
}
Respond::ResultPage(false);
?>
