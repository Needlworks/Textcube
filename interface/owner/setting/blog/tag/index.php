<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'tags' => array('string', 'default' => '')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
//$tags = explode(trim($_POST['tags']),',');
if (setBlogTags($blogid, trim($_POST['tags']))) {
	respond::ResultPage(0);
}
respond::ResultPage(-1);
?>
