<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'stype'=>array('int'),
		'userid'=>array('int')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (changeACLonTeamblog($owner,$_POST['stype'],$_POST['userid'])) {
	respondResultPage(0);
}
respondResultPage(-1);
?>