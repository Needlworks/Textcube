<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'visibility' => array('int',0,3)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (setting::setBlogSettingGlobal('visibility',$_GET['visibility'])) respond::ResultPage(0);
respond::ResultPage(-1);
?>
