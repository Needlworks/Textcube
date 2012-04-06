<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);

require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");

requireStrictRoute();
if(isset($suri['id'])) {
	if (deleteTrackbackLog($blogid, $suri['id']) !== false)
		Respond::ResultPage(0);
	else
		Respond::ResultPage(-1);
} else if(!empty($_POST['targets'])) {
	foreach(explode(',', $_POST['targets']) as $target)
		deleteTrackbackLog($blogid, $target);
	Respond::ResultPage(0);
}
Respond::ResultPage(-1);
?> 
