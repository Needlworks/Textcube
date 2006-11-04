<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');

$IV = array(
	'GET' => array(
		'visibility' => array('int', 0, 3, 'default' => 0),
		'command' =>  array('string', 'mandatory' => false)
		)
	);

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

//$isAjaxRequest = checkAjaxRequest();

//if ($isAjaxRequest) 
if (!isset($_GET['command']))
{
	respondResultPage(setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0));
} else {
	switch ($_GET['command']) {
		case "protect":
			$_GET['command'] = 1;
			break;
		case "public":
			$_GET['command'] = 2;
			break;
		case "syndicate":
			$_GET['command'] = 3;
			break;
		case "private":
		default:
			$_GET['command'] = 0;
			break;
	}
	setEntryVisibility($suri['id'], $_GET['command']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
