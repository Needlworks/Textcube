<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'userid' => array('id')
	)
);
requireStrictRoute();
$result = cancelInvite($_POST['userid']);
if ($result) {
	Respond::ResultPage(0);
} else {
	Respond::ResultPage(1);
}
?>
