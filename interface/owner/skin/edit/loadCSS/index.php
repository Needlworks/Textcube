<?php
/// Copyright (c) 2004-2006, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'file' => array('string')
	)
);

require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
	
$result = getCSSContent($blogid, $_POST['file']);
if ($result === false)
	printRespond(array('error' => 1));
else
	printRespond(array('error' => 0, 'content' => $result));
?>