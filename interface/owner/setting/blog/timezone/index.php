<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'timezone' => array('string')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$timezone = new Timezone;
if (isset($_GET['timezone']) && $timezone->set($_GET['timezone'])) {
	setBlogSetting('timezone',$_GET['timezone']);
	respond::ResultPage(0);
}
respond::ResultPage( - 1);
?>
