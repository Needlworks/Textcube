<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET'=>array(
		'blogid'=>array('int','default'=>1)
	)
);
require ROOT . '/library/preprocessor.php';
if($_GET['blogid'] == 0 || empty($_GET['blogid'])) $newBlogid = getBlogId();
else $newBlogid = $_GET['blogid'];

$url = getDefaultURL($newBlogid) . '/owner/center/dashboard';
header("location:".$url);
exit;
?>
