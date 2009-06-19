<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
'userid'=>array('id')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if (deleteTeamblogUser($_POST['userid'])) {
	Utils_Respond::ResultPage(0);
}
Utils_Respond::ResultPage(-1);
?>
