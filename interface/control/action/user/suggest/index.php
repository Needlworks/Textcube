<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'id' => array('string'),
		'input' => array('string','default' => ''),
		'cursor' => array('number', 'min' => 1)
	) 
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

header('Content-type: text/javascript');

$pool = DBModel::getInstance();
$pool->init("Users");
$pool->setQualifierSet(array("name","like",$_GET['input'],true),
    "OR",
    array("loginid","like",$_GET['input']));
$pool->setLimit(5);
$result = $pool->getAll("loginid, name");
if ($result) {
	echo 'ctlUserSuggestFunction_showSuggestion("'.$_GET['id'].'","'.$_GET['cursor'].'",';
	echo '"0"'; //TODO : clear
	foreach($result as $row) {
		echo ',"'. $row['loginid'] ." - ".$row['name'] . '"';
	}
	echo ');';
}
else {
	echo 'ctlUserSuggestFunction_showSuggestion("'.$_GET['id'].'","'.$_GET['cursor'].'",';
	echo '"-1"'; //TODO : clear
	echo ');';
}
?>
