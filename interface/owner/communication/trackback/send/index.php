<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.trackback");
requireComponent('Textcube.Function.Respond');

requireStrictRoute();
respond::ResultPage(!empty($_GET['url']) && sendTrackback($blogid, $suri['id'], trim($_GET['url'])));
?>
