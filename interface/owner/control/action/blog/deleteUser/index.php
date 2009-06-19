<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
	Utils_Respond::ResultPage(0);
}
Utils_Respond::ResultPage(-1);
?>
