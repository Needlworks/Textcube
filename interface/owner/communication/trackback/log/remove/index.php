<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);

require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.trackback");
requireComponent('Textcube.Function.Respond');

requireStrictRoute();
if(isset($suri['id'])) {
	if (deleteTrackbackLog($blogid, $suri['id']) !== false)
		respond::ResultPage(0);
	else
		respond::ResultPage(-1);
} else if(!empty($_POST['targets'])) {
	foreach(explode(',', $_POST['targets']) as $target)
		deleteTrackbackLog($blogid, $target);
	respond::ResultPage(0);
}
respond::ResultPage(-1);
?> 
