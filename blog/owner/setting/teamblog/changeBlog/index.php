<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET'=>array(
		'blogid'=>array('int','default'=>''),
		'path'=>array('string')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
if($_GET['blogid'] == 0 || empty($_GET['blogid'])) $newBlogid = getBlogId();
else $newBlogid = $_GET['blogid'];

setBlogId($newBlogid);
$url = getDefaultURL($newBlogid) . $_GET['path'];
header("location:".$url);
exit;
?>
