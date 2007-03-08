<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'userid' => array('id')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
$result = cancelInvite($_POST['userid']);
if ($result) {
	respondResultPage(0);
} else {
	respondResultPage(1);
}
?>