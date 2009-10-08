<?php
/// Copyright (c) 2004-2006, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'file' => array('string')
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
	
$result = getCSSContent($blogid, $_POST['file']);
if ($result === false)
	Respond::PrintResult(array('error' => 1));
else
	Respond::PrintResult(array('error' => 0, 'content' => $result));
?>
