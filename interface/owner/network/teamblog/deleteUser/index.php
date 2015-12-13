<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
'userid'=>array('id')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if (deleteTeamblogUser($_POST['userid'])) {
	Respond::ResultPage(0);
}
Respond::ResultPage(-1);
?>
