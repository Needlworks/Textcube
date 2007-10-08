<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'tags' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
//$tags = explode(trim($_POST['tags']),',');
if (setBlogTags($blogid, trim($_POST['tags']))) {
	respondResultPage(0);
}
respondResultPage(-1);
?>
