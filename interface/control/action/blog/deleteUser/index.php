<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'userid'=>array('id'),
		'blogid'=>array('id')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
requirePrivilege('group.creators');

if (deleteTeamblogUser($_GET['userid'],$_GET['blogid'],false)) {
	Respond::ResultPage(0);
}
Respond::ResultPage(-1);
?>
