<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForBlogOwner.php';

$IV = array(
	'GET' => array(
		'name' => array('string'),
		'email' => array('email')
	) 
);
requireStrictRoute();
$result = addUser($_GET['email'], $_GET['name']);
if ($result===true) {
	printRespond(array('error' => 0));
}
else {
	printRespond(array('error' => -1 , 'result' =>$result));
}

?>
