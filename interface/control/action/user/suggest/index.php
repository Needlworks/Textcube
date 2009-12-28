<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
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

global $database;

header('Content-type: text/javascript');

$result = POD::queryAll("SELECT loginid,name FROM `{$database['prefix']}Users` WHERE name LIKE \"%".$_GET['input']."%\" or loginid LIKE \"%".$_GET['input']."%\" LIMIT 5");
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
