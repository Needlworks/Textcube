<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

$IV = array(
	'GET' => array(
		'name' => array('string'),
		'email' => array('email')
	) 
);
requireStrictRoute();

$result = User::add($_GET['email'], $_GET['name']);
if ($result===true) {
	Respond::PrintResult(array('error' => 0));
}
else {
	Respond::PrintResult(array('error' => -1 , 'result' =>$result));
}

?>
