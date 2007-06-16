<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");

requireStrictRoute();
respondResultPage(!empty($_GET['url']) && sendTrackback($owner, $suri['id'], $_GET['url']));
?>
