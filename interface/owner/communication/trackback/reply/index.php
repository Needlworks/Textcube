<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/library/preprocessor.php';
importlib("model.blog.remoteresponse");

requireStrictRoute();

/// First, detect trackback URL from RDF information.
$info  = getRDFfromURL($_POST['url']);
if(empty($info)) {
	$blogInfo = getInfoFromURL($_POST['url']);
	if(!empty($blogInfo) && $blogInfo['service'] != null) {
		$info['trackbackURL'] = getTrackbackURLFromInfo($_POST['url'],$blogInfo['service']);
	} else {
		Respond::ResultPage(false);
		exit;
	}
}
Respond::ResultPage(!empty($_POST['url']) && sendTrackback($blogid, $suri['id'], trim($info['trackbackURL'])));
?>
