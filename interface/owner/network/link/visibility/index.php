<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0,3)
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.link");
requireStrictRoute();
$respond = array();
list($result,$visibility) = toggleLinkVisibility($blogid, $suri['id'],$_POST['visibility']);
respond::PrintResult( array( 'error' => $result ? 0 : 1, 'visibility' => $visibility ), false );
?>
