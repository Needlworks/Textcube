<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'item' => array('string')
	) 
);
require ROOT . '/lib/includeForBlogOwner.php';

requireStrictRoute();

$items = split(",",$_GET['item']);
foreach ($items as $item) {
	$result = removeBlog($item);
	if ($result===true) {
	}
	else {
		respond::PrintResult(array('error' => -1 , 'result' =>$result));
	}
}
		respond::PrintResult(array('error' => 0 , 'result' =>$suri['id']));
?>
