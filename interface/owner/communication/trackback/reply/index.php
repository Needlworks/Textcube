<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");

requireStrictRoute();

/// First, detect trackback URL from RDF information.
$info  = getRDFfromURL($_POST['url']);
if(empty($info)) {
	/// TODO : parse trackback URL information from site address.
	respond::ResultPage(false);
	exit;
}
respond::ResultPage(!empty($_POST['url']) && sendTrackback($blogid, $suri['id'], trim($info['trackbackURL'])));
?>
