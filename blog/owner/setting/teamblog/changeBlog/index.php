<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET'=>array(
		'bs'=>array('int','default'=>''),
		'path'=>array('string')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
if($_GET['bs'] == 0 || empty($_GET['bs'])) $bs = $_SESSION['admin'];
else $bs = $_GET['bs'];

$sql = "SELECT *  FROM `{$database['prefix']}BlogSettings` WHERE owner='$bs'";
$res = mysql_fetch_array(DBQuery::query($sql));
$_SESSION['userid'] = $bs;
$url = getDefaultURL($bs) . $_GET['path'];
header("location:".$url);
echo "<script> location.href = $url; </script>";
?>