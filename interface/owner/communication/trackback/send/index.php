<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");

requireStrictRoute();
Respond::ResultPage(!empty($_GET['url']) && sendTrackback($blogid, $suri['id'], trim($_GET['url'])));
?>
