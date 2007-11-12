<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';

$param = array();
if (isset($_REQUEST['trashType'])) {
	array_push($param,'trashType=' . URL::encode($_REQUEST['trashType'],$service['useEncodedURL']));
}
if (isset($_REQUEST['category'])) {
	array_push($param,'category=' . URL::encode($_REQUEST['category'],$service['useEncodedURL']));
}
if (isset($_REQUEST['name'])) {
	array_push($param,'name=' . URL::encode($_REQUEST['name'],$service['useEncodedURL']));
}
if (isset($_REQUEST['ip'])) {
	array_push($param,'ip=' . URL::encode($_REQUEST['ip']));
}
if (isset($_REQUEST['withSearch'])) {
	array_push($param,'withSearch=' . URL::encode($_REQUEST['withSearch'],$service['useEncodedURL']));
}
if (isset($_REQUEST['search'])) {
	array_push($param,'search=' . URL::encode($_REQUEST['search'],$service['useEncodedURL']));
}

$paramStr = implode('&', $param);
if (strlen($paramStr) > 0) {
	$paramStr = '?' . $paramStr;
}

$location = $blogURL . 'owner/center';
if (empty($_REQUEST['trashType']) || $_REQUEST['trashType'] == "comment") {
	$location = 'trash/comment/index.php';
}
else
{
	$location = 'trash/trackback/index.php';
}

header("Location: {$location}{$paramStr}");

?>
