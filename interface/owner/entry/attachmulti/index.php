<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'FILES' => array(
		'Filedata' => array('file')
	),
	'GET' => array( 
		'TSSESSION' => array( 'string' , 'default' => null) 
	)
);

if (!empty($_GET['TSSESSION']))
	$_COOKIE['TSSESSION'] = $_GET['TSSESSION'];
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.attachment");
$file = array_pop($_FILES);
$attachment = addAttachment($blogid, $suri['id'], $file);
echo "&success";
?>
