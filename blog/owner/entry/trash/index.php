<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';

$param = array();
if (isset($_REQUEST['trashType'])) {
	array_push($param,'trashType=' . encodeURL($_REQUEST['trashType']));
}
if (isset($_REQUEST['category'])) {
	array_push($param,'category=' . encodeURL($_REQUEST['category']));
}
if (isset($_REQUEST['name'])) {
	array_push($param,'name=' . encodeURL($_REQUEST['name']));
}
if (isset($_REQUEST['ip'])) {
	array_push($param,'ip=' . encodeURL($_REQUEST['ip']));
}
if (isset($_REQUEST['withSearch'])) {
	array_push($param,'withSearch=' . encodeURL($_REQUEST['withSearch']));
}
if (isset($_REQUEST['search'])) {
	array_push($param,'search=' . encodeURL($_REQUEST['search']));
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