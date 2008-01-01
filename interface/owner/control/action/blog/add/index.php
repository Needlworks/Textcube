<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'identify' => array('string', 'min' => 1),
		'owner' => array('email')
	) 
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
$result = addBlog('', getUserIdByEmail($_GET['owner']), $_GET['identify']);

if ($result===true) {
	printRespond(array('error' => 0));
}
else {
	printRespond(array('error' => -1 , 'result' =>$result));
}
?>
