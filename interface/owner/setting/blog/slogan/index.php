<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
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
	Respond::ResultPage(0);
Respond::ResultPage( - 1);
?>
