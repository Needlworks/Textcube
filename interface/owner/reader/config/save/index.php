<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'updatecycle' => array('int', 0),
		'feedlife' => array('int', 0),
		'loadimage' => array(array('1', '2')),
		'allowscript' => array(array('1', '2')),
		'newwindow' => array(array('1', '2')) 
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
Respond::ResultPage(setReaderSetting($blogid, $_POST));
?>
