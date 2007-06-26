<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'timezone' => array('string')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (isset($_GET['timezone']) && Timezone::set($_GET['timezone'])) {
	setBlogSetting('timezone',$_GET['timezone']);
	respondResultPage(0);
}
respondResultPage( - 1);
?>
