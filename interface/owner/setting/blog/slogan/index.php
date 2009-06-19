<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'useSloganOnPost' => array('int',0,1),
		'useSloganOnCategory' => array('int',0,1),
		'useSloganOnTag' => array('int',0,1)
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (useBlogSlogan($blogid, $_POST['useSloganOnPost'],$_POST['useSloganOnCategory'],$_POST['useSloganOnTag']))
	Utils_Respond::ResultPage(0);
Utils_Respond::ResultPage( - 1);
?>
