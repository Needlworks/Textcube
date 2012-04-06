<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'userid' => array('id')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$result = cancelInvite($_POST['userid']);
if ($result) {
	Respond::ResultPage(0);
} else {
	Respond::ResultPage(1);
}
?>
