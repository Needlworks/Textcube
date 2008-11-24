<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET'=>array(
		'blogid'=>array('int','default'=>1)
	)
);
require ROOT . '/library/dispatcher.php';
if($_GET['blogid'] == 0 || empty($_GET['blogid'])) $newBlogid = getBlogId();
else $newBlogid = $_GET['blogid'];

$url = getDefaultURL($newBlogid) . '/owner/center/dashboard';
header("location:".$url);
exit;
?>
