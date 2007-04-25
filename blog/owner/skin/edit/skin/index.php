<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'body' => array('string'),
		'mode' => array('string')
	)
);

require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
	
$result = writeSkinHtml($owner, $_POST['body'], $_POST['mode']);
if ( $result === true)
	printRespond(array('error' => 0));
else
	printRespond(array('error' => 1, 'msg' => $result));
?>